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
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

final class CurrentProductDTO
{
    /** ID продукта */
    private ProductUid $product;

    /** ID события продукта */
    private ProductEventUid $event;

    /** Торговое предложение */
    private ProductOfferUid|null|false $offer = null;
    private ProductOfferConst|null|false $offerConst = null;
    private string|null|false $offerValue = null;

    /** Множественный вариант торгового предложения */
    private ProductVariationUid|null|false $variation = null;
    private ProductVariationConst|null|false $variationConst = null;
    private string|null|false $variationValue = null;

    /** Модификация множественного варианта торгового предложения */
    private ProductModificationUid|null|false $modification = null;
    private ProductModificationConst|null|false $modificationConst = null;
    private string|null|false $modificationValue = null;


    public function __construct(
        string $id,
        string $event,

        /** Торговое предложение */
        ?string $offer = null,
        ?string $offer_const = null,
        ?string $offer_value = null,

        /** Множественный вариант торгового предложения */
        ?string $variation = null,
        ?string $variation_const = null,
        ?string $variation_value = null,

        /** Модификация множественного варианта торгового предложения */
        ?string $modification = null,
        ?string $modification_const = null,
        ?string $modification_value = null,
    )
    {

        $this->product = new ProductUid($id);
        $this->event = new ProductEventUid($event);

        if($offer)
        {
            $this->offer = new ProductOfferUid($offer);
            $this->offerConst = new ProductOfferConst($offer_const);
            $this->offerValue = $offer_value ?: false;
        }

        if($variation)
        {
            $this->variation = new ProductVariationUid($variation);
            $this->variationConst = new ProductVariationConst($variation_const);
            $this->variationValue = $variation_value ?: false;
        }

        if($modification)
        {
            $this->modification = new ProductModificationUid($modification);
            $this->modificationConst = new ProductModificationConst($modification_const);
            $this->modificationValue = $modification_value ?: false;
        }

    }

    /**
     * Product
     */
    public function getProduct(): ProductUid
    {
        return $this->product;
    }

    /**
     * Event
     */
    public function getEvent(): ProductEventUid
    {
        return $this->event;
    }

    /**
     * Offer
     */
    public function getOffer(): ProductOfferUid|false
    {
        return $this->offer ?: false;
    }

    /**
     * OfferConst
     */
    public function getOfferConst(): ProductOfferConst|false
    {
        return $this->offerConst ?: false;
    }

    /**
     * Variation
     */
    public function getVariation(): ProductVariationUid|false
    {
        return $this->variation ?: false;
    }

    /**
     * VariationConst
     */
    public function getVariationConst(): ProductVariationConst|false
    {
        return $this->variationConst ?: false;
    }

    /**
     * Modification
     */
    public function getModification(): ProductModificationUid|false
    {
        return $this->modification ?: false;
    }

    /**
     * ModificationConst
     */
    public function getModificationConst(): ProductModificationConst|false
    {
        return $this->modificationConst ?: false;
    }

    /**
     * OfferValue
     */
    public function getOfferValue(): false|string|null
    {
        return $this->offerValue;
    }

    /**
     * VariationValue
     */
    public function getVariationValue(): false|string|null
    {
        return $this->variationValue;
    }

    /**
     * ModificationValue
     */
    public function getModificationValue(): false|string|null
    {
        return $this->modificationValue;
    }
}
