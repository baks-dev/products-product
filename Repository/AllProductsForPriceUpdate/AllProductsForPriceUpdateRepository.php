<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\AllProductsForPriceUpdate;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Currency\CategoryProductCurrency;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\Cost\ProductOfferCost;
use BaksDev\Products\Product\Entity\Offers\Opt\ProductOfferOpt;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Cost\ProductVariationCost;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Cost\ProductModificationCost;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Opt\ProductModificationOpt;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Opt\ProductVariationOpt;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Price\Cost\ProductPriceCost;
use BaksDev\Products\Product\Entity\Price\Opt\ProductPriceOpt;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use Generator;

final readonly class AllProductsForPriceUpdateRepository implements AllProductsForPriceUpdateInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    public function findAll(): Generator
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('category_product_currency.opt AS opt')
            ->addSelect('category_product_currency.price AS price')
            ->from(CategoryProductCurrency::class, 'category_product_currency')
            ->where('category_product_currency.opt IS NOT NULL or category_product_currency.price IS NOT NULL');

        $dbal
            ->join(
                'category_product_currency',
                CategoryProduct::class,
                'category_product',
                'category_product.event = category_product_currency.event'
            );

        $dbal
            ->join(
                'category_product',
                ProductCategory::class,
                'product_category',
                'product_category.category = category_product.id'
            );

        $dbal
            ->addSelect('product_event.id AS event')
            ->join(
                'product_category',
                ProductEvent::class,
                'product_event',
                'product_event.id = product_category.event'
            );

        $dbal
            ->join(
                'product_event',
                Product::class,
                'product',
                'product.event = product_event.id'
            );

        $dbal
            ->addSelect('product_price.currency AS product_price_currency')
            ->leftJoin(
                'product_event',
                ProductPrice::class,
                'product_price',
                'product_price.event = product_event.id'
            );

        $dbal
            ->leftJoin(
                'product_event',
                ProductPriceOpt::class,
                'product_price_opt',
                'product_price_opt.event = product_event.id'
            );

        $dbal
            ->addSelect('product_price_cost.cost AS product_cost')
            ->addSelect('product_price_cost.currency AS product_cost_currency')
            ->join(
                'product_event',
                ProductPriceCost::class,
                'product_price_cost',
                'product_price_cost.event = product_event.id'
            );

        $dbal
            ->addSelect('product_offer.id AS offer')
            ->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
            );

        $dbal
            ->addSelect('product_offer_price.currency AS product_offer_price_currency')
            ->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id'
            );

        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferOpt::class,
                'product_offer_opt',
                'product_offer_opt.offer = product_offer.id'
            );

        $dbal
            ->addSelect('product_offer_cost.cost AS product_offer_cost')
            ->addSelect('product_offer_cost.currency AS product_offer_cost_currency')
            ->leftJoin(
                'product_offer',
                ProductOfferCost::class,
                'product_offer_cost',
                'product_offer_cost.offer = product_offer.id'
            );

        $dbal
            ->addSelect('product_variation.id AS variation')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );

        $dbal
            ->addSelect('product_variation_price.currency AS product_variation_price_currency')
            ->leftJoin(
                'product_variation',
                ProductVariationPrice::class,
                'product_variation_price',
                'product_variation_price.variation = product_variation.id'
            );

        $dbal
            ->leftJoin(
                'product_variation',
                ProductVariationOpt::class,
                'product_variation_opt',
                'product_variation_opt.variation = product_variation.id'
            );

        $dbal
            ->addSelect('product_variation_cost.cost AS product_variation_cost')
            ->addSelect('product_variation_cost.currency AS product_variation_cost_currency')
            ->leftJoin(
                'product_variation',
                ProductVariationCost::class,
                'product_variation_cost',
                'product_variation_cost.variation = product_variation.id'
            );

        $dbal
            ->addSelect('product_modification.id AS modification')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id'
            );

        $dbal
            ->addSelect('product_modification_price.currency AS product_modification_price_currency')
            ->leftJoin(
                'product_modification',
                ProductModificationPrice::class,
                'product_modification_price',
                'product_modification_price.modification = product_modification.id'
            );

        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationOpt::class,
                'product_modification_opt',
                'product_modification_opt.modification = product_modification.id'
            );

        $dbal
            ->addSelect('product_modification_cost.cost AS product_modification_cost')
            ->addSelect('product_modification_cost.currency AS product_modification_cost_currency')
            ->leftJoin(
                'product_modification',
                ProductModificationCost::class,
                'product_modification_cost',
                'product_modification_cost.modification = product_modification.id'
            );

        return $dbal
            ->enableCache('Namespace', 3600)
            ->fetchAllHydrate(AllProductsForPriceUpdateResult::class);
    }
}