<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ProductCatalog;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
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

final class ProductCatalogRepository implements ProductCatalogInterface
{
    private CategoryProductUid|false $categoryUid = false;

    private int|false $maxResult = false;

    public function __construct(
        private readonly DBALQueryBuilder $dbal
    ) {}

    /**
     * Максимальное количество записей в результате
     */
    public function maxResult(int $max): self
    {
        $this->maxResult = $max;

        return $this;
    }

    /**
     * Фильтр по категории
     */
    public function forCategory(CategoryProduct|CategoryProductUid|string $category): self
    {
        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->categoryUid = $category;

        return $this;
    }

    /**
     * Метод возвращает список продуктов из разных категорий
     *
     * @return array{
     * "id": string,
     * "event": string,
     * "product_name": string,
     * "url": string,
     * "active_from": string,
     * "active_to": string,
     * "product_offer_uid": string,
     * "product_offer_value": string,
     * "product_offer_postfix": string,
     * "product_offer_reference": string,
     * "product_variation_uid": string,
     * "product_variation_value": string,
     * "product_variation_postfix": string,
     * "product_variation_reference": string,
     * "product_modification_uid": string,
     * "product_modification_value": string,
     * "product_modification_postfix": string,
     * "product_modification_reference": string,
     * "product_article": string,
     * "product_image": string,
     * "product_image_ext": string,
     * "product_image_cdn": bool,
     * "product_price": int,
     * "product_currency": string,
     * "category_url": string,
     * "category_name": string,
     * "category_section_field": string,
     * } | false
     */
    public function find(): array|false
    {

        $dbal = $this->dbal
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        $dbal->join('product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local'
            );

        /** Цена товара */
        $dbal
            ->leftJoin(
                'product_event',
                ProductPrice::class,
                'product_price',
                'product_price.event = product_event.id'
            )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency')
            ->addGroupBy('product_price.quantity')
            ->addGroupBy('product_price.reserve');

        /* ProductInfo */
        $dbal
            ->addSelect('product_info.url')
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            )
            ->addGroupBy('product_info.article')
            ->addGroupBy('product_info.sort');

