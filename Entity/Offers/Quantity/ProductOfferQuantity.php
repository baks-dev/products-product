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

namespace BaksDev\Products\Product\Entity\Offers\Quantity;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Стоимость торгового предложения */


#[ORM\Entity]
#[ORM\Table(name: 'product_offer_quantity')]
class ProductOfferQuantity extends EntityEvent
{
    /** ID торгового предложения */
    #[Assert\NotBlank]
    #[Assert\Type(ProductOffer::class)]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: ProductOffer::class, inversedBy: 'quantity')]
    #[ORM\JoinColumn(name: 'offer', referencedColumnName: "id")]
    private ProductOffer $offer;

    /** В наличии */
    #[Assert\Type('integer')]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $quantity = 0; // 0 - нет в наличии

    /** Резерв */
    #[Assert\Type('integer')]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $reserve = 0;


    public function __construct(ProductOffer $offer)
    {
        $this->offer = $offer;
    }


    public function __toString(): string
    {
        return (string) $this->offer;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProductOfferQuantityInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof ProductOfferQuantityInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /** Добавляем в резерв указанное количество */
    public function addReserve(int $reserve): bool
    {
        $this->reserve += $reserve;
        return $this->reserve <= $this->quantity;
    }


    /** Удаляем из резерва указанное количество */
    public function subReserve(?int $reserve): bool
    {
        if(empty($this->reserve) || $reserve > $this->reserve)
        {
            return false;
        }

        $this->reserve -= $reserve;

        return true;
    }

    /** Присваиваем резерву указанное количество */
    public function setReserve(?int $reserve): self
    {
        $this->quantity ?: $this->quantity = 0;
        $this->reserve = $reserve ?: 0;

        return $this;
    }


    /** Удаляем из наличия указанное количество */
    public function subQuantity(?int $quantity): bool
    {
        if(empty($this->quantity) || $quantity > $this->quantity)
        {
            return false;
        }

        $this->quantity -= $quantity;

        return true;
    }

    /** Добавляем в наличие указанное количество */
    public function addQuantity(?int $quantity): self
    {
        $this->quantity ?: $this->quantity = 0;
        $this->quantity += $quantity;

        return $this;
    }

    /** Присваиваем наличию указанное количество */
    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity ?: 0;

        return $this;
    }
    
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }
    
    public function getReserve(): ?int
    {
        return $this->reserve;
    }
}
