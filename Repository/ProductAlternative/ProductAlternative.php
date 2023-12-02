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

namespace BaksDev\Products\Product\Repository\ProductAlternative;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Category\Type\Section\Field\Id\ProductCategorySectionFieldUid;
use BaksDev\Products\Product\Entity as ProductEntity;
use stdClass;

final class ProductAlternative implements ProductAlternativeInterface
{

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder) {

        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function fetchAllAlternativeAssociative(
        string $offer,
        ?string $variation,
        ?string $modification,
        ?array $property = null
    ): ?array {

        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

        $qb->addSelect('product_offer.value as product_offer_value')
            //->addGroupBy('product_offer.value')
            //->addGroupBy('product_offer.article')
        ;

        $qb->addSelect('product_offer.postfix as product_offer_postfix')
            //->addGroupBy('product_offer.postfix')
        ;

        $qb->addSelect('product_offer.id as product_offer_uid')
            //->addGroupBy('product_offer.id')
        ;

        $qb->from(ProductEntity\Offers\ProductOffer::TABLE, 'product_offer');

        $qb->addSelect('product.id')
            //->addGroupBy('product.id ')
        ;
        $qb->addSelect('product.event')
            //->addGroupBy('product.event ')
        ;
        $qb->join(
            'product_offer',
            ProductEntity\Product::TABLE,
            'product',
            'product.event = product_offer.event'
        );

        // Цена торгового предожения
        // $qb->addSelect('product_offer_price.price');
        // $qb->addSelect('product_offer_price.currency');
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            //->addGroupBy('product_offer_price.price')
            ->addGroupBy('product_offer_price.currency')
        ;

        // Получаем тип торгового предложения
        $qb->addSelect('category_offer.reference AS product_offer_reference')
            //->addGroupBy('category_offer.reference')
        ;
        $qb->leftJoin(
            'product_offer',
            CategoryEntity\Offers\ProductCategoryOffers::TABLE,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );

        // Получаем название торгового предложения
        $qb->addSelect('category_offer_trans.name as product_offer_name')
            //->addGroupBy('category_offer_trans.name')
        ;
        $qb->leftJoin(
            'category_offer',
            CategoryEntity\Offers\Trans\ProductCategoryOffersTrans::TABLE,
            'category_offer_trans',
            'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
        );

        // Наличие и резерв торгового предложения
        // $qb->addSelect('product_offer_quantity.quantity');
        // $qb->addSelect('product_offer_quantity.reserve');
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Quantity\ProductOfferQuantity::TABLE,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        )
            //->addGroupBy('product_offer_quantity.quantity')
            ->addGroupBy('product_offer_quantity.reserve')
        ;

        // МНОЖЕСТВЕННЫЕ ВАРИАНТЫ

        $qb->addSelect('product_variation.value as product_variation_value')
            //->addGroupBy('product_variation.value')
            //->addGroupBy('product_variation.article')
        ;

        $qb->addSelect('product_variation.postfix as product_variation_postfix')
            //->addGroupBy('product_variation.postfix')
        ;

        $qb->addSelect('product_variation.id as product_variation_uid')
            //->addGroupBy('product_variation.id')
        ;

        $variationMethod = empty($variation) ? 'leftJoin' : 'join';

        $qb->{$variationMethod}(
            'product_offer',
            ProductEntity\Offers\Variation\ProductVariation::TABLE,
            'product_variation',
            'product_variation.offer = product_offer.id '.(empty($variation) ? '' : 'AND product_variation.value = :variation')
        );

        if (!empty($variation)) {
            $qb->setParameter('variation', $variation);
        }

        // Цена множественного варианта
        $qb->leftJoin(
            'product_variation',
            ProductEntity\Offers\Variation\Price\ProductVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        )
            //->addGroupBy('product_variation_price.price')
            ->addGroupBy('product_variation_price.currency')
        ;

        // Получаем тип множественного варианта
        $qb->addSelect('category_offer_variation.reference as product_variation_reference')
            //->addGroupBy('category_offer_variation.reference')
        ;
        $qb->leftJoin(
            'product_variation',
            CategoryEntity\Offers\Variation\ProductCategoryVariation::TABLE,
            'category_offer_variation',
            'category_offer_variation.id = product_variation.category_variation'
        );

        // Получаем название множественного варианта
        $qb->addSelect('category_offer_variation_trans.name as product_variation_name')
            //->addGroupBy('category_offer_variation_trans.name')
        ;
        $qb->leftJoin(
            'category_offer_variation',
            CategoryEntity\Offers\Variation\Trans\ProductCategoryVariationTrans::TABLE,
            'category_offer_variation_trans',
            'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local'
        );

        // Наличие и резерв множественного варианта
        $qb->leftJoin(
            'category_offer_variation',
            ProductEntity\Offers\Variation\Quantity\ProductVariationQuantity::TABLE,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        )
            //->addGroupBy('product_variation_quantity.quantity')
            ->addGroupBy('product_variation_quantity.reserve')
        ;

        // МОДИФИКАЦИЯ
        $qb->addSelect('product_modification.value as product_modification_value')
            //->addGroupBy('product_modification.value')
            ->addGroupBy('product_modification.article')
        ;

        $qb->addSelect('product_modification.postfix as product_modification_postfix')
            ///->addGroupBy('product_modification.postfix')
        ;

        $qb->addSelect('product_modification.id as product_modification_uid')
            //->addGroupBy('product_modification.id')
        ;

        $modificationMethod = empty($modification) ? 'leftJoin' : 'join';

        $qb->{$modificationMethod}(
            'product_variation',
            ProductEntity\Offers\Variation\Modification\ProductModification::TABLE,
            'product_modification',
            'product_modification.variation = product_variation.id '.(empty($modification) ? '' : 'AND product_modification.value = :modification')
        );

        if (!empty($modification)) {
            $qb->setParameter('modification', $modification);
        }

        // Цена модификации множественного варианта
        $qb->leftJoin(
            'product_modification',
            ProductEntity\Offers\Variation\Modification\Price\ProductModificationPrice::TABLE,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        )
            //->addGroupBy('product_modification_price.price')
            ->addGroupBy('product_modification_price.currency')
        ;

        // Получаем тип модификации множественного варианта
        $qb->addSelect('category_offer_modification.reference as product_modification_reference')
            //->addGroupBy('category_offer_modification.reference')
        ;
        $qb->leftJoin(
            'product_modification',
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::TABLE,
            'category_offer_modification',
            'category_offer_modification.id = product_modification.category_modification'
        );

        // Получаем название типа модификации
        $qb->addSelect('category_offer_modification_trans.name as product_modification_name')
            //->addGroupBy('category_offer_modification_trans.name')
        ;
        $qb->leftJoin(
            'category_offer_modification',
            CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryModificationTrans::TABLE,
            'category_offer_modification_trans',
            'category_offer_modification_trans.modification = category_offer_modification.id AND category_offer_modification_trans.local = :local'
        );

        // Наличие и резерв модификации множественного варианта
        $qb->leftJoin(
            'category_offer_modification',
            ProductEntity\Offers\Variation\Modification\Quantity\ProductModificationQuantity::TABLE,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        )
            //->addGroupBy('product_modification_quantity.quantity')
            ->addGroupBy('product_modification_quantity.reserve')
        ;

        // Артикул продукта

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification.article IS NOT NULL THEN product_modification.article
			   WHEN product_variation.article IS NOT NULL THEN product_variation.article
			   WHEN product_offer.article IS NOT NULL THEN product_offer.article
			   WHEN product_info.article IS NOT NULL THEN product_info.article
			   ELSE NULL
			END AS article
		'
        );

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.price
			   ELSE NULL
			END AS price
		'
        );

        // Валюта продукта

        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.currency
			   ELSE NULL
			END AS currency
		'
        );

        // Наличие продукта

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
			END AS quantity
		'
        );

        // СВОЙСТВА, УЧАВСТВУЮЩИЕ В ФИЛЬТРЕ АЛЬТЕРНАТИВ

        if ($property) {

            /** @var stdClass $props */
            foreach ($property as $props) {

                if(empty($props->field_uid))
                {
                    continue;
                }

                $alias = md5($props->field_uid);

                $qb->join(
                    'product_offer',
                    ProductEntity\Property\ProductProperty::TABLE,
                    'product_property_'.$alias,
                    'product_property_'.$alias.'.event = product_offer.event AND product_property_'.$alias.'.field = :field_'.$alias.' AND product_property_'.$alias.'.value = :props_'.$alias
                );

                $qb->setParameter(
                    'field_'.$alias,
                    new ProductCategorySectionFieldUid($props->field_uid),
                    ProductCategorySectionFieldUid::TYPE
                );


                $qb->setParameter('props_'.$alias, $props->field_value);
            }
        }





        $qb->join(
            'product',
            ProductEntity\Event\ProductEvent::TABLE,
            'product_event',
            'product_event.id = product.event'
        );

        // Проверяем активность продукции
        $qb->addSelect('product_active.active_from')
            //->addGroupBy('product_active.active_from')
        ;

        $qb->join(
            'product',
            ProductEntity\Active\ProductActive::TABLE,
            'product_active',
            'product_active.event = product.event AND product_active.active = true AND product_active.active_from < NOW()
			
			AND (
				CASE
				   WHEN product_active.active_to IS NOT NULL THEN product_active.active_to > NOW()
				   ELSE TRUE
				END
			)
		'
        );

        // Название твоара
        $qb->addSelect('product_trans.name AS product_name')
        //    ->addGroupBy('product_trans.name')
        ;

        $qb->leftJoin(
            'product_event',
            ProductEntity\Trans\ProductTrans::TABLE,
            'product_trans',
            'product_trans.event = product_event.id AND product_trans.local = :local'
        );

        // Базовая Цена товара
        $qb->leftJoin(
            'product_event',
            ProductEntity\Price\ProductPrice::TABLE,
            'product_price',
            'product_price.event = product_event.id'
        )
            ///->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency')
            //->addGroupBy('product_price.quantity')
            ->addGroupBy('product_price.reserve')
        ;

        $qb->addSelect('product_info.url AS product_url')
            //->addGroupBy('product_info.url')
            //->addGroupBy('product_info.article')
        ;

        $qb->leftJoin(
            'product_event',
            ProductEntity\Info\ProductInfo::TABLE,
            'product_info',
            'product_info.product = product.id '
        );

        // Категория
        $qb->join(
            'product_event',
            ProductEntity\Category\ProductCategory::TABLE,
            'product_event_category',
            'product_event_category.event = product_event.id AND product_event_category.root = true'
        );

        // $qb->andWhere('product_event_category.category = :category');
        // $qb->setParameter('category', $category, ProductCategoryUid::TYPE);

        $qb->join(
            'product_event_category',
            CategoryEntity\ProductCategory::TABLE,
            'category',
            'category.id = product_event_category.category'
        );

        $qb->addSelect('category_trans.name AS category_name')
            //->addGroupBy('category_trans.name')
        ;

        $qb->leftJoin(
            'category',
            CategoryEntity\Trans\ProductCategoryTrans::TABLE,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );

        $qb->addSelect('category_info.url AS category_url')
            //->addGroupBy('category_info.url')
        ;
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

        // Свойства, учавствующие в ПРЕВЬЮ

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
            'category_product_property',
            'category_product_property.event = product.event AND category_product_property.field = category_section_field.id'
        );

        $qb->addSelect(
            "JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
				
					'0', category_section_field.sort, /* сортирвока */
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', category_product_property.value
				)
			
		)
			AS category_section_field"
        );

        $qb->where('product_offer.value = :offer');
        $qb->setParameter('offer', $offer);

        $qb->allGroupByExclude();

        //GROUP BY product_modification.article, product_offer.value, product_offer.postfix, product_offer.id, product.id, product.event, category_offer.reference, category_offer_trans.name, product_variation.value, product_variation.postfix, product_variation.id, category_offer_variation.reference, category_offer_variation_trans.name, product_modification.value, product_modification.postfix, product_modification.id, category_offer_modification.reference, category_offer_modification_trans.name, article, price, currency, quantity, product_active.active_from, product_trans.name, product_info.url, category_trans.name, category_info.url

        //dd($qb->fetchAllAssociative());

        $qb->setMaxResults(1000);

        return $qb
            ->enableCache('products-product', 86400)
            ->fetchAllAssociative();
    }
}
