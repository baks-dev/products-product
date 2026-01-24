<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\ProductsByValues;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;
use InvalidArgumentException;

final class ProductsByValuesRepository implements ProductsByValuesInterface
{
    private CategoryProductUid|false $category = false;

    private ?string $offerValue = null;

    private ?string $variationValue = null;

    private ?string $modificationValue = null;

    private string|UserProfileUid $profile;

    public function __construct(
        private readonly DBALQueryBuilder $dbal,
    ) {}


    /** Идентификатор профиля пользователя */
    public function forProfile(UserProfileUid $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    /** Фильтр по категории */
    public function forCategory(CategoryProduct|CategoryProductUid|string $category): self
    {
        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->category = $category;

        return $this;
    }

    public function forOfferValue(string|null $offerValue): self
    {
        $this->offerValue = $offerValue;
        return $this;
    }

    public function forVariationValue(string|null $variationValue): self
    {
        $this->variationValue = $variationValue;
        return $this;
    }

    public function forModificationValue(string|null $modificationValue): self
    {
        $this->modificationValue = $modificationValue;
        return $this;
    }

    /**
     * Метод возвращает список продуктов из данной категории и с нужными offer, variation и modification по uid и
     * значениям offer, variation и modification
     *
     * @return Generator<int, ProductsByValuesResult>|false
     */
    public function findAll(): Generator|false
    {
        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new InvalidArgumentException('Invalid Argument UserProfileUid');
        }

        if(false === ($this->category instanceof CategoryProductUid))
        {
            throw new InvalidArgumentException('Invalid Argument CategoryProductUid');
        }

        $dbal = $this->dbal
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id AS product')
            ->addSelect('product.event AS event')
            ->from(Product::class, 'product');

        $dbal->join('product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event',
        );

        $dbal
            ->leftJoin(
                'product_event',
                ProductPrice::class,
                'product_price',
                'product_event.id = product_price.event',
            );

        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url AS product_url')
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                '
                    product_info.product = product.id 
                    AND (product_info.profile IS NULl OR product_info.profile = :profile)',

            )
            ->setParameter(
                key: 'profile',
                value: $this->profile,
                type: UserProfileUid::TYPE,
            );

        /** Категория */
        $dbal
            ->join(
                'product',
                ProductCategory::class,
                'product_event_category',
                '
               product_event_category.event = product.event AND 
               product_event_category.category = :category AND 
               product_event_category.root = true',
            )
            ->setParameter(
                key: 'category',
                value: $this->category,
                type: CategoryProductUid::TYPE,
            );

        /** Даты продукта */
        $dbal->join(
            'product',
            ProductActive::class,
            'product_active',
            'product_active.event = product.event',
        );

        /** OFFERS */
        $method = 'leftJoin';

        if(is_string($this->offerValue))
        {
            $method = 'join';
            $dbal->setParameter('offer', $this->offerValue);
        }

        $dbal
            ->addSelect('product_offer.id as product_offer_uid')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->{$method}(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event '.($this?->offerValue ? ' AND product_offer.value = :offer' : '').' ',
            );

        /** Наличие торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id',
        );

        /** VARIATIONS */
        $method = 'leftJoin';

        if(is_string($this->variationValue))
        {
            $method = 'join';
            $dbal->setParameter('variation', $this->variationValue);
        }

        $dbal
            ->addSelect('product_offer_variation.id as product_variation_uid')
            ->addSelect('product_offer_variation.value as product_variation_value')
            ->addSelect('product_offer_variation.postfix as product_variation_postfix')
            ->{$method}(
                'product_offer',
                ProductVariation::class,
                'product_offer_variation',
                'product_offer_variation.offer = product_offer.id '.($this?->variationValue ? ' AND product_offer_variation.value = :variation' : '').' ',
            );

        /** Наличие множественного варианта */
        $dbal->leftJoin(
            'product_offer_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_offer_variation.id',
        );

        /** MODIFICATION */
        $method = 'leftJoin';

        if(is_string($this->modificationValue))
        {
            $method = 'join';
            $dbal->setParameter('modification', $this->modificationValue);
        }

        $dbal
            ->addSelect('product_offer_modification.id as product_modification_uid')
            ->addSelect('product_offer_modification.value as product_modification_value')
            ->addSelect('product_offer_modification.postfix as product_modification_postfix')
            ->{$method}(
                'product_offer_variation',
                ProductModification::class,
                'product_offer_modification',
                'product_offer_modification.variation = product_offer_variation.id '.($this?->modificationValue ? ' AND product_offer_modification.value = :modification' : '').' ',
            );

        /** Наличие множественного варианта */
        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_offer_modification.id',
        );

        /** Наличие продукта */
        $dbal->addSelect('
			CASE

			   WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
			   THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)

			   WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve  
			   THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			
			   WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
			   THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)

			   WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
			   THEN (product_price.quantity - product_price.reserve)

			   ELSE 0
			END AS product_quantity
		');

        $dbal->addSelect('
			COALESCE(
                NULLIF(product_modification_quantity.reserve, 0),
                NULLIF(product_variation_quantity.reserve, 0),
                NULLIF(product_offer_quantity.reserve, 0),
                NULLIF(product_price.reserve, 0),
                0
            ) AS product_reserve
		');

        $dbal->enableCache('products-product', 86400);

        $result = $dbal->fetchAllHydrate(ProductsByValuesResult::class);

        return (true === $result->valid()) ? $result : false;
    }


}