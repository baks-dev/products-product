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

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductDetailByValue implements ProductDetailByValueInterface
{

    private TranslatorInterface $translator;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        TranslatorInterface $translator,
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {

        $this->translator = $translator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает детальную информацию о продукте и его заполненному значению ТП, вариантов и модификаций.
     *
     * @param ?string $offer - значение торгового предложения
     * @param ?string $variation - значение множественного варианта ТП
     * @param ?string $modification - значение модификации множественного варианта ТП
     */
    public function fetchProductAssociative(
        ProductUid $product,
        string $offer = null,
        string $variation = null,
        string $modification = null,
    ): array|bool
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('product.id')->groupBy('product.id');
        $qb->addSelect('product.event')->addGroupBy('product.event');

        $qb->from(ProductEntity\Product::TABLE, 'product');

        $qb->join(
            'product',
            ProductEntity\Event\ProductEvent::TABLE,
            'product_event',
            'product_event.id = product.event'
        );

        $qb->addSelect('product_active.active')->addGroupBy('product_active.active');
        $qb->addSelect('product_active.active_from')->addGroupBy('product_active.active_from');
        $qb->addSelect('product_active.active_to')->addGroupBy('product_active.active_to');

        $qb->join(
            'product',
            ProductEntity\Active\ProductActive::TABLE,
            'product_active',
            '
			product_active.event = product.event
			
			'
        );

        $qb->addSelect('product_seo.title AS seo_title')->addGroupBy('product_seo.title');
        $qb->addSelect('product_seo.keywords AS seo_keywords')->addGroupBy('product_seo.keywords');
        $qb->addSelect('product_seo.description AS seo_description')->addGroupBy('product_seo.description');

        $qb
            ->leftJoin(
            'product',
            ProductEntity\Seo\ProductSeo::TABLE,
            'product_seo',
            'product_seo.event = product.event AND product_seo.local = :local'
        );


        $qb->addSelect('product_trans.name AS product_name')->addGroupBy('product_trans.name');

        $qb->leftJoin(
            'product_event',
            ProductEntity\Trans\ProductTrans::TABLE,
            'product_trans',
            'product_trans.event = product_event.id AND product_trans.local = :local'
        );



        $qb->addSelect('product_desc.preview AS product_preview')->addGroupBy('product_desc.preview');
        $qb->addSelect('product_desc.description AS product_description')->addGroupBy('product_desc.description');

        $qb
            ->leftJoin(
                'product_event',
                ProductEntity\Description\ProductDescription::TABLE,
                'product_desc',
                'product_desc.event = product_event.id AND product_desc.device = :device '

            )->setParameter('device', 'pc');


        /* Базовая Цена товара */
        $qb->leftJoin(
            'product_event',
            ProductEntity\Price\ProductPrice::TABLE,
            'product_price',
            'product_price.event = product_event.id'
        )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency')
            ->addGroupBy('product_price.quantity')
            ->addGroupBy('product_price.reserve')
            ->addGroupBy('product_price.reserve');

        /* ProductInfo */

        $qb->addSelect('product_info.url')->addGroupBy('product_info.url');

        $qb->leftJoin(
            'product_event',
            ProductEntity\Info\ProductInfo::TABLE,
            'product_info',
            'product_info.product = product.id '
        )->addGroupBy('product_info.article');

        /* Торговое предложение */

        $qb->addSelect('product_offer.id as product_offer_uid')->addGroupBy('product_offer.id');
        $qb->addSelect('product_offer.value as product_offer_value')->addGroupBy('product_offer.value');
        $qb->addSelect('product_offer.postfix as product_offer_postfix')->addGroupBy('product_offer.postfix');
        $qb->leftJoin(
            'product_event',
            ProductEntity\Offers\ProductOffer::TABLE,
            'product_offer',
            'product_offer.event = product_event.id '.($offer ? ' AND product_offer.value = :product_offer_value' : '').' '
        )
            ->addGroupBy('product_offer.article');

        if($offer)
        {
            $qb->setParameter('product_offer_value', $offer);
        }

        /* Цена торгового предо жения */
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_price.price')
            ->addGroupBy('product_offer_price.currency');

        /* Получаем тип торгового предложения */
        $qb->addSelect('category_offer.reference AS product_offer_reference')->addGroupBy('category_offer.reference');
        $qb->leftJoin(
            'product_offer',
            CategoryEntity\Offers\ProductCategoryOffers::TABLE,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );

        /* Получаем название торгового предложения */
        $qb->addSelect('category_offer_trans.name as product_offer_name')->addGroupBy('category_offer_trans.name');
        $qb->addSelect('category_offer_trans.postfix as product_offer_name_postfix')->addGroupBy('category_offer_trans.postfix');
        $qb->leftJoin(
            'category_offer',
            CategoryEntity\Offers\Trans\ProductCategoryOffersTrans::TABLE,
            'category_offer_trans',
            'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
        );

        /* Наличие и резерв торгового предложения */
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Quantity\ProductOfferQuantity::TABLE,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_quantity.quantity')
            ->addGroupBy('product_offer_quantity.reserve');

        //ProductCategoryOffers

        /* Множественные варианты торгового предложения */

        $qb->addSelect('product_offer_variation.id as product_variation_uid')->addGroupBy('product_offer_variation.id');
        $qb->addSelect('product_offer_variation.value as product_variation_value')->addGroupBy('product_offer_variation.value');
        $qb->addSelect('product_offer_variation.postfix as product_variation_postfix')->addGroupBy('product_offer_variation.postfix');

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\ProductVariation::TABLE,
            'product_offer_variation',
            'product_offer_variation.offer = product_offer.id'.($variation ? ' AND product_offer_variation.value = :product_variation_value' : '').' '
        )
            ->addGroupBy('product_offer_variation.article');

        if($variation)
        {
            $qb->setParameter('product_variation_value', $variation);
        }

        /* Цена множественного варианта */
        $qb->leftJoin(
            'product_offer_variation',
            ProductEntity\Offers\Variation\Price\ProductVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_price.price')
            ->addGroupBy('product_variation_price.currency');

        /* Получаем тип множественного варианта */
        $qb->addSelect('category_offer_variation.reference as product_variation_reference')
            ->addGroupBy('category_offer_variation.reference');
        $qb->leftJoin(
            'product_offer_variation',
            CategoryEntity\Offers\Variation\ProductCategoryVariation::TABLE,
            'category_offer_variation',
            'category_offer_variation.id = product_offer_variation.category_variation'
        );

        /* Получаем название множественного варианта */
        $qb->addSelect('category_offer_variation_trans.name as product_variation_name')
            ->addGroupBy('category_offer_variation_trans.name');

        $qb->addSelect('category_offer_variation_trans.postfix as product_variation_name_postfix')
            ->addGroupBy('category_offer_variation_trans.postfix');
        $qb->leftJoin(
            'category_offer_variation',
            CategoryEntity\Offers\Variation\Trans\ProductCategoryVariationTrans::TABLE,
            'category_offer_variation_trans',
            'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local'
        );

        /* Наличие и резерв множественного варианта */
        $qb->leftJoin(
            'category_offer_variation',
            ProductEntity\Offers\Variation\Quantity\ProductVariationQuantity::TABLE,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_quantity.quantity')
            ->addGroupBy('product_variation_quantity.reserve');

        /* Модификация множественного варианта торгового предложения */

        $qb->addSelect('product_offer_modification.id as product_modification_uid')->addGroupBy('product_offer_modification.id');
        $qb->addSelect('product_offer_modification.value as product_modification_value')->addGroupBy('product_offer_modification.value');
        $qb->addSelect('product_offer_modification.postfix as product_modification_postfix')->addGroupBy('product_offer_modification.postfix');

        $qb->leftJoin(
            'product_offer_variation',
            ProductEntity\Offers\Variation\Modification\ProductModification::TABLE,
            'product_offer_modification',
            'product_offer_modification.variation = product_offer_variation.id'.($modification ? ' AND product_offer_modification.value = :product_modification_value' : '').' '
        )
            ->addGroupBy('product_offer_modification.article');

        if($modification)
        {
            $qb->setParameter('product_modification_value', $modification);
        }

        /* Цена модификации множественного варианта */
        $qb->leftJoin(
            'product_offer_modification',
            ProductEntity\Offers\Variation\Modification\Price\ProductModificationPrice::TABLE,
            'product_modification_price',
            'product_modification_price.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_price.price')
            ->addGroupBy('product_modification_price.currency');

        /* Получаем тип модификации множественного варианта */
        $qb->addSelect('category_offer_modification.reference as product_modification_reference')
            ->addGroupBy('category_offer_modification.reference');
        $qb->leftJoin(
            'product_offer_modification',
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::TABLE,
            'category_offer_modification',
            'category_offer_modification.id = product_offer_modification.category_modification'
        );

        /* Получаем название типа модификации */
        $qb->addSelect('category_offer_modification_trans.name as product_modification_name')
            ->addGroupBy('category_offer_modification_trans.name');

        $qb->addSelect('category_offer_modification_trans.postfix as product_modification_name_postfix')
            ->addGroupBy('category_offer_modification_trans.postfix');
        $qb->leftJoin(
            'category_offer_modification',
            CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryModificationTrans::TABLE,
            'category_offer_modification_trans',
            'category_offer_modification_trans.modification = category_offer_modification.id AND category_offer_modification_trans.local = :local'
        );

        /* Наличие и резерв модификации множественного варианта */
        $qb->leftJoin(
            'category_offer_modification',
            ProductEntity\Offers\Variation\Modification\Quantity\ProductModificationQuantity::TABLE,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_quantity.quantity')
            ->addGroupBy('product_modification_quantity.reserve');

        //$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");

        /* Артикул продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_offer_modification.article IS NOT NULL THEN product_offer_modification.article
			   WHEN product_offer_variation.article IS NOT NULL THEN product_offer_variation.article
			   WHEN product_offer.article IS NOT NULL THEN product_offer.article
			   WHEN product_info.article IS NOT NULL THEN product_info.article
			   ELSE NULL
			END AS product_article
		'
        );

        /* Фото модификаций */

        $qb->leftJoin(
            'product_offer_modification',
            ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE,
            'product_offer_modification_image',
            '
			product_offer_modification_image.modification = product_offer_modification.id
			'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE WHEN product_offer_modification_image.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_modification_image.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE."' , '/', product_offer_modification_image.name),
						'product_img_ext', product_offer_modification_image.ext,
						'product_img_cdn', product_offer_modification_image.cdn
						

					) END
			) AS product_modification_image
	"
        );

        /* Фото вариантов */

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE,
            'product_offer_variation_image',
            '
			product_offer_variation_image.variation = product_offer_variation.id
			'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE WHEN product_offer_variation_image.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_variation_image.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE."' , '/', product_offer_variation_image.name),
						'product_img_ext', product_offer_variation_image.ext,
						'product_img_cdn', product_offer_variation_image.cdn
						

					) END
			) AS product_variation_image
	"
        );

        /* Фот оторговых предложений */

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Image\ProductOfferImage::TABLE,
            'product_offer_images',
            '
			
			product_offer_images.offer = product_offer.id
			
		'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE WHEN product_offer_images.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_images.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Offers\Image\ProductOfferImage::TABLE."' , '/', product_offer_images.name),
						'product_img_ext', product_offer_images.ext,
						'product_img_cdn', product_offer_images.cdn
						

					) END

				 /*ORDER BY product_photo.root DESC, product_photo.id*/
			) AS product_offer_images
	"
        );

        /* Фот опродукта */

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Photo\ProductPhoto::TABLE,
            'product_photo',
            '
	
			product_photo.event = product_event.id
			'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT

					CASE WHEN product_photo.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_photo.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Photo\ProductPhoto::TABLE."' , '/', product_photo.name),
						'product_img_ext', product_photo.ext,
						'product_img_cdn', product_photo.cdn
						

					) END

				 /*ORDER BY product_photo.root DESC, product_photo.id*/
			) AS product_photo
	"
        );

        /* Стоимость продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.price
			   ELSE NULL
			END AS product_price
		'
        );

        /* Валюта продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		'
        );

        /* Наличие продукта */

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
        );

        //		->addGroupBy('product_modification_quantity.quantity')
        //		->addGroupBy('product_modification_quantity.reserve')

        /* Наличие */
        //		$qb->addSelect("
        //			CASE
        //			   WHEN product_modification_price.price IS NOT NULL THEN product_modification_price.price
        //			   WHEN product_variation_price.price IS NOT NULL THEN product_variation_price.price
        //			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.price
        //			   WHEN product_price.price IS NOT NULL THEN product_price.price
        //			   ELSE NULL
        //			END AS product_price
        //		"
        //		);

        /* Категория */
        $qb->join(
            'product_event',
            ProductEntity\Category\ProductCategory::TABLE,
            'product_event_category',
            'product_event_category.event = product_event.id AND product_event_category.root = true'
        );

        //$qb->andWhere('product_event_category.category = :category');
        //$qb->setParameter('category', $category, ProductCategoryUid::TYPE);

        $qb->join(
            'product_event_category',
            CategoryEntity\ProductCategory::TABLE,
            'category',
            'category.id = product_event_category.category'
        );

        $qb->addSelect('category_trans.name AS category_name')->addGroupBy('category_trans.name');

        $qb->leftJoin(
            'category',
            CategoryEntity\Trans\ProductCategoryTrans::TABLE,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );

        $qb->addSelect('category_info.url AS category_url')->addGroupBy('category_info.url');
        $qb->leftJoin(
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

        /* Свойства, учавствующие в карточке */

        $qb->leftJoin(
            'category_section',
            CategoryEntity\Section\Field\ProductCategorySectionField::TABLE,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.public = TRUE OR category_section_field.name = TRUE )'
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

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
				
					'0', category_section_field.sort, /* сортирвока */
				
					'field_uid', category_section_field.id,
					'field_name', category_section_field.name,
					'field_alternative', category_section_field.alternative,
					'field_public', category_section_field.public,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)
			
		)
			AS category_section_field"
        );

        $qb->where('product.id = :product');
        $qb->setParameter('product', $product, ProductUid::TYPE);
        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        //$qb->select('id');
        //$qb->from(ClasssName::TABLE, 'wb_order');

        /*dd($qb->fetchAssociative());*/


        return $qb
            ->enableCache('products-product', 86400)
            ->fetchAssociative();

    }


    /**
     * Метод возвращает детальную информацию о продукте и его заполненному значению ТП, вариантов и модификаций.
     *
     * @param ?string $offer - значение торгового предложения
     * @param ?string $variation - значение множественного варианта ТП
     * @param ?string $modification - значение модификации множественного варианта ТП
     */
    public function fetchProductEventAssociative(
        ProductEventUid $event,
        string $offer = null,
        string $variation = null,
        string $modification = null,
    ): array|bool
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('product_event.main')->groupBy('product.id');
        $qb->addSelect('product_event.id')->addGroupBy('product.event');

        $qb->from(ProductEntity\Event\ProductEvent::TABLE, 'product_event');
        $qb->where('product_event.id = :product');
        $qb->setParameter('product', $event, ProductEventUid::TYPE);

        $qb->addSelect('product_active.active')->addGroupBy('product_active.active');
        $qb->addSelect('product_active.active_from')->addGroupBy('product_active.active_from');
        $qb->addSelect('product_active.active_to')->addGroupBy('product_active.active_to');

        $qb->join(
            'product',
            ProductEntity\Active\ProductActive::TABLE,
            'product_active',
            '
			product_active.event = product.event
			
			'
        );

        $qb->addSelect('product_trans.name AS product_name')->addGroupBy('product_trans.name');

        $qb->leftJoin(
            'product_event',
            ProductEntity\Trans\ProductTrans::TABLE,
            'product_trans',
            'product_trans.event = product_event.id AND product_trans.local = :local'
        );



        $qb->addSelect('product_desc.preview AS product_preview')->addGroupBy('product_desc.preview');
        $qb->addSelect('product_desc.description AS product_description')->addGroupBy('product_desc.description');

        $qb
            ->leftJoin(
                'product_event',
                ProductEntity\Description\ProductDescription::TABLE,
                'product_desc',
                'product_desc.event = product_event.id AND product_desc.device = :device '

            )->setParameter('device', 'pc');

        /* Базовая Цена товара */
        $qb->leftJoin(
            'product_event',
            ProductEntity\Price\ProductPrice::TABLE,
            'product_price',
            'product_price.event = product_event.id'
        )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency')
            ->addGroupBy('product_price.quantity')
            ->addGroupBy('product_price.reserve')
            ->addGroupBy('product_price.reserve');

        /* ProductInfo */

        $qb->addSelect('product_info.url')->addGroupBy('product_info.url');

        $qb->leftJoin(
            'product_event',
            ProductEntity\Info\ProductInfo::TABLE,
            'product_info',
            'product_info.product = product.id '
        )->addGroupBy('product_info.article');

        /* Торговое предложение */

        $qb->addSelect('product_offer.id as product_offer_uid')->addGroupBy('product_offer.id');
        $qb->addSelect('product_offer.value as product_offer_value')->addGroupBy('product_offer.value');
        $qb->addSelect('product_offer.postfix as product_offer_postfix')->addGroupBy('product_offer.postfix');
        $qb->leftJoin(
            'product_event',
            ProductEntity\Offers\ProductOffer::TABLE,
            'product_offer',
            'product_offer.event = product_event.id '.($offer ? ' AND product_offer.value = :product_offer_value' : '').' '
        )
            ->addGroupBy('product_offer.article');

        if($offer)
        {
            $qb->setParameter('product_offer_value', $offer);
        }

        /* Цена торгового предо жения */
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_price.price')
            ->addGroupBy('product_offer_price.currency');

        /* Получаем тип торгового предложения */
        $qb->addSelect('category_offer.reference AS product_offer_reference')->addGroupBy('category_offer.reference');
        $qb->leftJoin(
            'product_offer',
            CategoryEntity\Offers\ProductCategoryOffers::TABLE,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );

        /* Получаем название торгового предложения */
        $qb->addSelect('category_offer_trans.name as product_offer_name')->addGroupBy('category_offer_trans.name');
        $qb->addSelect('category_offer_trans.postfix as product_offer_name_postfix')->addGroupBy('category_offer_trans.postfix');
        $qb->leftJoin(
            'category_offer',
            CategoryEntity\Offers\Trans\ProductCategoryOffersTrans::TABLE,
            'category_offer_trans',
            'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
        );

        /* Наличие и резерв торгового предложения */
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Quantity\ProductOfferQuantity::TABLE,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_quantity.quantity')
            ->addGroupBy('product_offer_quantity.reserve');

        //ProductCategoryOffers

        /* Множественные варианты торгового предложения */

        $qb->addSelect('product_offer_variation.id as product_variation_uid')->addGroupBy('product_offer_variation.id');
        $qb->addSelect('product_offer_variation.value as product_variation_value')->addGroupBy('product_offer_variation.value');
        $qb->addSelect('product_offer_variation.postfix as product_variation_postfix')->addGroupBy('product_offer_variation.postfix');

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\ProductVariation::TABLE,
            'product_offer_variation',
            'product_offer_variation.offer = product_offer.id'.($variation ? ' AND product_offer_variation.value = :product_variation_value' : '').' '
        )
            ->addGroupBy('product_offer_variation.article');

        if($variation)
        {
            $qb->setParameter('product_variation_value', $variation);
        }

        /* Цена множественного варианта */
        $qb->leftJoin(
            'product_offer_variation',
            ProductEntity\Offers\Variation\Price\ProductVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_price.price')
            ->addGroupBy('product_variation_price.currency');

        /* Получаем тип множественного варианта */
        $qb->addSelect('category_offer_variation.reference as product_variation_reference')
            ->addGroupBy('category_offer_variation.reference');
        $qb->leftJoin(
            'product_offer_variation',
            CategoryEntity\Offers\Variation\ProductCategoryVariation::TABLE,
            'category_offer_variation',
            'category_offer_variation.id = product_offer_variation.category_variation'
        );

        /* Получаем название множественного варианта */
        $qb->addSelect('category_offer_variation_trans.name as product_variation_name')
            ->addGroupBy('category_offer_variation_trans.name');

        $qb->addSelect('category_offer_variation_trans.postfix as product_variation_name_postfix')
            ->addGroupBy('category_offer_variation_trans.postfix');
        $qb->leftJoin(
            'category_offer_variation',
            CategoryEntity\Offers\Variation\Trans\ProductCategoryVariationTrans::TABLE,
            'category_offer_variation_trans',
            'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local'
        );

        /* Наличие и резерв множественного варианта */
        $qb->leftJoin(
            'category_offer_variation',
            ProductEntity\Offers\Variation\Quantity\ProductVariationQuantity::TABLE,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_quantity.quantity')
            ->addGroupBy('product_variation_quantity.reserve');

        /* Модификация множественного варианта торгового предложения */

        $qb->addSelect('product_offer_modification.id as product_modification_uid')->addGroupBy('product_offer_modification.id');
        $qb->addSelect('product_offer_modification.value as product_modification_value')->addGroupBy('product_offer_modification.value');
        $qb->addSelect('product_offer_modification.postfix as product_modification_postfix')->addGroupBy('product_offer_modification.postfix');

        $qb->leftJoin(
            'product_offer_variation',
            ProductEntity\Offers\Variation\Modification\ProductModification::TABLE,
            'product_offer_modification',
            'product_offer_modification.variation = product_offer_variation.id'.($modification ? ' AND product_offer_modification.value = :product_modification_value' : '').' '
        )
            ->addGroupBy('product_offer_modification.article');

        if($modification)
        {
            $qb->setParameter('product_modification_value', $modification);
        }

        /* Цена модификации множественного варианта */
        $qb->leftJoin(
            'product_offer_modification',
            ProductEntity\Offers\Variation\Modification\Price\ProductModificationPrice::TABLE,
            'product_modification_price',
            'product_modification_price.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_price.price')
            ->addGroupBy('product_modification_price.currency');

        /* Получаем тип модификации множественного варианта */
        $qb->addSelect('category_offer_modification.reference as product_modification_reference')
            ->addGroupBy('category_offer_modification.reference');
        $qb->leftJoin(
            'product_offer_modification',
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::TABLE,
            'category_offer_modification',
            'category_offer_modification.id = product_offer_modification.category_modification'
        );

        /* Получаем название типа модификации */
        $qb->addSelect('category_offer_modification_trans.name as product_modification_name')
            ->addGroupBy('category_offer_modification_trans.name');

        $qb->addSelect('category_offer_modification_trans.postfix as product_modification_name_postfix')
            ->addGroupBy('category_offer_modification_trans.postfix');
        $qb->leftJoin(
            'category_offer_modification',
            CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryModificationTrans::TABLE,
            'category_offer_modification_trans',
            'category_offer_modification_trans.modification = category_offer_modification.id AND category_offer_modification_trans.local = :local'
        );

        /* Наличие и резерв модификации множественного варианта */
        $qb->leftJoin(
            'category_offer_modification',
            ProductEntity\Offers\Variation\Modification\Quantity\ProductModificationQuantity::TABLE,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_quantity.quantity')
            ->addGroupBy('product_modification_quantity.reserve');

        //$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");

        /* Артикул продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_offer_modification.article IS NOT NULL THEN product_offer_modification.article
			   WHEN product_offer_variation.article IS NOT NULL THEN product_offer_variation.article
			   WHEN product_offer.article IS NOT NULL THEN product_offer.article
			   WHEN product_info.article IS NOT NULL THEN product_info.article
			   ELSE NULL
			END AS product_article
		'
        );

        /* Фото модификаций */

        $qb->leftJoin(
            'product_offer_modification',
            ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE,
            'product_offer_modification_image',
            '
			product_offer_modification_image.modification = product_offer_modification.id
			'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE WHEN product_offer_modification_image.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_modification_image.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Offers\Variation\Modification\Image\ProductModificationImage::TABLE."' , '/', product_offer_modification_image.name),
						'product_img_ext', product_offer_modification_image.ext,
						'product_img_cdn', product_offer_modification_image.cdn
						

					) END
			) AS product_modification_image
	"
        );

        /* Фото вариантов */

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE,
            'product_offer_variation_image',
            '
			product_offer_variation_image.variation = product_offer_variation.id
			'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE WHEN product_offer_variation_image.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_variation_image.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Offers\Variation\Image\ProductVariationImage::TABLE."' , '/', product_offer_variation_image.name),
						'product_img_ext', product_offer_variation_image.ext,
						'product_img_cdn', product_offer_variation_image.cdn
						

					) END
			) AS product_variation_image
	"
        );

        /* Фот оторговых предложений */

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Image\ProductOfferImage::TABLE,
            'product_offer_images',
            '
			
			product_offer_images.offer = product_offer.id
			
		'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
				CASE WHEN product_offer_images.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_offer_images.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Offers\Image\ProductOfferImage::TABLE."' , '/', product_offer_images.name),
						'product_img_ext', product_offer_images.ext,
						'product_img_cdn', product_offer_images.cdn
						

					) END

				 /*ORDER BY product_photo.root DESC, product_photo.id*/
			) AS product_offer_images
	"
        );

        /* Фот опродукта */

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Photo\ProductPhoto::TABLE,
            'product_photo',
            '
	
			product_photo.event = product_event.id
			'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT

					CASE WHEN product_photo.ext IS NOT NULL THEN
					JSONB_BUILD_OBJECT
					(
						'product_img_root', product_photo.root,
						'product_img', CONCAT ( '/upload/".ProductEntity\Photo\ProductPhoto::TABLE."' , '/', product_photo.name),
						'product_img_ext', product_photo.ext,
						'product_img_cdn', product_photo.cdn
						

					) END

				 /*ORDER BY product_photo.root DESC, product_photo.id*/
			) AS product_photo
	"
        );

        /* Стоимость продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.price
			   ELSE NULL
			END AS product_price
		'
        );

        /* Валюта продукта */

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		'
        );

        /* Наличие продукта */

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
        );

        //		->addGroupBy('product_modification_quantity.quantity')
        //		->addGroupBy('product_modification_quantity.reserve')

        /* Наличие */
        //		$qb->addSelect("
        //			CASE
        //			   WHEN product_modification_price.price IS NOT NULL THEN product_modification_price.price
        //			   WHEN product_variation_price.price IS NOT NULL THEN product_variation_price.price
        //			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.price
        //			   WHEN product_price.price IS NOT NULL THEN product_price.price
        //			   ELSE NULL
        //			END AS product_price
        //		"
        //		);

        /* Категория */
        $qb->join(
            'product_event',
            ProductEntity\Category\ProductCategory::TABLE,
            'product_event_category',
            'product_event_category.event = product_event.id AND product_event_category.root = true'
        );

        //$qb->andWhere('product_event_category.category = :category');
        //$qb->setParameter('category', $category, ProductCategoryUid::TYPE);

        $qb->join(
            'product_event_category',
            CategoryEntity\ProductCategory::TABLE,
            'category',
            'category.id = product_event_category.category'
        );

        $qb->addSelect('category_trans.name AS category_name')->addGroupBy('category_trans.name');

        $qb->leftJoin(
            'category',
            CategoryEntity\Trans\ProductCategoryTrans::TABLE,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );

        $qb->addSelect('category_info.url AS category_url')->addGroupBy('category_info.url');
        $qb->leftJoin(
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

        /* Свойства, учавствующие в карточке */

        $qb->leftJoin(
            'category_section',
            CategoryEntity\Section\Field\ProductCategorySectionField::TABLE,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.public = TRUE OR category_section_field.name = TRUE )'
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

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
				
					'0', category_section_field.sort, /* сортирвока */
				
					'field_uid', category_section_field.id,
					'field_name', category_section_field.name,
					'field_alternative', category_section_field.alternative,
					'field_public', category_section_field.public,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)
			
		)
			AS category_section_field"
        );


        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);


        /* Кешируем результат DBAL */
        return $qb
            ->enableCache('products-product', 86400)
            ->fetchAssociative();

    }

}
