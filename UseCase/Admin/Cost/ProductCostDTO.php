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

namespace BaksDev\Products\Product\UseCase\Admin\Cost;

use BaksDev\Products\Product\UseCase\Admin\Cost\ProductCostFile\ProductCostFileDTO;
use BaksDev\Reference\Money\Type\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductCost */
final class ProductCostDTO
{
    /** Стоимость логистики */
    #[Assert\NotBlank]
    private ?Money $price = null;

    #[Assert\Valid]
    private ArrayCollection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    /**
     * Files
     */

    public function getFiles(): ArrayCollection
    {
        return $this->files;
    }

    public function addFiles(ProductCostFileDTO $file): self
    {
        $this->files->add($file);
        return $this;
    }

    public function setFiles(ArrayCollection $files): self
    {
        $this->files = $files;
        return $this;
    }

    /**
     * Стоимость логистики
     */

    public function setPrice(?Money $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

}