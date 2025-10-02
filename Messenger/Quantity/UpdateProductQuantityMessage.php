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

namespace BaksDev\Products\Product\Messenger\Quantity;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

final readonly class UpdateProductQuantityMessage
{
    private string $event;

    private string|false $offer;

    private string|false $variation;

    private string|false $modification;

    public function __construct(
        ProductEventUid $event,
        ProductOfferUid|null|false $offer,
        ProductVariationUid|null|false $variation,
        ProductModificationUid|null|false $modification,
        private int $quantity,
        private int|false $reserve = false
    )
    {
        $this->event = (string) $event;
        $this->offer = empty($offer) ? false : (string) $offer;
        $this->variation = empty($variation) ? false : (string) $variation;
        $this->modification = empty($modification) ? false : (string) $modification;
    }

    public function getEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function getOffer(): ProductOfferUid|false
    {
        return $this->offer ? new ProductOfferUid($this->offer) : false;
    }

    public function getVariation(): ProductVariationUid|false
    {
        return $this->variation ? new ProductVariationUid($this->variation) : false;
    }

    public function getModification(): ProductModificationUid|false
    {
        return $this->modification ? new ProductModificationUid($this->modification) : false;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReserve(): int|false
    {
        return $this->reserve ?: false;
    }
}