<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ProductModels;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\Property\ProductFilterPropertyDTO;
use InvalidArgumentException;

final class ProductModelsRepository implements ProductModelsInterface
{
    private ProductFilterDTO|null $filter = null;

    private int|false $maxResult = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    /** Фильтр продукции */
    public function filter(ProductFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /** Максимальное количество записей в результате */
    public function maxResult(int $max): self
    {
        $this->maxResult = $max;

        return $this;
    }

    /** Возвращает массив ограниченный по количеству */
    public function findAll(): array|false
    {
        if(false == $this->maxResult)
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $maxResult');
        }

        $dbal = $this->builder();

        $dbal->setMaxResults($this->maxResult);

        $result = $dbal
            ->enableCache('products-product', 86400)
            ->fetchAllAssociative();

        return empty($result) ? false : $result;
    }

    /** Билдер запроса */
    public function builder(): DBALQueryBuilder
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local'
            );

        $dbal
            //                    ->addSelect('product_desc.preview AS product_preview')
            //                    ->addSelect('product_desc.description AS product_description')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.device = :device AND product_desc.local = :local'
            )
            ->setParameter('device', 'pc');


        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url')
            ->addSelect('product_info.sort')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        $dbal
            ->addSelect('product_active.active_from')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE AND
                    (product_active.active_to IS NULL OR product_active.active_to > NOW())
                ');

        /** Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency');

        /** Торговое предложение */
        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event'
        );

        if($this->filter?->getOffer())
        {
            $dbal->andWhere('product_offer.value = :offer');
            $dbal->setParameter('offer', $this->filter->getOffer());
        }

        /**  Тип торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            CategoryProductOffers::class,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );

        /** Цена торгового предложения */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id'
            )
            ->addGroupBy('product_offer_price.currency');

        $dbal
            ->addSelect('SUM(product_offer_quantity.quantity) AS product_offer_quantity')
            ->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id'
            );

        /** Множественные варианты торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id'
        );

        if($this->filter?->getVariation())
        {
            $dbal->andWhere('product_variation.value = :variation');
            $dbal->setParameter('variation', $this->filter->getVariation());
        }

        /** Тип множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            CategoryProductVariation::class,
            'category_variation',
            'category_variation.id = product_variation.category_variation'
        );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'category_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        )
            ->addGroupBy('product_variation_price.currency');

        $dbal
            ->addSelect('SUM(product_variation_quantity.quantity) AS product_variation_quantity')
            ->leftJoin(
                'category_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id'
            );

        /** Модификация множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_variation.id '
        );

        if($this->filter?->getModification())
        {
            $dbal->andWhere('product_modification.value = :modification');
            $dbal->setParameter('modification', $this->filter->getModification());
        }

        /** Тип модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            CategoryProductModification::class,
            'category_modification',
            'category_modification.id = product_modification.category_modification'
        );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        )
            ->addGroupBy('product_modification_price.currency');

        $dbal
            ->addSelect('SUM(product_modification_quantity.quantity) AS product_modification_quantity')
            ->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id'
            )
            ->addGroupBy('product_modification_price.currency');

        /** Артикул продукта */
        //        $dbal->addSelect("
        //            COALESCE(
        //                product_modification.article,
        //                product_variation.article,
        //                product_offer.article,
        //                product_info.article
        //            ) AS product_article
        //		");

        /** Фото продукта */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event AND product_photo.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id AND product_variation_image.root = true'
        );

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id AND product_modification_image.root = true'
        );

        $dbal->addSelect(
            "
			CASE
			    WHEN product_modification_image.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name)

			   WHEN product_variation_image.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)
					
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)
					
			   WHEN product_photo.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
					
			   ELSE NULL
			END AS product_image
		"
        );

        /** Расширение изображения */
        $dbal->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.ext
			   
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.ext
			   
			   WHEN product_photo.name IS NOT NULL 
			   THEN product_photo.ext
			   
			   ELSE NULL
			END AS product_image_ext
		");

        /** Флаг загрузки файла CDN */
        $dbal->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.cdn
					
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.cdn
					
			   WHEN product_photo.name IS NOT NULL 
			   THEN product_photo.cdn
			   
			   ELSE NULL
			END AS product_image_cdn
		");

        /** Категория */
        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true'
        );

        if($this->filter?->getCategory())
        {
            $dbal->andWhere('product_event_category.category = :category');
            $dbal->setParameter('category', $this->filter->getCategory(), CategoryProductUid::TYPE);
        }

        $dbal->leftJoin(
            'product_event_category',
            CategoryProduct::class,
            'category',
            'category.id = product_event_category.category'
        );

        $dbal
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event'
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );

        $dbal->addSelect("
			COALESCE(
                NULLIF(COUNT(product_modification), 0),
                NULLIF(COUNT(product_variation), 0),
                NULLIF(COUNT(product_offer), 0),
                0
            ) AS offer_count
		");

        /**
         * Фильтр по свойства продукта
         */
        if($this->filter?->getProperty())
        {
            /** @var ProductFilterPropertyDTO $property */
            foreach($this->filter->getProperty() as $property)
            {
                if($property->getValue())
                {
                    $dbal->join(
                        'product',
                        ProductProperty::class,
                        'product_property_'.$property->getType(),
                        'product_property_'.$property->getType().'.event = product.event AND 
                        product_property_'.$property->getType().'.field = :'.$property->getType().'_const AND 
                        product_property_'.$property->getType().'.value = :'.$property->getType().'_value'
                    );

                    $dbal->setParameter($property->getType().'_const', $property->getConst());
                    $dbal->setParameter($property->getType().'_value', $property->getValue());
                }
            }
        }

        /** Минимальная стоимость продукта */
        $dbal->addSelect("CASE
                   
        /* СТОИМОСТЬ МОДИФИКАЦИИ */       
        WHEN (ARRAY_AGG(
                            DISTINCT product_modification_price.price ORDER BY product_modification_price.price
                         ) 
                         FILTER 
                         (
                            WHERE  product_modification_price.price > 0
                         )
                     )[1] > 0 
                     
                     THEN (ARRAY_AGG(
                            DISTINCT product_modification_price.price ORDER BY product_modification_price.price
                         ) 
                         FILTER 
                         (
                            WHERE  product_modification_price.price > 0
                         )
                     )[1]
         
         
        /* СТОИМОСТЬ ВАРИАНТА */
         WHEN (ARRAY_AGG(
                            DISTINCT product_variation_price.price ORDER BY product_variation_price.price
                         ) 
                         FILTER 
                         (
                            WHERE  product_variation_price.price > 0
                         )
                     )[1] > 0 
                     
         THEN (ARRAY_AGG(
                            DISTINCT product_variation_price.price ORDER BY product_variation_price.price
                         ) 
                         FILTER 
                         (
                            WHERE  product_variation_price.price > 0
                         )
                     )[1]
         
         
         /* СТОИМОСТЬ ТП */
            WHEN (ARRAY_AGG(
                            DISTINCT product_offer_price.price ORDER BY product_offer_price.price
                         ) 
                         FILTER 
                         (
                            WHERE  product_offer_price.price > 0
                         )
                     )[1] > 0 
                     
            THEN (ARRAY_AGG(
                            DISTINCT product_offer_price.price ORDER BY product_offer_price.price
                         ) 
                         FILTER 
                         (
                            WHERE  product_offer_price.price > 0
                         )
                     )[1]
         
			  
			   WHEN product_price.price IS NOT NULL 
			   THEN product_price.price
			   
			   ELSE NULL
			END AS product_price
		");

        /** Валюта продукта */
        $dbal->addSelect(
            "
			CASE
			
			   WHEN MIN(product_modification_price.price) IS NOT NULL AND MIN(product_modification_price.price) > 0 
			   THEN product_modification_price.currency
			   
			   WHEN MIN(product_variation_price.price) IS NOT NULL AND MIN(product_variation_price.price) > 0  
			   THEN product_variation_price.currency
			   
			   WHEN MIN(product_offer_price.price) IS NOT NULL AND MIN(product_offer_price.price) > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL 
			   THEN product_price.currency
			   
			   ELSE NULL
			   
			END AS product_currency
		"
        );

        /** Количественный учет */
        $dbal->addSelect("
			CASE
			
			   WHEN SUM(product_modification_quantity.quantity) > 0 
			   THEN SUM(product_modification_quantity.quantity)
					
			   WHEN SUM(product_variation_quantity.quantity) > 0 
			   THEN SUM(product_variation_quantity.quantity)
					
			   WHEN SUM(product_offer_quantity.quantity) > 0 
			   THEN SUM(product_offer_quantity.quantity)
					
			   WHEN SUM(product_price.quantity) > 0
			   THEN SUM(product_price.quantity)
			   
			   ELSE 0
			   
			END AS product_quantity
		");

        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->allGroupByExclude();

        return $dbal;
    }

    public function analyze(): void
    {
        $this->builder()->analyze();
    }
}

