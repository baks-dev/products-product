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

namespace BaksDev\Products\Product\Messenger\Quantity;

use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;

final readonly class UpdateProductQuantityMessage
{
    private ProductEventUid $event;

    private ?ProductOfferUid $offer;

    private ?ProductVariationUid $variation;

    private ?ProductModificationUid $modification;

    private int $quantity;


    public function getEvent(): ProductEventUid
    {
        return $this->event;
    }

    public function getOffer(): ?ProductOfferUid
    {
        return $this->offer;
    }

    public function getVariation(): ?ProductVariationUid
    {
        return $this->variation;
    }

    public function getModification(): ?ProductModificationUid
    {
        return $this->modification;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setEvent(ProductEventUid $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function setOffer(?ProductOfferUid $offer): self
    {
        $this->offer = $offer;
        return $this;
    }

    public function setVariation(?ProductVariationUid $variation): self
    {
        $this->variation = $variation;
        return $this;
    }

    public function setModification(?ProductModificationUid $modification): self
    {
        $this->modification = $modification;
        return $this;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
}