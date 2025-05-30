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

namespace BaksDev\Products\Product\Forms\ProductsQuantityForm;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;

final class ProductsQuantityDTO
{
    private ?CategoryProductUid $category = null;

    /**
     * Значение торгового предложения
     */
    private ?string $offer = null;

    /**
     * Значение варианта торгового предложения
     */
    private ?string $variation = null;

    /**
     * Значение модификации торгового предложения
     */
    private ?string $modification = null;

    private ?int $quantity;

    public function getCategory(): ?CategoryProductUid
    {
        return $this->category;
    }

    public function getOffer(): ?string
    {
        return $this->offer;
    }

    public function getVariation(): ?string
    {
        return $this->variation;
    }

    public function getModification(): ?string
    {
        return $this->modification;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setCategory(?CategoryProductUid $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setOffer(?string $offer): self
    {
        $this->offer = $offer;
        return $this;
    }

    public function setVariation(?string $variation): self
    {
        $this->variation = $variation;
        return $this;
    }

    public function setModification(?string $modification): self
    {
        $this->modification = $modification;
        return $this;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }
}