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

namespace BaksDev\Products\Product\Repository\ProductModificationChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\Trans\CategoryProductModificationTrans;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class ProductModificationChoiceRepository implements ProductModificationChoiceInterface
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
     * Метод возвращает все постоянные идентификаторы CONST модификаций множественных вариантов торговых предложений
     * продукта
     */
    public function fetchProductModificationConstByVariationConst(ProductVariationConst|string $const): Generator
    {
        if(is_string($const))
        {
            $const = new ProductVariationConst($const);
        }


        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $dbal
            ->from(ProductVariation::class, 'product_variation')
            ->where('product_variation.const = :const')
            ->setParameter('const', $const, ProductVariationConst::TYPE);

        $dbal->join(
            'product_variation',
            ProductOffer::class,
            'product_offer',
            'product_offer.id = product_variation.offer',
        );

        $dbal->join(
            'product_offer',
            Product::class,
            'product',
            'product.event = product_offer.event',
        );

        $dbal->join(
            'product_variation',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_variation.id',
        );

        // Тип торгового предложения

        $dbal->join(
            'product_modification',
            CategoryProductModification::class,
            'category_modification',
            'category_modification.id = product_modification.category_modification',
        );

        $dbal->leftJoin(
            'category_modification',
            CategoryProductModificationTrans::class,
            'category_modification_trans',
            'category_modification_trans.modification = category_modification.id AND category_modification_trans.local = :local',
        );

        /** Свойства конструктора объекта гидрации */

        $dbal->addSelect('product_modification.const AS value');
        $dbal->addSelect('product_modification.value AS attr');

        $dbal->addSelect('category_modification_trans.name AS option');
        $dbal->addSelect('category_modification.reference AS property');

        $dbal->addSelect('product_modification.postfix AS characteristic');


        $dbal->orderBy('product_modification.value');

        return $dbal
            ->enableCache('products-product', 86400)
            ->fetchAllHydrate(ProductModificationConst::class);

    }


    /**
     * Метод возвращает все идентификаторы модификаций множественных вариантов торговых предложений продукта
     */
    public function fetchProductModificationByVariation(ProductVariationUid $variation): ?array
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf('new %s(
            product_modification.id, 
            product_modification.value, 
            trans.name, 
            category_modification.reference
        )', ProductModificationUid::class);

        $qb->select($select);

        $qb
            ->from(ProductVariation::class, 'product_variation')
            ->where('product_variation.id = :variation')
            ->setParameter(
                key: 'variation',
                value: $variation,
                type: ProductVariationUid::TYPE,
            );

        $qb->join(
            ProductOffer::class,
            'product_offer',
            'WITH',
            'product_offer.id = product_variation.offer',
        );

        $qb->join(
            Product::class,
            'product',
            'WITH',
            'product.event = product_offer.event',
        );


        $qb->join(
            ProductModification::class,
            'product_modification',
            'WITH',
            'product_modification.variation = product_variation.id',
        );

        // Тип торгового предложения

        $qb->join(
            CategoryProductModification::class,
            'category_modification',
            'WITH',
            'category_modification.id = product_modification.categoryModification',
        );

        $qb->leftJoin(
            CategoryProductModificationTrans::class,
            'trans',
            'WITH',
            'trans.modification = category_modification.id AND trans.local = :local',
        );

        return $qb->getResult();
    }

    /**
     * Метод возвращает все идентификаторы модификаций множественных вариантов торговых предложений продукта
     */
    public function fetchProductModificationExistsByVariation(ProductVariationUid|string $variation): Generator
    {
        if(is_string($variation))
        {
            $variation = new ProductVariationUid($variation);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(ProductVariation::class, 'product_variation')
            ->where('product_variation.id = :variation')
            ->setParameter(
                key: 'variation',
                value: $variation,
                type: ProductVariationUid::TYPE,
            );

        $dbal->join(
            'product_variation',
            ProductOffer::class,
            'product_offer',
            'product_offer.id = product_variation.offer',
        );

        $dbal->join(
            'product_offer',
            Product::class,
            'product',
            'product.event = product_offer.event',
        );


        $dbal->join(
            'product_variation',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_variation.id',
        );

        // Тип торгового предложения

        $dbal->join(
            'product_modification',
            CategoryProductModification::class,
            'category_modification',
            'category_modification.id = product_modification.category_modification',
        );

        $dbal->leftJoin(
            'category_modification',
            CategoryProductModificationTrans::class,
            'category_modification_trans',
            'category_modification_trans.modification = category_modification.id
             AND category_modification_trans.local = :local',
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

            $dbal
                ->addSelect('SUM(modification_quantity.quantity - modification_quantity.reserve) AS option')
                ->join(
                    'product_modification',
                    ProductModificationQuantity::class,
                    'modification_quantity',
                    'modification_quantity.modification = product_modification.id AND modification_quantity.quantity > 0 ',
                );


            $dbal->having('SUM(modification_quantity.quantity - modification_quantity.reserve) > 0');

        }


        /* Фото продукта */
        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationImage::class,
                'product_modification_image',
                'product_modification_image.modification = product_modification.id AND product_modification_image.root = true',
            );

        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        $dbal->addSelect(
            "JSON_AGG
            (DISTINCT
                JSONB_BUILD_OBJECT
                (
                    'product_image', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                    'product_image_cdn', product_modification_image.cdn,
                    'product_image_ext', product_modification_image.ext,
                    'product_article', product_modification.article,
                    'product_price', product_modification_price.price,
                    'product_currency', product_modification_price.currency,
                    'product_modification_value', product_modification.value,
                    'product_modification_postfix', product_modification.postfix
                )
            ) AS params",
        );

        /** Свойства конструктора объекта гидрации */

        $dbal->addSelect('product_modification.id AS value');
        $dbal->addSelect("CONCAT(product_modification.value, ' ', product_modification.postfix) AS attr");

        $dbal->addSelect('category_modification_trans.name AS property');
        $dbal->addSelect('category_modification.reference AS characteristic');

        $dbal->allGroupByExclude();

        return $dbal->fetchAllHydrate(ProductModificationUid::class);

    }


}
