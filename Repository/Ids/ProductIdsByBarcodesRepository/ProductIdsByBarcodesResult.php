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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\Ids\ProductIdsByBarcodesRepository;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

final readonly class ProductIdsByBarcodesResult
{
    public function __construct(
        private string $product,
        private string $event,
        private string $invariable,

        /** Торговое предложение */
        private ?string $offer,
        private ?string $offer_const,

        /** Множественный вариант торгового предложения */
        private ?string $variation,
        private ?string $variation_const,

        /** Модификация множественного варианта торгового предложения */
        private ?string $modification,
        private ?string $modification_const,
    ) {}

    /**
     * Product
     */
    public function getProduct(): ProductUid
    {
        return new ProductUid($this->product);
    }

    /**
     * Event
     */
    public function getEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    /**
     * Offer
     */
    public function getOffer(): ?ProductOfferUid
    {
        return $this->offer ? new ProductOfferUid($this->offer) : null;
    }

    /**
     * OfferConst
     */
    public function getOfferConst(): ?ProductOfferConst
    {
        return $this->offer_const ? new ProductOfferConst($this->offer_const) : null;
    }

    /**
     * Variation
     */
    public function getVariation(): ?ProductVariationUid
    {
        return $this->variation ? new ProductVariationUid($this->variation) : null;
    }

    /**
     * VariationConst
     */
    public function getVariationConst(): ?ProductVariationConst
    {
        return $this->variation_const ? new ProductVariationConst($this->variation_const) : null;
    }

    /**
     * Modification
     */
    public function getModification(): ?ProductModificationUid
    {
        return $this->modification ? new ProductModificationUid($this->modification) : null;
    }

    /**
     * ModificationConst
     */
    public function getModificationConst(): ?ProductModificationConst
    {
        return $this->modification_const ? new ProductModificationConst($this->modification_const) : null;
    }

    public function getInvariable(): ProductInvariableUid
    {
        return new ProductInvariableUid($this->invariable);
    }
}
