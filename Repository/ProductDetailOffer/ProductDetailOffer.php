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

namespace BaksDev\Products\Product\Repository\ProductDetailOffer;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Type\Id\ProductUid;

final class ProductDetailOffer implements ProductDetailOfferInterface
{


    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder) {


        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /** Метод возвращает торговые предложения продукта */
    public function fetchProductOfferAssociative(
        ProductUid $product,

    ): array|bool {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('product.id');

        $qb->from(ProductEntity\Product::TABLE, 'product');

        $qb->join(
            'product',
            ProductEntity\Event\ProductEvent::TABLE,
            'product_event',
            'product_event.id = product.event'
        );

        /* Цена товара */
        $qb->leftJoin(
            'product_event',
            ProductEntity\Price\ProductPrice::TABLE,
            'product_price',
            'product_price.event = product_event.id'
        );

        /* Торговое предложение */

        $qb->addSelect('product_offer.value as product_offer_value');
        $qb->addSelect('product_offer.postfix as product_offer_postfix');
        $qb->addSelect('product_offer.category_offer as category_offer');
        $qb->leftJoin(
            'product_event',
            ProductEntity\Offers\ProductOffer::TABLE,
            'product_offer',
            'product_offer.event = product_event.id'
        );

        /* Цена торгового предожения */
        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            //->addGroupBy('product_offer_price.price')
            //->addGroupBy('product_offer_price.currency')
;

        /* Получаем тип торгового предложения */
        $qb->addSelect('category_offer.reference AS product_offer_reference');
        $qb->leftJoin(
            'product_offer',
            CategoryEntity\Offers\ProductCategoryOffers::TABLE,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );

        /* Получаем название торгового предложения */
        $qb->addSelect('category_offer_trans.name as product_offer_name');
        $qb->addSelect('category_offer_trans.postfix as product_offer_name_postfix');
        $qb->leftJoin(
            'category_offer',
            CategoryEntity\Offers\Trans\ProductCategoryOffersTrans::TABLE,
            'category_offer_trans',
            'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
        );

        /* Множественные варианты торгового предложения */

        $qb->addSelect('product_offer_variation.value as product_variation_value');
        $qb->addSelect('product_offer_variation.postfix as product_variation_postfix');

        $qb->leftJoin(
            'product_offer',
            ProductEntity\Offers\Variation\ProductVariation::TABLE,
            'product_offer_variation',
            'product_offer_variation.offer = product_offer.id'
        );

        /* Цена множественного варианта */
        $qb->leftJoin(
            'category_offer_variation',
            ProductEntity\Offers\Variation\Price\ProductOfferVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_offer_variation.id'
        );

        /* Получаем тип множественного варианта */
        $qb->addSelect('category_offer_variation.reference as product_variation_reference');

        $qb->leftJoin(
            'product_offer_variation',
            CategoryEntity\Offers\Variation\ProductCategoryVariation::TABLE,
            'category_offer_variation',
            'category_offer_variation.id = product_offer_variation.category_variation'
        );

        /* Получаем название множественного варианта */
        $qb->addSelect('category_offer_variation_trans.name as product_variation_name');
        $qb->addSelect('category_offer_variation_trans.postfix as product_variation_name_postfix');

        $qb->leftJoin(
            'category_offer_variation',
            CategoryEntity\Offers\Variation\Trans\ProductCategoryVariationTrans::TABLE,
            'category_offer_variation_trans',
            'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local'
        );

        /* Модификация множественного варианта торгового предложения */

        $qb->addSelect('product_offer_modification.value as product_modification_value');
        $qb->addSelect('product_offer_modification.postfix as product_modification_postfix');

        $qb->leftJoin(
            'product_offer_variation',
            ProductEntity\Offers\Variation\Modification\ProductModification::TABLE,
            'product_offer_modification',
            'product_offer_modification.variation = product_offer_variation.id'
        );

        /* Цена Модификации множественного варианта */
        $qb->leftJoin(
            'product_offer_modification',
            ProductEntity\Offers\Variation\Modification\Price\ProductModificationPrice::TABLE,
            'product_modification_price',
            'product_modification_price.modification = product_offer_modification.id'
        );

        /* Получаем тип множественного варианта */
        $qb->addSelect('category_offer_modification.reference as product_modification_reference');
        $qb->leftJoin(
            'product_offer_modification',
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::TABLE,
            'category_offer_modification',
            'category_offer_modification.id = product_offer_modification.category_modification'
        );

        /* Получаем название типа */
        $qb->addSelect('category_offer_modification_trans.name as product_modification_name');
        $qb->addSelect('category_offer_modification_trans.postfix as product_modification_name_postfix');
        $qb->leftJoin(
            'category_offer_modification',
            CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryModificationTrans::TABLE,
            'category_offer_modification_trans',
            'category_offer_modification_trans.modification = category_offer_modification.id AND category_offer_modification_trans.local = :local'
        );

        //$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");

        $qb->addSelect('product_variation_price.price');

        /* Стоимость продукта */
        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE NULL
			END AS product_price
		'
        );

        /* Валюта продукта */
        $qb->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		'
        );

        $qb->where('product.id = :product');
        $qb->setParameter('product', $product, ProductUid::TYPE);
        $qb->bindLocal();

        //$qb->select('id');
        //$qb->from(ClasssName::TABLE, 'wb_order');

        return $qb->enableCache('products-product', 86400)->fetchAllAssociative();
    }
}
