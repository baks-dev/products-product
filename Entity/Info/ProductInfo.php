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

namespace BaksDev\Products\Product\Entity\Info;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityReadonly;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Неизменяемые данные Продукта */


#[ORM\Entity]
#[ORM\Table(name: 'product_info')]
class ProductInfo extends EntityReadonly
{
    public const TABLE = 'product_info';

    /** ID Product */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductUid::TYPE)]
    private ProductUid $product;

    /** ID события */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\OneToOne(targetEntity: ProductEvent::class, inversedBy: 'info')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ProductEvent $event;

    /** Семантическая ссылка на товар */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $url;

    /** Артикул товара */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $article = null;

    /** Штрихкод товара */
    #[ORM\Column(type: ProductBarcode::TYPE, nullable: true)]
    private ?ProductBarcode $barcode = null;

    /** Сортировка */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 500])]
    private int $sort = 500;

    /** Профиль пользователя, которому принадлежит товар */
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE, nullable: true)]
    private ?UserProfileUid $profile = null;

    public function __construct(ProductEvent $event)
    {
        $this->event = $event;
        $this->product = $event->getMain();
    }

    public function __toString(): string
    {
        return (string) $this->product;
    }

    public function getProduct(): ProductUid
    {
        return $this->product;
    }

    public function setEvent(ProductEvent $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProductInfoInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof ProductInfoInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function updateUrlUniq(): void
    {
        $this->url = uniqid($this->url.'_', false);
    }
}
