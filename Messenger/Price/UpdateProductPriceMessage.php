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

namespace BaksDev\Products\Product\Messenger\Price;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Money\Type\Money;

final class UpdateProductPriceMessage
{
    private string $event;

    private ?string $offer;

    private ?string $variation;

    private ?string $modification;

    private int|float $price;

    public function getEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function setEvent(ProductEventUid $event): self
    {
        $this->event = (string) $event;
        return $this;
    }

    public function getOffer(): ?ProductOfferUid
    {
        return $this->offer ? new ProductOfferUid($this->offer) : null;
    }

    public function setOffer(?ProductOfferUid $offer): self
    {
        if(null === $offer)
        {
            $this->offer = null;
            return $this;
        }

        $this->offer = (string) $offer;
        return $this;
    }

    public function getVariation(): ?ProductVariationUid
    {
        return $this->variation ? new ProductVariationUid($this->variation) : null;
    }

    public function setVariation(?ProductVariationUid $variation): self
    {
        if(null === $variation)
        {
            $this->variation = null;
            return $this;
        }
        $this->variation = (string) $variation;
        return $this;
    }

    public function getModification(): ?ProductModificationUid
    {
        return $this->modification ? new ProductModificationUid($this->modification) : null;
    }

    public function setModification(?ProductModificationUid $modification): self
    {
        if(null === $modification)
        {
            $this->modification = null;
            return $this;
        }
        $this->modification = (string) $modification;
        return $this;
    }

    public function getPrice(): Money
    {
        return new Money($this->price);
    }

    public function setPrice(Money $price): self
    {
        $this->price = $price->getValue();
        return $this;
    }
}