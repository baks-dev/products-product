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

namespace BaksDev\Products\Product\Repository\ProductOfferChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Trans\CategoryProductOffersTrans;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final  class ProductOfferChoiceRepository implements ProductOfferChoiceInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
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
     * Метод возвращает все постоянные идентификаторы CONST торговых предложений продукта
     */
    public function findByProduct(ProductUid|string $product): Generator
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter(
                key: 'product',
                value: $product,
                type: ProductUid::TYPE,
            );


        $dbal
            ->join(
                'product',
                ProductOffer::class,
                'offer',
                'offer.event = product.event',
            );

        $dbal
            ->join(
                'offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = offer.category_offer',
            );


        $dbal
            ->leftJoin(
                'category_offer',
                CategoryProductOffersTrans::class,
                'category_offer_trans',
                'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
            );


        /** Свойства конструктора объекта гидрации */

        $dbal->addSelect('offer.const AS value');
        $dbal->addSelect("offer.value AS attr");

        $dbal->addSelect('category_offer_trans.name AS option');
        $dbal->addSelect('category_offer.reference AS property');
        $dbal->addSelect('offer.postfix AS characteristic');

        $dbal->orderBy('offer.value');


        return $dbal
            ->enableCache('products-product', 86400)
            ->fetchAllHydrate(ProductOfferConst::class);
    }


    /**
     * Метод возвращает все идентификаторы торговых предложений продукта по событию имеющиеся в доступе
     */
    public function findOnlyExistsByProductEvent(ProductEventUid|string $product): Generator
    {
        if(is_string($product))
        {
            $product = new ProductEventUid($product);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(Product::class, 'product')
            ->where('product.event = :product')
            ->setParameter(
                key: 'product',
                value: $product,
                type: ProductEventUid::TYPE,
            );


        $dbal->join(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event',
        );

        // Тип торгового предложения

        $dbal->leftJoin(
            'product_offer',
            CategoryProductOffers::class,
            'category_offer',
            'category_offer.id = product_offer.category_offer',
        );


        $dbal->leftJoin(
            'category_offer',
            CategoryProductOffersTrans::class,
            'category_offer_trans',
            'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
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
    
                   WHEN SUM(product_offer_quantity.quantity - product_offer_quantity.reserve) > 0
                   THEN SUM(product_offer_quantity.quantity - product_offer_quantity.reserve)

    
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
    
                   ELSE 0
                END > 0');

        }


        $dbal->addSelect('product_offer.id AS value');
        $dbal->addSelect("CONCAT(product_offer.value, ' ', product_offer.postfix) AS attr");


        /**
         * Фото торговых предложений
         */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.root = true',
        );

        /**
         * Цена торгового предложения
         */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        /** Свойства конструктора объекта гидрации */
        $dbal->addSelect('category_offer_trans.name AS property');
        $dbal->addSelect('category_offer.reference AS characteristic');

        $dbal->addSelect(
            "JSON_AGG
            (DISTINCT
                JSONB_BUILD_OBJECT
                (
                    'product_image', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                    'product_image_cdn', product_offer_images.cdn,
                    'product_image_ext', product_offer_images.ext,
                    'product_article', product_offer.article,
                    'product_price', product_offer_price.price,
                    'product_currency', product_offer_price.currency,
                    'product_offer_value', product_offer.value,
                    'product_offer_postfix', product_offer.postfix
                )
            ) AS params",
        );

        $dbal->allGroupByExclude();

        return $dbal->fetchAllHydrate(ProductOfferUid::class);


    }

}
