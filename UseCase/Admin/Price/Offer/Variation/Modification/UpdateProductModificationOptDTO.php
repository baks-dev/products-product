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

namespace BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\Modification;

use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Opt\ProductModificationOptInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Validator\Constraints as Assert;

final class UpdateProductModificationOptDTO implements ProductModificationOptInterface
{
    /** Идентификатор */
    #[Assert\Uuid]
    #[Assert\NotBlank]
    private ?ProductModificationUid $modificationId = null;

    /** Оптовая цена */
    #[Assert\Valid]
    private ?Money $price = null;

    private ?ProductEventUid $productEvent = null;

    public function getModification(): ?ProductModificationUid
    {
        return $this->modificationId;
    }

    public function setModification(ProductModificationUid $modificationId): self
    {
        $this->modificationId = $modificationId;
        return $this;
    }

    public function setPrice(Money $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    public function getProductEvent(): ?ProductEventUid
    {
        return $this->productEvent;
    }

    public function setProductEvent(?ProductEventUid $productEvent): self
    {
        $this->productEvent = $productEvent;
        return $this;
    }
}