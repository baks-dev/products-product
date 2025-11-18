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
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
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
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class ProductChoiceRepository implements ProductChoiceInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly ORMQueryBuilder $ORMQueryBuilder,
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
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

        $qb
            ->join(
                'product',
                ProductInfo::class,
                'info',
                'info.product = product.id AND (info.profile = :profile OR info.profile IS NULL)',
            )
            ->setParameter(
                key: 'profile',
                value: $this->profile instanceof UserProfileUid ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );


        if($category)
        {
            $qb
                ->join(
                    'product',
                    ProductCategory::class,
                    'category',
                    'category.event = product.event AND category.category = :category',
                )
                ->setParameter(
                    'category',
                    $category,
                    CategoryProductUid::TYPE,
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
		',
        );

        $qb->join(
            'product',
            ProductTrans::class,
            'trans',
            'trans.event = product.event AND trans.local = :local',
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
            'info.product = product.id',
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
		',
        );

        $qb->join(
            ProductTrans::class,
            'trans',
            'WITH',
            'trans.event = product.event AND trans.local = :local',
        );


        /* Кешируем результат ORM */
        return $qb->getResult();

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

        $dbal
            ->join(
                'product',
                ProductInfo::class,
                'info',
                'info.product = product.id AND (info.profile = :profile OR info.profile IS NULL)',
            )
            ->setParameter(
                key: 'profile',
                value: $this->profile instanceof UserProfileUid ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );


        if($category)
        {
            $dbal->join(
                'product',
                ProductCategory::class,
                'category',
                'category.event = product.event AND category.category = :category AND category.root = TRUE',
            )
                ->setParameter(
                    key: 'category',
                    value: $category,
                    type: CategoryProductUid::TYPE,
                );

            $dbal->join(
                'category',
                CategoryProduct::class,
                'category_product',
                'category_product.id = category.category',
            );

            $dbal->leftJoin(
                'category_product',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category_product.event',
            );
        }

        $dbal->leftJoin(
            'product',
            ProductTrans::class,
            'trans',
            'trans.event = product.event AND trans.local = :local',
        );

        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );

        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id',
        );

        $dbal->leftJoin(
            'product_variation',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_variation.id',
        );


        if(class_exists(BaksDevProductsStocksBundle::class))
        {

            $dbal
                ->addSelect("SUM(stock.total - stock.reserve) AS option")
                ->leftJoin(
                    'product_modification',
                    ProductStockTotal::class,
                    'stock',
                    '
                    stock.profile = :profile AND
                    stock.product = product.id

                    AND

                        CASE
                            WHEN product_offer.const IS NOT NULL
                            THEN stock.offer = product_offer.const
                            ELSE stock.offer IS NULL
                        END

                    AND

                        CASE
                            WHEN product_variation.const IS NOT NULL
                            THEN stock.variation = product_variation.const
                            ELSE stock.variation IS NULL
                        END

                    AND

                        CASE
                            WHEN product_modification.const IS NOT NULL
                            THEN stock.modification = product_modification.const
                            ELSE stock.modification IS NULL
                        END
                ');

            $dbal->having('SUM(stock.total - stock.reserve) > 0');

        }
        else
        {
            /**
             * Quantity
             */

            $dbal
                ->leftJoin(
                    'product_offer',
                    ProductOfferQuantity::class,
                    'product_offer_quantity',
                    'product_offer_quantity.offer = product_offer.id',
                );

            $dbal
                ->leftJoin(
                    'product_variation',
                    ProductVariationQuantity::class,
                    'product_variation_quantity',
                    'product_variation_quantity.variation = product_variation.id',
                );

            $dbal
                ->leftJoin(
                    'product_modification',
                    ProductModificationQuantity::class,
                    'product_modification_quantity',
                    'product_modification_quantity.modification = product_modification.id',
                );


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


            $dbal->having('
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
                END > 0');

        }


        $dbal->addSelect('product.event AS value');
        $dbal->addSelect('trans.name AS attr');

        /* Фото продукта */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event AND product_photo.root = true',
        );

        $dbal
            ->addSelect(
                "JSON_AGG
                (DISTINCT
                    JSONB_BUILD_OBJECT
                    (
                        'product_url', info.url,
                        'product_image', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                        'product_image_cdn', product_photo.cdn,
                        'product_image_ext', product_photo.ext,
                        'product_price', product_price.price,
                        'product_currency', product_price.currency,
                        'category_url', CASE
                            WHEN (:category)::UUID IS NOT NULL
                            THEN category_info.url
                            ELSE
                            NULL
                        END
                    )
                ) AS params",

            )
            ->setParameter(
                key: 'category',
                value: $category,
                type: CategoryProductUid::TYPE,
            );


        $dbal->allGroupByExclude();

        
        return $dbal
            ->enableCache('products-product', 60)
            ->fetchAllHydrate(ProductEventUid::class);

    }

}
