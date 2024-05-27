<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\AllProductsByCategory;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Device\Devices\Desktop;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Category\Type\Section\Field\Id\ProductCategorySectionFieldUid;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllProductsByCategory implements AllProductsByCategoryInterface
{

    private PaginatorInterface $paginator;

    private TranslatorInterface $translator;
    private DBALQueryBuilder $DBALQueryBuilder;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        TranslatorInterface $translator,
        PaginatorInterface $paginator,
    )
    {

        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    public function fetchAllProductByCategoryAssociative(
        ProductCategoryUid $category,
        ProductCategoryFilterDTO $filter,
        ?array $property,
        string $expr = 'AND',
    ): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        /** ЛОКАЛЬ */
        $locale = new Locale($this->translator->getLocale());
        $qb->setParameter('local', $locale, Locale::TYPE);


        $qb->from(CategoryEntity\ProductCategory::TABLE, 'category');
        $qb->where('category.id = :category');
        $qb->setParameter('category', $category, ProductCategoryUid::TYPE);

        $qb->join(
            'category',
            CategoryEntity\Event\ProductCategoryEvent::TABLE,
            'category_event',
            'category_event.id = category.event OR category_event.parent = category.id'
        );


        $qb
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
            'category_event',
            CategoryEntity\Info\ProductCategoryInfo::TABLE,
            'category_info',
            'category_info.event = category_event.id'
        );





        $qb->addSelect('category_trans.name AS category_name');

        $qb->leftJoin(
            'category_event',
            CategoryEntity\Trans\ProductCategoryTrans::TABLE,
            'category_trans',
            'category_trans.event = category_event.id AND category_trans.local = :local'
        );

        $qb->leftJoin(
            'category',
            CategoryEntity\Section\ProductCategorySection::TABLE,
            'category_section',
            'category_section.event = category.event'
        );


        /** Свойства, учавствующие в карточке */


        $qb->leftJoin(
            'category_section',
            CategoryEntity\Section\Field\ProductCategorySectionField::TABLE,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.card = TRUE OR category_section_field.photo = TRUE OR category_section_field.name = TRUE )'
        );

        $qb->leftJoin(
            'category_section_field',
            CategoryEntity\Section\Field\Trans\ProductCategorySectionFieldTrans::TABLE,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local'
        );


        $qb->addSelect('product.id');
        $qb->addSelect('product.event');


        /** Категория продукта */
        //$qb->from(ProductEntity\Category\ProductCategory::TABLE, 'product_category');
        $qb->leftJoin(
            'category',
            ProductEntity\Category\ProductCategory::TABLE,
            'product_category',
            'product_category.category = category_event.category'
        );




        $qb->join('product_category',
            ProductEntity\Product::TABLE,
            'product',
            'product.event = product_category.event'
        );


        $qb->join('product',
            ProductEntity\Event\ProductEvent::TABLE,
            'product_event',
            'product_event.id = product.event'
        );

        /** ФИЛЬТР СВОЙСТВ */
        if($property)
        {
            if($expr === 'AND')
            {
                foreach($property as $type => $item)
                {
                    if($item === true)
                    {
                        $item = 'true';
                    }

                    $prepareKey = uniqid('key_', false);
                    $prepareValue = uniqid('val_', false);
                    $aliase = uniqid('aliase', false);

                    $ProductCategorySectionFieldUid = new ProductCategorySectionFieldUid($type);
                    $ProductPropertyJoin = $aliase.'.field = :'.$prepareKey.' AND '.$aliase.'.value = :'.$prepareValue;

                    $qb->setParameter($prepareKey,
                        $ProductCategorySectionFieldUid,
                        ProductCategorySectionFieldUid::TYPE
                    );
                    $qb->setParameter($prepareValue, $item);

                    $qb->join(
                        'product',
                        ProductEntity\Property\ProductProperty::TABLE,
                        $aliase,
                        $aliase.'.event = product.event '.$expr.' '.$ProductPropertyJoin
                    );
                }
            }
            else
            {

                foreach($property as $type => $item)
                {
                    if($item === true)
                    {
                        $item = 'true';
                    }

                    $prepareKey = uniqid('', false);
                    $prepareValue = uniqid('', false);

                    $ProductCategorySectionFieldUid = new ProductCategorySectionFieldUid($type);
                    $ProductPropertyJoin[] = 'product_property_filter.field = :'.$prepareKey.' AND product_property_filter.value = :'.$prepareValue;

                    $qb->setParameter($prepareKey,
                        $ProductCategorySectionFieldUid,
                        ProductCategorySectionFieldUid::TYPE
                    );
                    $qb->setParameter($prepareValue, $item);

                }

                $qb->join(
                    'product',
                    ProductEntity\Property\ProductProperty::TABLE,
                    'product_property_filter',
                    'product_property_filter.event = product.event AND '.implode(' '.$expr.' ', $ProductPropertyJoin)

                );
            }
        }

        $qb->addSelect('product_trans.name AS product_name');

        $qb->leftJoin(
            'product_event',
            ProductEntity\Trans\ProductTrans::TABLE,
            'product_trans',
            'product_trans.event = product_event.id AND product_trans.local = :local'
        );


        $qb->addSelect('product_desc.preview AS product_preview');
        $qb->addSelect('product_desc.description AS product_description');

        $qb
            ->leftJoin(
                'product_event',
                ProductEntity\Description\ProductDescription::TABLE,
                'product_desc',
                'product_desc.event = product_event.id AND product_desc.device = :device '

            )->setParameter('device', 'pc');

        /** Цена товара */
        $qb->leftJoin(
            'product_event',
            ProductEntity\Price\ProductPrice::TABLE,
            'product_price',
            'product_price.event = product_event.id'
        )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency');

        /* ProductInfo */

        $qb->addSelect('product_info.url');

        $qb->leftJoin(
            'product_event',
            ProductEntity\Info\ProductInfo::TABLE,
            'product_info',
            'product_info.product = product.id'
        );

        /** Торговое предложение */

        $method = 'leftJoin';
        if($filter->getOffer())
        {
            $method = 'join';
            $qb->setParameter('offer', $filter->getOffer());
        }


        $qb->{$method}(
            'product_event',
            ProductEntity\Offers\ProductOffer::TABLE,
            'product_offer',
            'product_offer.event = product_event.id '.($filter->getOffer() ? ' AND product_offer.value = :offer' : '').' '
        );

        /* Получаем тип торгового предложения */
        $qb->leftJoin(
            'product_offer',
            CategoryEntity\Offers\ProductCategoryOffers::TABLE,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );


        /* Цена торгового предожения */
        $qb

            ->leftJoin(
            'product_offer',
            ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_price.currency');


        /** Множественные варианты торгового предложения */

        $method = 'leftJoin';
        if($filter->getVariation())
        {
            $method = 'join';
            $qb->setParameter('variation', $filter->getVariation());
        }



        $qb->{$method}(
            'product_offer',
            ProductEntity\Offers\Variation\ProductVariation::TABLE,
            'product_variation',
            'product_variation.offer = product_offer.id '.($filter->getVariation() ? ' AND product_variation.value = :variation' : '').' '
        );

        /** Получаем тип множественного варианта */

        $qb->leftJoin(
            'product_variation',
            CategoryEntity\Offers\Variation\ProductCategoryVariation::TABLE,
            'category_variation',
            'category_variation.id = product_variation.category_variation'
        );

        /** Цена множественного варианта */

        $qb->leftJoin(
            'category_variation',
            ProductEntity\Offers\Variation\Price\ProductVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        )
            //->addGroupBy('product_variation_price.price')
            ->addGroupBy('product_variation_price.currency');

        /** Модификация множественного варианта торгового предложения */

        $method = 'leftJoin';
        if($filter->getModification())
        {
            $method = 'join';
            $qb->setParameter('modification', $filter->getModification());
        }

        $qb->{$method}(
            'product_variation',
            ProductEntity\Offers\Variation\Modification\ProductModification::TABLE,
            'product_modification',
            'product_modification.variation = product_variation.id '.($filter->getModification() ? ' AND product_modification.value = :modification' : '').' '
        );

        /** Получаем тип модификации множественного варианта */

        $qb->leftJoin(
            'product_modification',
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::TABLE,
            'category_modification',
            'category_modification.id = product_modification.category_modification'
        );


        /** Цена множественного варианта */
        $qb->leftJoin(
            'product_modification',
            ProductEntity\Offers\Variation\Modification\Price\ProductModificationPrice::TABLE,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        )
            ->addGroupBy('product_modification_price.currency');

        $qb->addSelect("JSON_AGG
			( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						
						/* свойства для сортирвоки JSON */
						'0', CONCAT(product_offer.value, product_variation.value, product_modification.value),
						
						
						'offer_value', product_offer.value, /* значение торгового предложения */
						'offer_reference', category_offer.reference, /* тип (field) торгового предложения */
						'offer_article', product_offer.article, /* артикул торгового предложения */

						'variation_value', product_variation.value, /* значение множественного варианта */
						'variation_reference', category_variation.reference, /* тип (field) множественного варианта */
						'variation_article', category_variation.article, /* валюта множественного варианта */

						'modification_value', product_modification.value, /* значение модификации */
						'modification_reference', category_modification.reference, /* тип (field) модификации */
						'modification_article', category_modification.article /* артикул модификации */

					)
				
			)
			AS product_offers"
        );


        /** Фото продукта */

        $qb->leftJoin(
            'product_modification',
            ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE,
            'product_modification_image',
            '
			product_modification_image.modification = product_modification.id AND
			product_modification_image.root = true
			'
        );


        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id AND
			product_variation_image.root = true
			'
        );


        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Image\ProductOfferImage::TABLE,
            'product_offer_images',
            '
			product_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			'
        );


        $qb->leftJoin(
            'product_offer',
            ProductEntity\Photo\ProductPhoto::TABLE,
            'product_photo',
            '
			product_offer_images.name IS NULL AND
			product_photo.event = product_event.id AND
			product_photo.root = true
			'
        );

        $qb->addSelect("
			CASE
			 WHEN product_modification_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE."' , '/', product_modification_image.name)
			   WHEN product_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE."' , '/', product_variation_image.name)
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Image\ProductOfferImage::TABLE."' , '/', product_offer_images.name)
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Photo\ProductPhoto::TABLE."' , '/', product_photo.name)
			   ELSE NULL
			END AS product_image
		"
        );

        /** Флаг загрузки файла CDN */
        $qb->addSelect("
			CASE
			WHEN product_modification_image.name IS NOT NULL THEN
					product_modification_image.ext
			   WHEN product_variation_image.name IS NOT NULL THEN
					product_variation_image.ext
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.ext
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.ext
			   ELSE NULL
			END AS product_image_ext
		"
        );

        /** Флаг загрузки файла CDN */
        $qb->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					product_variation_image.cdn
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.cdn
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.cdn
			   ELSE NULL
			END AS product_image_cdn
		"
        );


        /** Минимальная стоиомсть продукта */
        $qb->addSelect("CASE
                          
                   
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
         
			  
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE NULL
			END AS product_price
		"
        );

        /** Валюта продукта */
        $qb->addSelect("
			CASE
			   WHEN MIN(product_modification_price.price) IS NOT NULL AND MIN(product_modification_price.price) > 0 THEN product_modification_price.currency
			   WHEN MIN(product_variation_price.price) IS NOT NULL AND MIN(product_variation_price.price) > 0  THEN product_variation_price.currency
			   WHEN MIN(product_offer_price.price) IS NOT NULL AND MIN(product_offer_price.price) > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		"
        );


        $qb->leftJoin(
            'product',
            ProductEntity\Property\ProductProperty::TABLE,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.id'
        );


        $qb->addSelect("JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort,
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

        $qb->allGroupByExclude();

        return $this->paginator->fetchAllAssociative($qb);

    }


    /** Метод возвращает все товары в категории */
    public function fetchAllProductByCategory(
        ProductCategoryUid $category = null
    ): array
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $qb->addSelect('product_category.category');

        if($category)
        {
            $qb
                ->from(ProductEntity\Category\ProductCategory::TABLE, 'product_category')
                ->where('product_category.category = :category AND product_category.root = true')
                ->setParameter('category', $category, ProductCategoryUid::TYPE);

            $qb->join('product_category',
                ProductEntity\Product::TABLE,
                'product',
                'product.event = product_category.event'
            );
        }
        else
        {
            $qb->from(ProductEntity\Product::TABLE, 'product');

            $qb->leftJoin('product',
                ProductEntity\Category\ProductCategory::TABLE,
                'product_category',
                'product_category.event = product.event AND product_category.root = true'
            );
        }

        $qb->addSelect('product.id');


        $qb
            ->addSelect('product_info.url')
            //->addSelect('product_info.article')
            ->leftJoin(
                'product',
                ProductEntity\Info\ProductInfo::TABLE,
                'product_info',
                'product_info.product = product.id'
            );


        $qb->addSelect('product_trans.name AS product_name');

        $qb->leftJoin(
            'product',
            ProductEntity\Trans\ProductTrans::TABLE,
            'product_trans',
            'product_trans.event = product.event AND product_trans.local = :local'
        );


        $qb
            ->addSelect('product_desc.preview')
            ->leftJoin(
                'product',
                ProductEntity\Description\ProductDescription::TABLE,
                'product_desc',
                'product_desc.event = product.event AND product_desc.local = :local AND product_desc.device = :device'
            )->setParameter('device', new Device(Desktop::class), Device::TYPE);

        $qb
            ->addSelect('product_modify.mod_date AS modify',)
            ->leftJoin(
                'product',
                ProductEntity\Modify\ProductModify::TABLE,
                'product_modify',
                'product_modify.event = product.event'
            );


        /** Торговое предложение */
        $qb
            ->addSelect('product_offer.value AS offer_value',)
            ->addSelect('product_offer.postfix AS offer_postfix',)
            ->leftJoin(
                'product',
                ProductEntity\Offers\ProductOffer::TABLE,
                'product_offer',
                'product_offer.event = product.event'
            );

        /* Цена торгового предожения */
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        );

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Quantity\ProductOfferQuantity::TABLE,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        );

        /* Получаем тип торгового предложения */
        $qb
            ->addSelect('category_offer.reference as offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryEntity\Offers\ProductCategoryOffers::TABLE,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );


        /** Множественный вариант */
        $qb
            ->addSelect('product_variation.value AS variation_value')
            ->addSelect('product_variation.postfix AS variation_postfix')
            ->leftJoin(
                'product_offer',
                ProductEntity\Offers\Variation\ProductVariation::TABLE,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );


        $qb->leftJoin(
            'product_variation',
            ProductEntity\Offers\Variation\Price\ProductVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );

        $qb->leftJoin(
            'category_variation',
            ProductEntity\Offers\Variation\Quantity\ProductVariationQuantity::TABLE,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        );

        $qb
            ->addSelect('category_variation.reference as variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryEntity\Offers\Variation\ProductCategoryVariation::TABLE,
                'category_variation',
                'category_variation.id = product_variation.category_variation'
            );


        /** Модификация множественного варианта торгового предложения */
        $qb
            ->addSelect('product_modification.value AS modification_value')
            ->addSelect('product_modification.postfix AS modification_postfix')
            ->leftJoin(
                'product_variation',
                ProductEntity\Offers\Variation\Modification\ProductModification::TABLE,
                'product_modification',
                'product_modification.variation = product_variation.id'
            );

        /** Цена множественного варианта */
        $qb->leftJoin(
            'product_modification',
            ProductEntity\Offers\Variation\Modification\Price\ProductModificationPrice::TABLE,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );

        $qb->leftJoin(
            'category_modification',
            ProductEntity\Offers\Variation\Modification\Quantity\ProductModificationQuantity::TABLE,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        );

        /** Получаем тип модификации множественного варианта */
        $qb
            ->addSelect('category_modification.reference as modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::TABLE,
                'category_modification',
                'category_modification.id = product_modification.category_modification'
            );


        /** Цена товара */
        $qb->leftJoin(
            'product',
            ProductEntity\Price\ProductPrice::TABLE,
            'product_price',
            'product_price.event = product.event'
        );


        /** Идентификатор */
        $qb->addSelect("
			CASE
			   WHEN product_modification.const IS NOT NULL THEN product_modification.const
			   WHEN product_variation.const IS NOT NULL THEN product_variation.const
			   WHEN product_offer.const IS NOT NULL THEN product_offer.const
			   ELSE product.id
			END AS product_id
		"
        );


        /** Стоимость */
        $qb->addSelect("
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0  THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE NULL
			END AS product_price
		"
        );

        /** Валюта продукта */
        $qb->addSelect("
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0  THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		"
        )
            ->addGroupBy('product_modification_price.currency')
            ->addGroupBy('product_variation_price.currency')
            ->addGroupBy('product_offer_price.currency')
            ->addGroupBy('product_price.currency');


        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_quantity.quantity IS NOT NULL AND product_modification_quantity.reserve IS NOT NULL THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)
			   WHEN product_modification_quantity.quantity IS NOT NULL THEN product_modification_quantity.quantity

			   WHEN product_variation_quantity.quantity IS NOT NULL AND product_variation_quantity.reserve  IS NOT NULL THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			   WHEN product_variation_quantity.quantity IS NOT NULL THEN product_variation_quantity.quantity

			   WHEN product_offer_quantity.quantity IS NOT NULL  AND product_offer_quantity.reserve IS NOT NULL THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)
			   WHEN product_offer_quantity.quantity IS NOT NULL THEN product_offer_quantity.quantity
			   
			   WHEN product_price.quantity IS NOT NULL AND product_price.reserve IS NOT NULL THEN (product_price.quantity - product_price.reserve)
			   WHEN product_price.quantity IS NOT NULL THEN product_price.quantity
			   
			   ELSE NULL
			END AS product_quantity
		'
        )
            ->addGroupBy('product_modification_quantity.reserve')
            ->addGroupBy('product_variation_quantity.reserve')
            ->addGroupBy('product_offer_quantity.reserve')
            ->addGroupBy('product_price.reserve');

        /** Фото продукта */

        $qb->leftJoin(
            'product_modification',
            ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE,
            'product_modification_image',
            '
			product_modification_image.modification = product_modification.id AND
			product_modification_image.root = true
			'
        );

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id AND
			product_variation_image.root = true
			'
        );

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Image\ProductOfferImage::TABLE,
            'product_offer_images',
            '
			product_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			'
        );

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Photo\ProductPhoto::TABLE,
            'product_photo',
            '
			product_offer_images.name IS NULL AND
			product_photo.event = product.event AND
			product_photo.root = true
			'
        );

        $qb->addSelect("
			CASE
			 WHEN product_modification_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE."' , '/', product_modification_image.name)
			   WHEN product_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE."' , '/', product_variation_image.name)
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Image\ProductOfferImage::TABLE."' , '/', product_offer_images.name)
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Photo\ProductPhoto::TABLE."' , '/', product_photo.name)
			   ELSE NULL
			END AS product_image
		"
        );

        /** Расширение файла */
        $qb->addSelect("
			CASE
			WHEN product_modification_image.ext IS NOT NULL AND product_modification_image.name IS NOT NULL THEN
					product_modification_image.ext
			   WHEN product_variation_image.ext IS NOT NULL AND product_variation_image.name IS NOT NULL THEN
					product_variation_image.ext
			   WHEN product_offer_images.ext IS NOT NULL AND product_offer_images.name IS NOT NULL THEN
					product_offer_images.ext
			   WHEN product_photo.ext IS NOT NULL AND product_photo.name IS NOT NULL THEN
					product_photo.ext
			   ELSE NULL
			END AS product_image_ext
		"
        );

        /** Флаг загрузки файла CDN */
        $qb->addSelect("
			CASE
			    WHEN product_modification_image.cdn IS NOT NULL AND product_modification_image.name IS NOT NULL THEN
					product_modification_image.cdn
			   WHEN product_variation_image.cdn IS NOT NULL AND product_variation_image.name IS NOT NULL THEN
					product_variation_image.cdn
			   WHEN product_offer_images.cdn IS NOT NULL AND product_offer_images.name IS NOT NULL THEN
					product_offer_images.cdn
			   WHEN product_photo.cdn IS NOT NULL AND product_photo.name IS NOT NULL THEN
					product_photo.cdn
			   ELSE NULL
			END AS product_image_cdn
		"
        );


        /** Свойства, учавствующие в карточке */

        $qb->leftJoin(
            'product_category',
            CategoryEntity\ProductCategory::TABLE,
            'category',
            'category.id = product_category.category'
        );


        $qb
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
                'category',
                CategoryEntity\Info\ProductCategoryInfo::TABLE,
                'category_info',
                'category_info.event = category.event'
            );


        $qb->leftJoin(
            'category',
            CategoryEntity\Section\ProductCategorySection::TABLE,
            'category_section',
            'category_section.event = category.event'
        );


        $qb->leftJoin(
            'category_section',
            CategoryEntity\Section\Field\ProductCategorySectionField::TABLE,
            'category_section_field',
            'category_section_field.section = category_section.id AND category_section_field.card = TRUE'
        );

        $qb->leftJoin(
            'category_section_field',
            CategoryEntity\Section\Field\Trans\ProductCategorySectionFieldTrans::TABLE,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local'
        );


        $qb->leftJoin(
            'category_section_field',
            ProductEntity\Property\ProductProperty::TABLE,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.id'
        );


        /* Артикул продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification.article IS NOT NULL THEN product_modification.article
			   WHEN product_variation.article IS NOT NULL THEN product_variation.article
			   WHEN product_offer.article IS NOT NULL THEN product_offer.article
			   WHEN product_info.article IS NOT NULL THEN product_info.article
			   ELSE NULL
			END AS product_article
		'
        );


        /* Артикул продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification.barcode IS NOT NULL THEN product_modification.barcode
			   WHEN product_variation.barcode IS NOT NULL THEN product_variation.barcode
			   WHEN product_offer.barcode IS NOT NULL THEN product_offer.barcode
			   WHEN product_info.barcode IS NOT NULL THEN product_info.barcode
			   ELSE NULL
			END AS product_barcode
		'
        );


        $qb->addSelect("JSON_AGG
		( DISTINCT

				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)

		)
			AS category_section_field"
        );

        $qb->allGroupByExclude();

        return $qb->enableCache('products-product', 86400)->fetchAllAssociative();

    }


}