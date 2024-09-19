<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Products\Product\Forms\ProductFilter\Admin;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductFilter\ProductFilterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

final class ProductFilterDTO implements ProductFilterInterface
{
    public const category = 'vKmDiRWOPq';
    public const offer = 'nOuksvfLiZ';
    public const variation = 'AItZEGxyrY';
    public const modification = 'FxCmFxVZxk';
    public const all = 'zQWwZXxhf';

    private Request $request;

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

    private ?bool $all = null;


    public function __construct(Request $request)
    {
        $this->property = new ArrayCollection();
        $this->request = $request;
    }

    /**
     * Категория
     */
    public function setCategory(CategoryProductUid|string|null $category): void
    {
        //        if(empty($category))
        //        {
        //            $this->request->getSession()->remove(self::category);
        //        }

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->category = $category;

    }


    public function getCategory(bool $readonly = false): ?CategoryProductUid
    {
        if($readonly)
        {
            return $this->category;
        }
        //
        //        return $this->category ?: $this->request->getSession()->get(self::category);

        return $this->category;

    }


    /**
     * Торговое предложение
     */

    public function getOffer(): ?string
    {
        //return $this->offer ?: $this->request->getSession()->get(self::offer);
        return $this->offer;
    }

    public function setOffer(?string $offer): void
    {
        //        if(empty($offer) || empty($this->category))
        //        {
        //            $this->request->getSession()->remove(self::offer);
        //        }

        $this->offer = $offer;
    }


    /**
     * Множественный вариант торгового предложения
     */

    public function getVariation(): ?string
    {
        // return $this->variation ?: $this->request->getSession()->get(self::variation);
        return $this->variation;
    }

    public function setVariation(?string $variation): void
    {
        //        if(empty($variation) || empty($this->category) || empty($this->offer))
        //        {
        //            $this->request->getSession()->remove(self::variation);
        //        }

        $this->variation = $variation;
    }


    /**
     * Модификатор множественного варианта торгового предложения
     */

    public function getModification(): ?string
    {
        //return $this->modification ?: $this->request->getSession()->get(self::modification);
        return $this->modification;
    }

    public function setModification(?string $modification): void
    {
        //        if(empty($modification) || empty($this->category) || empty($this->offer) || empty($this->variation))
        //        {
        //            $this->request->getSession()->remove(self::modification);
        //        }

        $this->modification = $modification;
    }

    /**
     * Показать все профили
     */
    public function getAll(): bool
    {
        //        if($this->all === null && $this->request->getSession()->get(self::all) === false)
        //        {
        //            return false;
        //        }



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
        //        if($this->property === null)
        //        {
        //            $this->property = new ArrayCollection();
        //        }

        $filter = $this->property->filter(function (Property\ProductFilterPropertyDTO $element) use ($property) {
            return $element->getType() === $property->getType();
        });

        if($filter->isEmpty())
        {
            $this->property->add($property);
        }


        return $this;
    }

}
