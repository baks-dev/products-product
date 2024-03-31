<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Entity\Offers\Offer\Offer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Стоимость варианта торгового предложения */


#[ORM\Entity]
#[ORM\Table(name: 'product_modification_quantity')]
class ProductModificationQuantity extends EntityEvent
{

    public const TABLE = 'product_modification_quantity';

    /** ID события */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: ProductModification::class, inversedBy: 'quantity')]
    #[ORM\JoinColumn(name: 'modification', referencedColumnName: "id")]
    private ProductModification $modification;

    /** В наличии */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $quantity = 0; // 0 - нет в наличии

    /** Резерв */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $reserve = 0;


    public function __construct(ProductModification $modification)
    {
        $this->modification = $modification;
    }

    public function __toString(): string
    {
        return (string) $this->modification;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProductModificationQuantityInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof ProductModificationQuantityInterface || $dto instanceof self)
        {
            if(empty($dto->getQuantity()) && empty($dto->getReserve()))
            {
                return false;
            }

            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    /** Добавляем в резерв указанное количество */
    public function addReserve(int $reserve): void
    {
        $this->reserve += $reserve;
    }


    /** Удаляем из резерва указанное количество */
    public function subReserve(?int $reserve): void
    {
        $this->reserve -= $reserve;
    }


    /** Удаляем из наличия указанное количество */
    public function subQuantity(?int $quantity): void
    {
        $this->quantity -= $quantity;
    }


    /** Добавляем в наличие указанное количество */
    public function addQuantity(?int $quantity): void
    {
        $this->quantity += $quantity;
    }
}
