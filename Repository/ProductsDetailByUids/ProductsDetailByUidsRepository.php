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

namespace BaksDev\Products\Product\Repository\ProductsDetailByUids;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Trans\CategoryProductOffersTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\Trans\CategoryProductModificationTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\Trans\CategoryProductVariationTrans;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
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
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Doctrine\DBAL\ArrayParameterType;
use Generator;
use InvalidArgumentException;

final class ProductsDetailByUidsRepository implements ProductsDetailByUidsInterface
{

    private array|bool $events = false;

    private array|bool $offers = false;

    private array|bool $variations = false;

    private array|bool $modifications = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}


    public function events(array $events): ProductsDetailByUidsInterface
    {
        foreach($events as $event)
        {

            if(is_string($event))
            {
                $event = new ProductEventUid($event);
            }

            if($event instanceof ProductEvent)
            {
                $event = $event->getId();
            }

            $this->events[] = $event;
        }

        return $this;
    }

    public function offers(array $offers): ProductsDetailByUidsInterface
    {
        foreach($offers as $offer)
        {

            if(is_string($offer))
            {
                $offer = new ProductOfferUid($offer);
            }

            if($offer instanceof ProductOffer)
            {
                $offer = $offer->getId();
            }

            if($offer)
            {
                $this->offers[] = $offer;
            }
        }
        return $this;
    }

    public function variations(array $variations): ProductsDetailByUidsInterface
    {
        foreach($variations as $variation)
        {

            if(is_string($variation))
            {
                $variation = new ProductVariationUid($variation);
            }

            if($variation instanceof ProductVariation)
            {
                $variation = $variation->getId();
            }

            if($variation)
            {
                $this->variations[] = $variation;
            }
        }

        return $this;
    }

    public function modifications(array $modifications): ProductsDetailByUidsInterface
    {
        foreach($modifications as $modification)
        {

            if(is_string($modification))
            {
                $modification = new ProductModificationUid($modification);
            }

            if($modification instanceof ProductModification)
            {
                $modification = $modification->getId();
            }

            if($modification)
            {
                $this->modifications[] = $modification;
            }
        }

        return $this;
    }


    public function findAll(): Generator|false
    {
        $dbal = $this->builder();

        $dbal->enableCache('products-product', 86400);

        $result = $dbal->fetchAllHydrate(ProductsDetailByUidsResult::class);

        return (true === $result->valid()) ? $result : false;
    }

    public function toArray(): array|false
    {
        $result = $this->findAll();

        return ($result) ? iterator_to_array($result) : false;
    }


    /**
     * Метод возвращает детальную информацию о продуктая по их идентификаторам события, ТП, вариантов и модификаций.
     */
    public function builder(): DBALQueryBuilder
    {

        if(false === $this->events)
        {
            throw new InvalidArgumentException('Invalid Argument ProductEvent');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->select('product_event.main');
        $dbal->addSelect('product_event.id');

        $dbal
            ->from(ProductEvent::class, 'product_event');

        /* Базовая Цена товара */

        $dbal->leftJoin(
            'product_event',
            ProductPrice::class,
            'product_price',
            'product_price.event = product_event.id'
        );

        /* ProductInfo */

        $dbal
            ->addSelect('product_info.url')
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product_event.main '
            );
        

        /* Торговое предложение */

        // если есть оффер TODO

        if($this->offers)
        {
            $dbal
                ->addSelect('product_offer.id as product_offer_uid')
                ->addSelect('product_offer.value as product_offer_value')
                ->addSelect('product_offer.postfix as product_offer_postfix');

            $dbal->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
            );
        }

        $dbal
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local'
            );

        /* Название продукта */

        $product_offer_name = $this->offers ? 'product_offer.name, ' : '';

        $dbal->addSelect('
            COALESCE(
                '.$product_offer_name.'
                product_trans.name
            ) AS product_name
		');


        /* Цена торгового предоложения */
        if($this->offers)
        {
            $dbal->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id'
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

            /* Получаем название торгового предложения */
            $dbal
                ->addSelect('category_offer_trans.name as product_offer_name')
                ->leftJoin(
                    'category_offer',
                    CategoryProductOffersTrans::class,
                    'category_offer_trans',
                    'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
                );

            /* Наличие и резерв торгового предложения */
            $dbal->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id'
            );
        }

        /* Множественные варианты торгового предложения */

        // Если отмечены "Множественные Варианты торгового предложения"
        if($this->variations)
        {
            $dbal
                ->addSelect('product_variation.id as product_variation_uid')
                ->addSelect('product_variation.value as product_variation_value')
                ->addSelect('product_variation.postfix as product_variation_postfix');

            $dbal->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );


            /* Цена множественного варианта */
            $dbal->leftJoin(
                'product_variation',
                ProductVariationPrice::class,
                'product_variation_price',
                'product_variation_price.variation = product_variation.id'
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

            /* Получаем название множественного варианта */
            $dbal
                ->addSelect('category_offer_variation_trans.name as product_variation_name')
                ->leftJoin(
                    'category_offer_variation',
                    CategoryProductVariationTrans::class,
                    'category_offer_variation_trans',
                    'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local'
                );


            /* Наличие и резерв множественного варианта */
            $dbal->leftJoin(
                'category_offer_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id'
            );
        }

        /* Модификация множественного варианта торгового предложения */

        if($this->modifications)
        {
            $dbal
                ->addSelect('product_modification.id as product_modification_uid')
                ->addSelect('product_modification.value as product_modification_value')
                ->addSelect('product_modification.postfix as product_modification_postfix');

            $dbal->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id '
            );


            /* Цена модификации множественного варианта */
            $dbal->leftJoin(
                'product_modification',
                ProductModificationPrice::class,
                'product_modification_price',
                'product_modification_price.modification = product_modification.id'
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

            /* Получаем название типа модификации */
            $dbal
                ->addSelect('category_offer_modification_trans.name as product_modification_name')
                ->leftJoin(
                    'category_offer_modification',
                    CategoryProductModificationTrans::class,
                    'category_offer_modification_trans',
                    '
            category_offer_modification_trans.modification = category_offer_modification.id AND 
            category_offer_modification_trans.local = :local'
                );

            /* Наличие и резерв модификации множественного варианта */
            $dbal->leftJoin(
                'category_offer_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id'
            );

        }

        /* Артикул продукта */

        //        $dbal->addSelect('
        //            COALESCE(
        //                product_modification.article,
        //                product_variation.article,
        //                product_offer.article,
        //                product_info.article
        //            ) AS product_article
        //		');

        $modification_article = $this->modifications ? 'product_modification.article,' : '';
        $variation_article = $this->variations ? 'product_variation.article,' : '';
        $offer_article = $this->offers ? 'product_offer.article,' : '';
        $dbal->addSelect('
            COALESCE('.
            $modification_article.
            $variation_article.
            $offer_article.
            'product_info.article
            ) AS product_article
        ');


        /* Фото продукта */

        $dbal->leftJoin(
            'product_event',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product_event.id AND product_photo.root = true'
        );

        if($this->offers)
        {
            $dbal->leftJoin(
                'product_offer',
                ProductOfferImage::class,
                'product_offer_images',
                'product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
            );
        }

        if($this->variations)
        {
            $dbal->leftJoin(
                'product_variation',
                ProductVariationImage::class,
                'product_variation_image',
                'product_variation_image.variation = product_variation.id AND product_variation_image.root = true'
            );
        }

        if($this->modifications)
        {
            $dbal->leftJoin(
                'product_modification',
                ProductModificationImage::class,
                'product_modification_image',
                'product_modification_image.modification = product_modification.id AND product_modification_image.root = true'
            );
        }


        /* Задать условия для формирования поля 'product_image' для: */
        /* product_modification_image.name, product_variation_image.name, product_offer_images.name */
        /* в зависимости от того указано ли соотвествующее сво-во: modification, offer, variation в процессе производства (action) */
        $modification_image_expr = $this->modifications ?
            "WHEN product_modification_image.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name)" : '';

        $variation_image_expr = $this->variations ?
            "WHEN product_variation_image.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)" : '';

        $offer_image_expr = $this->offers ?
            "WHEN product_offer_images.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)" : '';


        $dbal->addSelect("
                CASE
   
                    $modification_image_expr
                    $variation_image_expr
                    $offer_image_expr
                    WHEN product_photo.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
                    ELSE NULL
   
                END AS product_image
            ");


        /* Расширение файла */

        /* Задать условия для формирования полей 'product_image_ext' и 'product_image_cdn' для: */
        /* product_modification_image.ext, product_variation_image.ext, product_offer_images.ext */
        /* в зависимости от того указано ли соотвествующее сво-во: modification, offer,variation в процессе производства (action) */
        $modification_image_ext = $this->modifications ? 'product_modification_image.ext,' : '';

        $variation_image_ext = $this->variations ? 'product_variation_image.ext,' : '';

        $offer_image_ext = $this->offers ? 'product_offer_images.ext,' : '';

        $dbal->addSelect(
            '
            COALESCE('.
            $modification_image_ext.
            $variation_image_ext.
            $offer_image_ext.
            'product_photo.ext
            ) AS product_image_ext',
        );

        /* Флаг загрузки файла CDN */

        $modification_image_cdn = $this->modifications ? 'product_modification_image.cdn,' : '';

        $variation_image_cdn = $this->variations ? 'product_variation_image.cdn,' : '';

        $offer_image_cdn = $this->offers ? 'product_offer_images.cdn,' : '';

        $dbal->addSelect(
            '
            COALESCE('.
            $modification_image_cdn.
            $variation_image_cdn.
            $offer_image_cdn.
            'product_photo.cdn
            ) AS product_image_cdn',
        );


        /* Категория */
        $dbal->join(
            'product_event',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product_event.id AND product_event_category.root = true'
        );


        $dbal->join(
            'product_event_category',
            CategoryProduct::class,
            'category',
            'category.id = product_event_category.category'
        );

        $dbal->addSelect('category_trans.name AS category_name')->addGroupBy('category_trans.name');

        $dbal->leftJoin(
            'category',
            CategoryProductTrans::class,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );

        $dbal->addSelect('category_info.url AS category_url')->addGroupBy('category_info.url');
        $dbal->leftJoin(
            'category',
            CategoryProductInfo::class,
            'category_info',
            'category_info.event = category.event'
        );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event'
        );

        /* Свойства, участвующие в карточке */

        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.public = TRUE OR category_section_field.name = TRUE )'
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
            'product_property.event = product_event.id AND product_property.field = category_section_field.const'
        );

        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort, /* сортировка  */
				
					'field_uid', category_section_field.id,
					'field_const', category_section_field.const,
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


        /* Добавление массивов офферов  */
        if($this->events !== false)
        {
            /** Product Event */
            $dbal
                ->where('product_event.id in (:events)')
                ->setParameter(
                    key: 'events',
                    value: $this->events,
                    type: ArrayParameterType::STRING
                );
        }

        if($this->offers !== false)
        {
            /** Торговые предложения */
            $dbal
                ->where('product_offer.id in (:offers)')
                ->setParameter(
                    key: 'offers',
                    value: $this->offers,
                    type: ArrayParameterType::STRING
                );
        }

        if($this->variations !== false)
        {
            /** Множественные варианты торгового предложения */
            $dbal
                ->where('product_variation.id in (:variations)')
                ->setParameter(
                    key: 'variations',
                    value: $this->variations,
                    type: ArrayParameterType::STRING
                );
        }

        if($this->modifications !== false)
        {
            /** Модификации множественного варианта */
            $dbal
                ->where('product_modification.id in (:modifications)')
                ->setParameter(
                    key: 'modifications',
                    value: $this->modifications,
                    type: ArrayParameterType::STRING
                );
        }

        $dbal->allGroupByExclude();

        return $dbal;

    }

}
