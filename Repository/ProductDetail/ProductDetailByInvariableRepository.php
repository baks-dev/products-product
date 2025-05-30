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
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use BaksDev\Products\Product\Entity\Category\ProductCategory;

final class ProductDetailByInvariableRepository implements ProductDetailByInvariableInterface
{
    private ProductInvariableUid|false $invariable = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function invariable(ProductInvariable|ProductInvariableUid|string $invariable): self
    {
        if(empty($invariable))
        {
            $this->invariable = false;
            return $this;
        }

        if(is_string($invariable))
        {
            $invariable = new ProductInvariableUid($invariable);
        }

        if($invariable instanceof ProductInvariable)
        {
            $invariable = $invariable->getId();
        }

        $this->invariable = $invariable;

        return $this;
    }

    /**
     * Метод возвращает детальную информацию о продукте по его invariable
     */
    public function find(): ProductDetailByInvariableResult|false
    {
        if(false === ($this->invariable instanceof ProductInvariableUid))
        {
            throw new InvalidArgumentException('Invalid Argument product');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(ProductInvariable::class, 'invariable')
            ->where('invariable.id = :id')
            ->setParameter(
                key: 'id',
                value: $this->invariable,
                type: ProductInvariableUid::TYPE
            );

        $dbal->join(
            'invariable',
            Product::class,
            'product',
            'product.id = invariable.product'
        );

        $dbal
            ->select('product_event.id as product_event')
            ->join(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );

        /* Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        );

        /* Базовый артикул продукта и стоимость */
        $dbal->join(
            'product',
            ProductInfo::class,
            'product_info',
            'product_info.product = product.id '
        );

        /**
         * Торговое предложение
         */
        $dbal
            ->addSelect('product_offer.id as product_offer_id')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                '
                    product_offer.event = product.event AND
                    product_offer.const = invariable.offer
                '
            );

        $dbal
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local'
            );

        /* Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference AS product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /**
         * Множественные варианты торгового предложения
         */
        $dbal
            ->addSelect('product_variation.id as product_variation_id')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->leftJoin(
                'product',
                ProductVariation::class,
                'product_variation',
                'product_variation.const = invariable.variation'
            );

        /* Получаем тип множественного варианта */
        $dbal
            ->addSelect('category_offer_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_offer_variation',
                'category_offer_variation.id = product_variation.category_variation'
            );

        /**
         * Модификация множественного варианта торгового предложения
         */
        $dbal
            ->addSelect('product_modification.id as product_modification_id')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->leftJoin(
                'product',
                ProductModification::class,
                'product_modification',
                'product_modification.const = invariable.modification'
            );

        /* Получаем тип модификации множественного варианта */
        $dbal
            ->addSelect('category_offer_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_offer_modification',
                'category_offer_modification.id = product_modification.category_modification'
            );

        /* Артикул продукта */
        $dbal->addSelect('
			COALESCE(
                product_modification.article,
                product_variation.article,
                product_offer.article,
                product_info.article
            ) AS product_article
		');

        /**
         *  Фото продукта
         */

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
            'product_variation',
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
        $dbal->addSelect('
            COALESCE(
                product_modification_image.ext,
                product_variation_image.ext,
                product_offer_images.ext,
                product_photo.ext
            ) AS product_image_ext
        ');


        /** Флаг загрузки файла CDN */
        $dbal->addSelect('
            COALESCE(
                product_modification_image.cdn,
                product_variation_image.cdn,
                product_offer_images.cdn,
                product_photo.cdn
            ) AS product_image_cdn
        ');


        /* Категория */
        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true'
        );

        $dbal->leftJoin(
            'product_event_category',
            CategoryProduct::class,
            'category',
            'category.id = product_event_category.category'
        );

        /*$dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event'
        );*/

        /* Свойства, участвующие в карточке */
        /*$dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            '
                category_section_field.section = category_section.id AND
                (category_section_field.public = TRUE OR category_section_field.name = TRUE )
            '
        );*/

        /*$dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            '
                category_section_field_trans.field = category_section_field.id AND
                category_section_field_trans.local = :local
            '
        );*/

        /*$dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.const'
        );*/


        //        $dbal->addSelect(
        //            "JSON_AGG (DISTINCT
        //
        //                    JSONB_BUILD_OBJECT
        //                    (
        //                        '0', category_section_field.sort,
        //
        //                        'field_uid', category_section_field.id,
        //                        'field_const', category_section_field.const,
        //                        'field_name', category_section_field.name,
        //                        'field_alternative', category_section_field.alternative,
        //                        'field_public', category_section_field.public,
        //                        'field_card', category_section_field.card,
        //                        'field_type', category_section_field.type,
        //                        'field_trans', category_section_field_trans.name,
        //                        'field_value', product_property.value
        //                    )
        //            )
        //			AS category_section_field"
        //        );


        $dbal->addSelect('NULL AS category_section_field');

        /* Название продукта */
        $dbal->addSelect('
            COALESCE(
                product_offer.name,
                product_trans.name
            ) AS product_name
		');

        $dbal->allGroupByExclude();

        return $dbal
            ->enableCache('products-product')
            ->fetchHydrate(ProductDetailByInvariableResult::class);
    }
}