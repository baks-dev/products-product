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

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;

final readonly class AllProductsForPriceUpdateResult
{
    public function __construct(
        private ?int $opt,
        private ?int $price,
        private string $event,
        private int $product_cost,
        private string $product_cost_currency,
        private ?string $product_price_currency,
        private ?string $offer,
        private ?int $product_offer_cost,
        private ?string $product_offer_price_currency,
        private ?string $product_offer_cost_currency,
        private ?string $variation,
        private ?int $product_variation_cost,
        private ?string $product_variation_price_currency,
        private ?string $product_variation_cost_currency,
        private ?string $modification,
        private ?int $product_modification_cost,
        private ?string $product_modification_price_currency,
        private ?string $product_modification_cost_currency,
    ) {}

    public function getProductCost(): Money
    {
        return new Money($this->product_cost);
    }

    public function getOpt(): ?int
    {
        return $this->opt;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function getEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function getOffer(): ?ProductOfferUid
    {
        return $this->offer ? new ProductOfferUid($this->offer) : null;
    }

    public function getVariation(): ?ProductVariationUid
    {
        return $this->variation ? new ProductVariationUid($this->variation) : null;
    }

    public function getModification(): ?ProductModificationUid
    {
        return $this->modification ? new ProductModificationUid($this->modification) : null;
    }

    public function getProductCostCurrency(): Currency
    {
        return new Currency($this->product_cost_currency);
    }

    public function getProductPriceCurrency(): ?Currency
    {
        return $this->product_price_currency ? new Currency($this->product_price_currency) : null;
    }

    public function getProductOfferPriceCurrency(): ?Currency
    {
        return $this->product_offer_price_currency ? new Currency($this->product_offer_price_currency) : null;
    }

    public function getProductOfferCostCurrency(): ?Currency
    {
        return $this->product_offer_cost_currency ? new Currency($this->product_offer_cost_currency) : null;
    }

    public function getProductVariationPriceCurrency(): ?Currency
    {
        return $this->product_variation_price_currency ? new Currency($this->product_variation_price_currency) : null;
    }

    public function getProductVariationCostCurrency(): ?Currency
    {
        return $this->product_variation_cost_currency ? new Currency($this->product_variation_cost_currency) : null;
    }

    public function getProductModificationPriceCurrency(): ?Currency
    {
        return $this->product_modification_price_currency ? new Currency($this->product_modification_price_currency) : null;
    }

    public function getProductModificationCostCurrency(): ?Currency
    {
        return $this->product_modification_cost_currency ? new Currency($this->product_modification_cost_currency) : null;
    }

    public function getProductOfferCost(): ?Money
    {
        return null !== $this->product_offer_cost ? new Money($this->product_offer_cost) : null;
    }

    public function getProductVariationCost(): ?Money
    {
        return null !== $this->product_variation_cost ? new Money($this->product_variation_cost) : null;
    }

    public function getProductModificationCost(): ?Money
    {
        return null !== $this->product_modification_cost ? new Money($this->product_modification_cost) : null;
    }
}