        /** Даты категории */
        $dbal
            ->addSelect('product_active.active_from')
            ->addSelect('product_active.active_to')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                'product_active.event = product.event'
            );

        /** Торговое предложение */

        $dbal
            ->addSelect('product_offer.id as product_offer_uid')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
            )
            ->addGroupBy('product_offer.article');

        /** Цена торгового предожения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_price.price')
            ->addGroupBy('product_offer_price.currency');

        /** Наличие торгового предожения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_quantity.quantity')
            ->addGroupBy('product_offer_quantity.reserve');

        /** Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        //CategoryProductOffers

        /** Множественные варианты торгового предложения */
        $dbal
            ->addSelect('product_offer_variation.id as product_variation_uid')
            ->addSelect('product_offer_variation.value as product_variation_value')
            ->addSelect('product_offer_variation.postfix as product_variation_postfix')
            ->leftOneJoin(
                'product_offer',
                ProductVariation::class,
                'product_offer_variation',
                'product_offer_variation.offer = product_offer.id'
            )
            ->addGroupBy('product_offer_variation.article');

        /** Цена множественного варианта */

        $dbal->leftJoin(
            'category_offer_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_price.price')
            ->addGroupBy('product_variation_price.currency');

        /** Наличие множественного варианта */

        $dbal->leftJoin(
            'category_offer_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_quantity.quantity')
            ->addGroupBy('product_variation_quantity.reserve');

        /** Получаем тип множественного варианта */
        $dbal
            ->addSelect('category_offer_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_offer_variation',
                CategoryProductVariation::class,
                'category_offer_variation',
                'category_offer_variation.id = product_offer_variation.category_variation'
            );


        /** Модификация множественного варианта торгового предложения */

        $dbal
            ->addSelect('product_offer_modification.id as product_modification_uid')
            ->addSelect('product_offer_modification.value as product_modification_value')
            ->addSelect('product_offer_modification.postfix as product_modification_postfix')
            ->leftJoin(
                'product_offer_variation',
                ProductModification::class,
                'product_offer_modification',
                'product_offer_modification.variation = product_offer_variation.id'
            )
            ->addGroupBy('product_offer_modification.article');

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_price.price')
            ->addGroupBy('product_modification_price.currency');

        /** Наличие множественного варианта */
        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_quantity.quantity')
            ->addGroupBy('product_modification_quantity.reserve');

        /** Получаем тип множественного варианта */
        $dbal
            ->addSelect('category_offer_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_offer_modification',
                CategoryProductModification::class,
                'category_offer_modification',
                'category_offer_modification.id = product_offer_modification.category_modification'
            );

        /** Артикул продукта */

        $dbal->addSelect("
			CASE
			   WHEN product_offer_modification.article IS NOT NULL 
			   THEN product_offer_modification.article
			   
			   WHEN product_offer_variation.article IS NOT NULL 
			   THEN product_offer_variation.article
			   
			   WHEN product_offer.article IS NOT NULL 
			   THEN product_offer.article
			   
			   WHEN product_info.article IS NOT NULL 
			   THEN product_info.article
			   
			   ELSE NULL
			END AS product_article
		"
        );

        /** Фото продукта */

        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationImage::class,
            'product_offer_modification_image',
            '
			product_offer_modification_image.modification = product_offer_modification.id AND
			product_offer_modification_image.root = true
			'
        )
            ->addGroupBy('product_offer_modification_image.name')
            ->addGroupBy('product_offer_modification_image.ext')
            ->addGroupBy('product_offer_modification_image.cdn');

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_offer_variation_image',
            '
			product_offer_variation_image.variation = product_offer_variation.id AND
			product_offer_variation_image.root = true
			'
        )
            ->addGroupBy('product_offer_variation_image.name')
            ->addGroupBy('product_offer_variation_image.ext')
            ->addGroupBy('product_offer_variation_image.cdn');

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
			product_offer_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			'
        )
            ->addGroupBy('product_offer_images.name')
            ->addGroupBy('product_offer_images.ext')
            ->addGroupBy('product_offer_images.cdn');

        $dbal->leftJoin(
            'product_offer',
            ProductPhoto::class,
            'product_photo',
            '
			product_offer_images.name IS NULL AND
			product_photo.event = product_event.id AND
			product_photo.root = true
			'
        )
            ->addGroupBy('product_photo.name')
            ->addGroupBy('product_photo.ext')
            ->addGroupBy('product_photo.cdn');

        $dbal->addSelect("
			CASE
			
			 WHEN product_offer_modification_image.name IS NOT NULL 
			 THEN CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."', '/', product_offer_modification_image.name)
			 
			 WHEN product_offer_variation_image.name IS NOT NULL 
			 THEN CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_offer_variation_image.name)
			   
			 WHEN product_offer_images.name IS NOT NULL 
			 THEN CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)
			 
			 WHEN product_photo.name IS NOT NULL 
			 THEN CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
			 
			 ELSE NULL
			 
			END AS product_image
		"
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect("
			CASE
			
                WHEN product_offer_modification_image.name IS NOT NULL 
                THEN product_offer_modification_image.ext
			
			   WHEN product_offer_variation_image.name IS NOT NULL 
			   THEN product_offer_variation_image.ext
			   
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.ext
			   
			   WHEN product_photo.name IS NOT NULL 
			   THEN product_photo.ext
			   
			   ELSE NULL
			END AS product_image_ext
		"
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect("
			CASE
			   WHEN product_offer_variation_image.name IS NOT NULL 
			   THEN product_offer_variation_image.cdn
					
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.cdn
					
			   WHEN product_photo.name IS NOT NULL 
			   THEN product_photo.cdn
					
			   ELSE NULL
			END AS product_image_cdn
		"
        );

        /** Стоимость продукта */
        $dbal->addSelect("
			CASE
			   WHEN COALESCE(product_modification_price.price, 0) != 0 
			   THEN product_modification_price.price
			   
			   WHEN COALESCE(product_variation_price.price, 0) != 0 
			   THEN product_variation_price.price
			   
			   WHEN COALESCE(product_offer_price.price, 0) != 0 
			   THEN product_offer_price.price
			   
			   WHEN COALESCE(product_price.price, 0) != 0 
			   THEN product_price.price
			   
			   ELSE NULL
			END AS product_price
		"
        );

        //        /** Наличие продукта */
        //        $dbal->addSelect("
        //			CASE
        //			   WHEN COALESCE(product_modification_price.quantity, 0) != 0 THEN product_modification_price.quantity
        //			   WHEN COALESCE(product_variation_price.quantity, 0) != 0 THEN product_variation_price.quantity
        //			   WHEN COALESCE(product_offer_price.quantity, 0) != 0 THEN product_offer_price.quantity
        //			   WHEN COALESCE(product_price.quantity, 0) != 0 THEN product_price.quantity
        //			   ELSE NULL
        //			END AS product_quantity
        //		"
        //        );

        /** Стоимость продукта */
        $dbal->addSelect("
			CASE
			   WHEN COALESCE(product_modification_price.price, 0) != 0 
			   THEN product_modification_price.currency
			   
			   WHEN COALESCE(product_variation_price.price, 0) != 0 
			   THEN product_variation_price.currency
			   
			   WHEN COALESCE(product_offer_price.price, 0) != 0 
			   THEN product_offer_price.currency
			   
			   WHEN COALESCE(product_price.price, 0) != 0 
			   THEN product_price.currency
			   
			   ELSE NULL
			END AS product_currency
		"
        );

        /** Категория */
        if($this->categoryUid instanceof CategoryProductUid)
        {
            $dbal->join(
                'product',
                ProductCategory::class,
                'product_event_category',
                '
                product_event_category.event = product.event AND 
                product_event_category.category = :category AND 
                product_event_category.root = true'
            )->setParameter(
                'category',
                $this->categoryUid,
                CategoryProductUid::TYPE
            );
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductCategory::class,
                'product_event_category',
                '
                product_event_category.event = product.event AND 
                product_event_category.root = true'
            );
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
                'product_event_category',
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

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event'
        );

        /** Свойства, участвующие в карточке */
        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.card = TRUE OR category_section_field.photo = TRUE OR category_section_field.name = TRUE )'
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local'
        );

        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.const'
        );

        $dbal->addSelect("JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
					'field_sort', category_section_field.sort,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_photo', category_section_field.photo,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)
			
		)
			AS category_section_field"
        );

        /** Только с ценой */
        $dbal->andWhere("

 			CASE
			   WHEN product_modification_price.price  IS NOT NULL THEN product_modification_price.price
			   WHEN product_variation_price.price  IS NOT NULL THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE 0
			END > 0

 		"
        );

        /** Только при наличии */
        $dbal->andWhere("
 		
 			CASE
			   WHEN product_modification_quantity.quantity IS NOT NULL THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)
			   WHEN product_variation_quantity.quantity IS NOT NULL THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			   WHEN product_offer_quantity.quantity IS NOT NULL THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)
			   WHEN product_price.quantity  IS NOT NULL THEN (product_price.quantity - product_price.reserve)
			   ELSE 0
			END > 0
 		
 		"
        );

        $dbal->addOrderBy('product_modification_quantity.reserve', 'DESC');
        $dbal->addOrderBy('product_variation_quantity.reserve', 'DESC');
        $dbal->addOrderBy('product_offer_quantity.reserve', 'DESC');
        $dbal->addOrderBy('product_price.reserve', 'DESC');

        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->allGroupByExclude();

        if(false !== $this->maxResult)
        {
            $dbal->setMaxResults($this->maxResult);
        }

        $dbal->enableCache('product-products', 86400, false);

        $result = $dbal->fetchAllAssociative();

        return empty($result) ? false : $result;
    }
}