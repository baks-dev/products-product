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

namespace BaksDev\Products\Product\Repository\ProductVariationChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Trans\CategoryProductVariationTrans;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class ProductVariationChoiceRepository implements ProductVariationChoiceInterface
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
     * Метод возвращает все постоянные идентификаторы CONST множественных вариантов торговых предложений продукта
     */
    public function fetchProductVariationByOfferConst(ProductOfferConst|string $const): Generator
    {

        if(is_string($const))
        {
            $const = new ProductOfferConst($const);
        }


        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal
            ->from(ProductOffer::class, 'offer')
            ->where('offer.const = :const')
            ->setParameter('const', $const, ProductOfferConst::TYPE);

        $dbal->join(
            'offer',
            Product::class,
            'product',
            'product.event = offer.event',
        );

        $dbal->join(
            'offer',
            ProductVariation::class,
            'variation',
            'variation.offer = offer.id',
        );

        // Тип торгового предложения

        $dbal->join(
            'variation',
            CategoryProductVariation::class,
            'category_variation',
            'category_variation.id = variation.category_variation',
        );

        $dbal->leftJoin(
            'category_variation',
            CategoryProductVariationTrans::class,
            'category_variation_trans',
            'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local',
        );


        /** Свойства конструктора объекта гидрации */

        $dbal->addSelect('variation.const AS value');
        $dbal->addSelect('variation.value AS attr');

        $dbal->addSelect('category_variation_trans.name AS option');
        $dbal->addSelect('category_variation.reference AS property');

        $dbal->addSelect('variation.postfix AS characteristic');


        $dbal->orderBy('variation.value');

        return $dbal
            ->enableCache('products-product', 86400)
            ->fetchAllHydrate(ProductVariationConst::class);


    }


    public function fetchProductVariationByOffer(ProductOfferUid $offer): ?array
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf('new %s(
            variation.id, 
            variation.value, 
            trans.name, 
            category_variation.reference
        )', ProductVariationUid::class);

        $qb->select($select);

        $qb
            ->from(ProductOffer::class, 'offer')
            ->where('offer.id = :offer')
            ->setParameter(
                key: 'offer',
                value: $offer,
                type: ProductOfferUid::TYPE,
            );


        $qb->join(
            Product::class,
            'product',
            'WITH',
            'product.event = offer.event',
        );


        $qb->join(
            ProductVariation::class,
            'variation',
            'WITH',
            'variation.offer = offer.id',
        );

        // Тип торгового предложения

        $qb->join(
            CategoryProductVariation::class,
            'category_variation',
            'WITH',
            'category_variation.id = variation.categoryVariation',
        );

        $qb->leftJoin(
            CategoryProductVariationTrans::class,
            'trans',
            'WITH',
            'trans.variation = category_variation.id AND trans.local = :local',
        );

        /* Кешируем результат ORM */
        return $qb->getResult();

    }


    /**
     * Метод возвращает все идентификаторы множественных вариантов торговых предложений продукта имеющиеся в доступе
     */
    public function fetchProductVariationExistsByOffer(ProductOfferUid|string $offer): Generator
    {
        if(is_string($offer))
        {
            $offer = new ProductOfferUid($offer);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(ProductOffer::class, 'product_offer')
            ->where('product_offer.id = :offer')
            ->setParameter(
                key: 'offer',
                value: $offer,
                type: ProductOfferUid::TYPE,
            );

        $dbal->join(
            'product_offer',
            Product::class,
            'product',
            'product.event = product_offer.event',
        );

        $dbal->join(
            'product_offer',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id',
        );

        // Тип множественного варианта предложения

        $dbal->join(
            'product_variation',
            CategoryProductVariation::class,
            'category_variation',
            'category_variation.id = product_variation.category_variation',
        );

        $dbal->leftJoin(
            'category_variation',
            CategoryProductVariationTrans::class,
            'category_variation_trans',
            'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local',
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
                ',
                )
                ->setParameter(
                    key: 'profile',
                    value: $this->profile instanceof UserProfileUid ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                    type: UserProfileUid::TYPE,
                );

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

                   ELSE 0
                END
    
            AS option');


            $dbal->having('
             CASE
                   WHEN SUM(product_modification_quantity.quantity - product_modification_quantity.reserve) > 0
                   THEN SUM(product_modification_quantity.quantity - product_modification_quantity.reserve)
    
                   WHEN SUM(product_variation_quantity.quantity - product_variation_quantity.reserve) > 0
                   THEN SUM(product_variation_quantity.quantity - product_variation_quantity.reserve)
    
                   ELSE 0
                END > 0');

        }


        /** Свойства конструктора объекта гидрации */

        $dbal->addSelect('product_variation.id AS value');
        $dbal->addSelect("CONCAT(product_variation.value, ' ', product_variation.postfix) AS attr");


        /**
         * Фото торговых предложений
         */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationImage::class,
            'product_variation_images',
            'product_variation_images.variation = product_variation.id AND product_variation_images.root = true',
        );

        /**
         * Цена торгового предложения
         */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );

        $dbal->addSelect('category_variation_trans.name AS property');
        $dbal->addSelect('category_variation.reference AS characteristic');

        $dbal->addSelect(
            "JSON_AGG
            (DISTINCT
                JSONB_BUILD_OBJECT
                (
                    'product_image', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_images.name),
                    'product_image_cdn', product_variation_images.cdn,
                    'product_image_ext', product_variation_images.ext,
                    'product_article', product_variation.article,
                    'product_price', product_variation_price.price,
                    'product_currency', product_variation_price.currency,
                    'product_variation_value', product_variation.value,
                    'product_variation_postfix', product_variation.postfix
                )
            ) AS params",
        );

        $dbal->allGroupByExclude();

        return $dbal->fetchAllHydrate(ProductVariationUid::class);


    }


}
