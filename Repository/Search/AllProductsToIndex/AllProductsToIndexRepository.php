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

namespace BaksDev\Products\Product\Repository\Search\AllProductsToIndex;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Search\Repository\DataToIndex\DataToIndexInterface;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

/**
 * Используется для команды индексации товаров, ТП, вариаций и модификаций
 */
final readonly class AllProductsToIndexRepository implements DataToIndexInterface
{
    public function __construct(
        private DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function toArray(): array|false
    {
        $result = $this->findAll();

        return (true === $result->valid()) ? iterator_to_array($result) : false;
    }

    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->from(Product::class, 'product');

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local',
            );

        /** ProductInfo */
        $dbal
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );


        /** Торговое предложение */
        $dbal
            ->addSelect('product_offer.id as product_offer_id')
            ->addSelect('product_offer.value as product_offer_value')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );

        /** Тип торгового предложения */
        //        $dbal
        //            ->leftJoin(
        //                'product_offer',
        //                CategoryProductOffers::class,
        //                'category_offer',
        //                'category_offer.id = product_offer.category_offer'
        //            );


        /** Множественные варианты торгового предложения */
        $dbal
            ->addSelect('product_variation.id as product_variation_id')
            ->addSelect('product_variation.value as product_variation_value')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );


        /** Тип множественного варианта торгового предложения */
        //        $dbal
        //            ->leftJoin(
        //                'product_variation',
        //                CategoryProductVariation::class,
        //                'category_variation',
        //                'category_variation.id = product_variation.category_variation'
        //            );

        /** Модификация множественного варианта */
        $dbal
            ->addSelect('product_modification.id as product_modification_id')
            ->addSelect('product_modification.value as product_modification_value')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id ',
            );

        /** Получаем тип модификации множественного варианта */
        //        $dbal
        //            ->leftJoin(
        //                'product_modification',
        //                CategoryProductModification::class,
        //                'category_modification',
        //                'category_modification.id = product_modification.category_modification'
        //            );


        /** Значения свойств */
        $dbal
            ->addSelect('JSON_AGG( DISTINCT product_property.value) AS property')
            ->leftJoin(
                'product',
                ProductProperty::class,
                'product_property',
                'product_property.event = product.event',
            );


        /** Артикул продукта */

        $dbal->addSelect("
            COALESCE(
                product_modification.article,
                product_variation.article,
                product_offer.article,
                product_info.article
            ) AS product_article
		");

        $dbal->allGroupByExclude();

        $dbal->enableCache('products-product', '1 day');

        $result = $dbal->fetchAllHydrate(AllProductsToIndexResult::class);

        return (true === $result->valid()) ? $result : false;
    }
}