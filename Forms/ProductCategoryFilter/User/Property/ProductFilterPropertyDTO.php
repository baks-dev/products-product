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

namespace BaksDev\Products\Product\Forms\ProductCategoryFilter\User\Property;

use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductPropertyFilter */
final class ProductFilterPropertyDTO
{
    /** Постоянное неизменяемое значение */
    private ?string $const = null;

    /** Название поля */
    private ?string $label = null;

    /** Тип поля */
    private ?string $type = null;

    /** Передаваемое значение */
    private mixed $value = null;

    /** Домен перевода */
    private mixed $domain = null;


    /**
     * Const
     */
    public function getConst(): ?string
    {
        return $this->const;
    }

    public function setConst(?string $const): self
    {
        $this->const = $const;
        return $this;
    }


    /**
     * Label
     */

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Type
     */

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Value
     */

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = !empty($value) && $value !== 'false' ? $value : null;
        return $this;
    }

    /**
     * Domain
     */
    public function getDomain(): mixed
    {
        return $this->domain;
    }

    public function setDomain(mixed $domain): self
    {
        $this->domain = $domain;
        return $this;
    }


}
