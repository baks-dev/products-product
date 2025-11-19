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

namespace BaksDev\Products\Product\Entity\Offers\Variation\Opt;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Reference\Money\Type\Money;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * ProductVariationOpt
 *
 * @see ProductVariationOptEvent
 */
#[ORM\Entity]
#[ORM\Table(name: 'product_variation_opt')]
class ProductVariationOpt extends EntityEvent
{
    /** ID события */
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: ProductVariation::class, inversedBy: 'opt')]
    #[ORM\JoinColumn(name: 'variation', referencedColumnName: "id")]
    private ProductVariation $variation;

    /** Оптовая стоимость (валюта берется из розницы) */
    #[ORM\Column(type: Money::TYPE, nullable: true, options: ['default' => 0])]
    private ?Money $price = null;

    public function __construct(ProductVariation $variation)
    {
        $this->variation = $variation;
    }

    public function __toString(): string
    {
        return (string) $this->variation;
    }

    public function getPrice(): ?Money
    {
        return $this->price;
    }

    /** @return ProductVariationOptInterface */
    public function getDto($dto): mixed
    {
        if(is_string($dto) && class_exists($dto))
        {
            $dto = new $dto();
        }

        if($dto instanceof ProductVariationOptInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /** @var ProductVariationOptInterface $dto */
    public function setEntity($dto): mixed
    {
        if($dto instanceof ProductVariationOptInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}