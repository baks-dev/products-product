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

namespace BaksDev\Products\Product\Forms\ProductFilter\Admin;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductFilter\ProductFilterInterface;
use Doctrine\Common\Collections\ArrayCollection;

final class ProductFilterDTO implements ProductFilterInterface
{
    /**
     * Категория
     */
    private ?CategoryProductUid $category = null;

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

    /**
     * Показать все профили
     */
    private bool $visible = false;


    /**
     * Скрыть выбор категории
     */
    private bool $invisible = false;


    private ?bool $all = null;


    public function __construct()
    {
        $this->property = new ArrayCollection();
    }

    public function getCategory(bool $readonly = false): ?CategoryProductUid
    {
        if($readonly)
        {
            return $this->category;
        }

        return $this->category;
    }

    /**
     * Категория
     */
    public function setCategory(CategoryProductUid|string|null $category): void
    {
        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->category = $category;
    }

    /**
     * Торговое предложение
     */

    public function getOffer(): ?string
    {
        return $this->offer;
    }

    public function setOffer(?string $offer): void
    {
        $this->offer = $offer;
    }


    /**
     * Множественный вариант торгового предложения
     */

    public function getVariation(): ?string
    {
        return $this->variation;
    }

    public function setVariation(?string $variation): void
    {
        $this->variation = $variation;
    }


    /**
     * Модификатор множественного варианта торгового предложения
     */

    public function getModification(): ?string
    {
        return $this->modification;
    }

    public function setModification(?string $modification): void
    {
        $this->modification = $modification;
    }

    /**
     * Показать все профили
     */
    public function getAll(): bool
    {
        return $this->all === true;
    }

    public function setAll(bool $all): self
    {

        $this->all = $all;
        return $this;
    }

    public function allVisible(): self
    {
        $this->all = null;
        $this->visible = true;
        return $this;
    }

    public function isAllVisible(): bool
    {
        return $this->visible;
    }


    public function categoryInvisible(): self
    {
        $this->invisible = true;
        return $this;
    }

    public function isInvisible(): bool
    {
        return $this->invisible;
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
