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

namespace BaksDev\Products\Product\UseCase\Admin\Quantity\Offers\Variation;

use BaksDev\Products\Category\Type\Offers\Variation\CategoryProductVariationUid;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariationInterface;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offers\Variation\Quantity\ProductVariationQuantityDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductVariation */
final class ProductVariationCollectionDTO implements ProductVariationInterface
{
    /** Количественный учет */
    #[Assert\Valid]
    private ProductVariationQuantityDTO $quantity;

    /** Модификации множественных вариантов */
    #[Assert\Valid]
    private ArrayCollection $modification;


    public function __construct()
    {
        $this->modification = new ArrayCollection();
        $this->quantity = new ProductVariationQuantityDTO();
    }


    /** Количественный учет */

    public function getQuantity(): Quantity\ProductVariationQuantityDTO
    {
        return $this->quantity;
    }


    /** Модификации множественных вариантов */

    public function getModification(): ArrayCollection
    {
        return $this->modification;
    }


    public function addModification(Modification\ProductModificationCollectionDTO $modification): void
    {
        if(!$this->modification->contains($modification))
        {
            $this->modification->add($modification);
        }
    }

    public function removeModification(Modification\ProductModificationCollectionDTO $modification): void
    {
        $this->modification->removeElement($modification);
    }
}
