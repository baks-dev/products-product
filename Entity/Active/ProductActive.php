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

namespace BaksDev\Products\Product\Entity\Active;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Статусы активности Продукта */


#[ORM\Entity]
#[ORM\Table(name: 'product_active')]
#[ORM\Index(columns: ['active'])]
#[ORM\Index(columns: ['active_from', 'active_to'])]
class ProductActive extends EntityEvent
{
    /** ID события */
    #[Assert\NotBlank]
    #[Assert\Type(ProductEvent::class)]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: ProductEvent::class, inversedBy: 'active')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ProductEvent $event;

    /** Статус активности товара */
    #[Assert\Type('bool')]
    #[ORM\Column(name: 'active', type: Types::BOOLEAN, nullable: false)]
    private bool $active = true;

    /** Начало активности */
    #[Assert\NotBlank]
    #[ORM\Column(name: 'active_from', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $activeFrom;

    /** Окончание активности */
    #[ORM\Column(name: 'active_to', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $activeTo = null;


    public function __construct(ProductEvent $event)
    {
        $this->event = $event;
        $this->activeFrom = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProductActiveInterface)
        {
            return parent::getDto($dto);
        }
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        $format = "H:i";

        if($dto->activeFromTime())
        {
            $time = date_parse_from_format($format, $dto->activeFromTime()->format($format)); /* парсим время */
            $getActiveFrom = $dto->getActiveFrom()->setTime($time['hour'], $time['minute']);
            $dto->setActiveFrom($getActiveFrom);
        }

        if($dto->getActiveTo() && $dto->activeToTime())
        {
            $time = date_parse_from_format($format, $dto->activeToTime()->format($format)); /* парсим время */
            $getActiveTo = $dto->getActiveTo()->setTime($time['hour'], $time['minute']);
            $dto->setActiveTo($getActiveTo);
        }

        if($dto instanceof ProductActiveInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

}
