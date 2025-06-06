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

namespace BaksDev\Products\Product\Repository\CurrentProductIdentifier;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

final class CurrentProductIdentifierResult
{
    public function __construct(

        /** ID продукта */
        private readonly string $id,
        private readonly string $event,

        /** Торговое предложение */
        private ?string $offer = null,
        private ?string $offer_const = null,
        private ?string $offer_value = null,

        /** Множественный вариант торгового предложения */
        private ?string $variation = null,
        private ?string $variation_const = null,
        private ?string $variation_value = null,

        /** Модификация множественного варианта торгового предложения */
        private ?string $modification = null,
        private ?string $modification_const = null,
        private ?string $modification_value = null,

        private ?string $product_invariable = null,
    ) {}

    /**
     * Product
     */
    public function getProduct(): ProductUid
    {
        return new ProductUid($this->id);
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
    public function getOffer(): ProductOfferUid|false
    {
        return $this->offer ? new ProductOfferUid($this->offer) : false;
    }

    /**
     * OfferConst
     */
    public function getOfferConst(): ProductOfferConst|false
    {
        return $this->offer_const ? new ProductOfferConst($this->offer_const) : false;
    }

    /**
     * Variation
     */
    public function getVariation(): ProductVariationUid|false
    {
        return $this->variation ? new ProductVariationUid($this->variation) : false;
    }

    /**
     * VariationConst
     */
    public function getVariationConst(): ProductVariationConst|false
    {
        return $this->variation_const ? new ProductVariationConst($this->variation_const) : false;
    }

    /**
     * Modification
     */
    public function getModification(): ProductModificationUid|false
    {
        return $this->modification ? new ProductModificationUid($this->modification) : false;
    }

    /**
     * ModificationConst
     */
    public function getModificationConst(): ProductModificationConst|false
    {
        return $this->modification_const ? new ProductModificationConst($this->modification_const) : false;
    }

    /**
     * OfferValue
     */
    public function getOfferValue(): false|string|null
    {
        return $this->offer_value;
    }

    /**
     * VariationValue
     */
    public function getVariationValue(): false|string|null
    {
        return $this->variation_value;
    }

    /**
     * ModificationValue
     */
    public function getModificationValue(): false|string|null
    {
        return $this->modification_value;
    }

    public function getProductInvariable(): ProductInvariableUid|false
    {
        return $this->product_invariable ? new ProductInvariableUid($this->product_invariable) : false;
    }

}
