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

namespace BaksDev\Products\Product\Repository\ProductChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class ProductChoiceRepository implements ProductChoiceInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly ORMQueryBuilder $ORMQueryBuilder,
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод возвращает все идентификаторы продуктов (ProductUid) с названием указанной категории
     */
    public function fetchAllProduct(CategoryProductUid|false $category = false): ?array
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $qb->from(Product::class, 'product');

        $qb->join(
            'product',
            ProductInfo::class,
            'info',
            'info.product = product.id '.($this->profile ? 'AND (info.profile = :profile OR info.profile IS NULL)' : '')
        );

        if($this->profile instanceof UserProfileUid)
        {
            $qb->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }

        if($category)
        {
            $qb
                ->join(
                    'product',
                    ProductCategory::class,
                    'category',
                    'category.event = product.event AND category.category = :category'
                )
                ->setParameter(
                    'category',
                    $category,
                    CategoryProductUid::TYPE
                );
        }


        /*$qb->leftJoin(
            'product',
            ProductOffer::class,
            'offer',
            'offer.event = product.event'
        );*/

        $qb->join(
            'product',
            ProductActive::class,
            'active',
            '
            active.event = product.event AND
            active.active = true AND
            active.active_from < NOW() AND
            ( active.active_to IS NULL OR active.active_to > NOW() )
		'
        );

        $qb->join(
            'product',
            ProductTrans::class,
            'trans',
            'trans.event = product.event AND trans.local = :local'
        );


        $qb->addSelect('product.id AS value');
        $qb->addSelect('trans.name AS attr');
        $qb->addSelect('info.article AS option');

        $qb->orderBy('trans.name');

        /* Кешируем результат ORM */
        return $qb
            ->enableCache('products-product', 86400)
            ->fetchAllAssociativeIndexed(ProductUid::class);

    }


    /**
     * Метод возвращает активные идентификаторы событий (ProductEventUid) продукции
     */
    public function fetchAllProductEvent(): ?array
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $qb->from(Product::class, 'product');

        $qb->join(
            ProductInfo::class,
            'info',
            'WITH',
            'info.product = product.id'
        );

        $qb->join(
            ProductActive::class,
            'active',
            'WITH',
            '
            active.event = product.event AND
            active.active = true AND
            active.activeFrom < CURRENT_TIMESTAMP() AND
            (active.activeTo IS NULL OR active.activeTo > CURRENT_TIMESTAMP())
		'
        );

        $qb->join(
            ProductTrans::class,
            'trans',
            'WITH',
            'trans.event = product.event AND trans.local = :local'
        );


        /* Кешируем результат ORM */
        return $qb->enableCache('products-product', 86400)->getResult();

    }


    /**
     * Метод возвращает идентификаторы событий (ProductEventUid) доступной для продажи продукции
     */
    public function fetchAllProductEventByExists(CategoryProductUid|false $category = false): Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->from(Product::class, 'product');

        $dbal->join(
            'product',
            ProductInfo::class,
            'info',
            'info.product = product.id '.($this->profile ? 'AND (info.profile = :profile OR info.profile IS NULL)' : 'info.profile IS NULL')
        );

        if($this->profile instanceof UserProfileUid)
        {
            $dbal->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }


        if($category)
        {
            $dbal->join(
                'product',
                ProductCategory::class,
                'category',
                'category.event = product.event AND category.category = :category AND category.root = TRUE'
            )
                ->setParameter(
                    'category',
                    $category,
                    CategoryProductUid::TYPE
                );
        }


        $dbal->leftJoin(
            'product',
            ProductTrans::class,
            'trans',
            'trans.event = product.event AND trans.local = :local'
        );

        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        );

        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id'
        );

        $dbal->leftJoin(
            'product_variation',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_variation.id'
        );

        /**
         * Quantity
         */

        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id'
            );

        $dbal
            ->leftJoin(
                'product_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id'
            );

        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id'
            );


        $dbal->addSelect('product.event AS value');
        $dbal->addSelect('trans.name AS attr');


        $dbal->addSelect('

            CASE
               WHEN SUM(product_modification_quantity.quantity - product_modification_quantity.reserve) > 0
               THEN SUM(product_modification_quantity.quantity - product_modification_quantity.reserve)

               WHEN SUM(product_variation_quantity.quantity - product_variation_quantity.reserve) > 0
               THEN SUM(product_variation_quantity.quantity - product_variation_quantity.reserve)

               WHEN SUM(product_offer_quantity.quantity - product_offer_quantity.reserve) > 0
               THEN SUM(product_offer_quantity.quantity - product_offer_quantity.reserve)

               WHEN SUM(product_price.quantity - product_price.reserve) > 0
               THEN SUM(product_price.quantity - product_price.reserve)

               ELSE 0
            END

        AS option');

        $dbal->allGroupByExclude();

        return $dbal
            ->enableCache('products-product', 60)
            ->fetchAllHydrate(ProductEventUid::class);

    }

}
