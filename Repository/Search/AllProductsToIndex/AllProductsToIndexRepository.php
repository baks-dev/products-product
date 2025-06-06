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
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
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
        $dbal = $this->builder();

        $dbal->enableCache('article', 86400);

        $result = $dbal->fetchAllHydrate(AllProductsToIndexResult::class);

        return (true === $result->valid()) ? $result : false;
    }

    public function builder(UserProfileUid|string|null $profile = null): DBALQueryBuilder
    {

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->from(Product::class, 'product');

        $dbal->leftJoin(
            'product',
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


        //        $dbal
        //            ->addSelect('product_desc.preview AS product_preview')
        //            ->addSelect('product_desc.description AS product_description')
        //            ->leftJoin(
        //                'product_event',
        //                ProductDescription::class,
        //                'product_desc',
        //                'product_desc.event = product_event.id AND product_desc.device = :device '
        //            )->setParameter('device', 'pc');


        $dbal->andWhere('product_info.profile = :profile OR product_info.profile IS NULL');
        $dbal->setParameter(
            'profile',
            $profile,
            UserProfileUid::TYPE
        );


        /** ProductInfo */
        $dbal
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        $dbal
            ->addSelect('product_info.article as product_article')
            ->leftJoin(
                'product_info',
                UserProfile::class,
                'users_profile',
                'users_profile.id = product_info.profile'
            );

        $dbal->orderBy('product.event', 'DESC');


        /** Торговое предложение */
        $dbal
            ->addSelect('product_offer.id as product_offer_id')
            ->join(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
            );

        /** Тип торгового предложения */
        $dbal
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /** Артикул продукта */

        $dbal
            ->addSelect("
            COALESCE(
                product_offer.article,
                product_info.article
            ) AS product_article
		");


        /** Множественные варианты торгового предложения */
        $dbal
            ->addSelect('product_variation.id as product_variation_id')
            ->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );


        /** Тип множественного варианта торгового предложения */
        $dbal
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation'
            );

        /** Артикул продукта */

        $dbal->addSelect("
            COALESCE(
                product_variation.article,
                product_offer.article,
                product_info.article
            ) AS product_article
		");

        /** Модификация множественного варианта */
        $dbal
            ->addSelect('product_modification.id as product_modification_id')
            ->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id '
            );

        /** Получаем тип модификации множественного варианта */
        $dbal
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification'
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

        return $dbal;

    }

}