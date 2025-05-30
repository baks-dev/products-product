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

use BaksDev\Products\Category\Type\Id\CategoryProductUid;

final readonly class FindProductsForQuantityUpdateMessage
{
    private string $category;

    public function __construct(
        CategoryProductUid $category,
        private int $quantity,
        private ?string $offerValue = null,
        private ?string $variationValue = null,
        private ?string $modificationValue = null,
    )
    {
        $this->category = (string) $category;
    }

    public function getCategory(): CategoryProductUid
    {
        return new CategoryProductUid($this->category);
    }

    public function getOfferValue(): ?string
    {
        return $this->offerValue;
    }

    public function getVariationValue(): ?string
    {
        return $this->variationValue;
    }

    public function getModificationValue(): ?string
    {
        return $this->modificationValue;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}