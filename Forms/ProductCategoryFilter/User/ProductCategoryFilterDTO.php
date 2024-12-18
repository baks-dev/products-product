<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Forms\ProductCategoryFilter\User;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductCategoryFilterDTO
{
    /**
     * Идентификатор категории
     */
    #[Assert\Uuid]
    private ?CategoryProductUid $category;

    /**
     * Торговое предложение
     */
    private ?string $offer = null;

    /**
     * Множественный вариант торгового предложения
     */
    private ?string $variation = null;

    /**
     * Модификатор множественного варианта торгового предложения
     */
    private ?string $modification = null;

    /**
     * Свойства, участвующие в фильтре
     */
    private ?ArrayCollection $property = null;


    public function __construct(?CategoryProductUid $category = null)
    {
        $this->category = $category;
    }

    /**
     * Идентификатор категории
     */

    public function getCategory(): ?CategoryProductUid
    {
        return $this->category;
    }

    public function setCategory(?CategoryProductUid $category): self
    {
        $this->category = $category;
        return $this;
    }

    /** Торговое предложение */

    public function getOffer(): ?string
    {
        return $this->offer;
    }

    public function setOffer(?string $offer): void
    {
        $this->offer = $offer;
    }


    /** Множественный вариант торгового предложения */

    public function getVariation(): ?string
    {
        return $this->variation;
    }

    public function setVariation(?string $variation): void
    {
        $this->variation = $variation;
    }


    /** Модификатор множественного варианта торгового предложения */

    public function getModification(): ?string
    {
        return $this->modification;
    }

    public function setModification(?string $modification): void
    {
        $this->modification = $modification;
    }


    /**
     * Property
     */
    public function getProperty(): ?ArrayCollection
    {
        return $this->property;
    }

    public function setProperty(?ArrayCollection $property): self
    {
        $this->property = $property;
        return $this;
    }

    public function addProperty(Property\ProductFilterPropertyDTO $property): self
    {
        $filter = $this->property->filter(function(Property\ProductFilterPropertyDTO $element) use ($property) {
            return $element->getType() === $property->getType();
        });

        if($filter->isEmpty())
        {
            $this->property->add($property);
        }


        return $this;
    }

}
