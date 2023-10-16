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

namespace BaksDev\Products\Product\Entity\Offers\Variation\Modification;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Offers\Modification\ProductCategoryModificationUid;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Вариант в торговом предложения */

#[ORM\Entity]
#[ORM\Table(name: 'product_modification')]
#[ORM\Index(columns: ['const'])]
#[ORM\Index(columns: ['article'])]
class ProductModification extends EntityEvent
{
    public const TABLE = 'product_modification';

    /** ID модификации множественного варианта варианта */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductModificationUid::TYPE)]
    private ProductModificationUid $id;

    /** ID множественного варианта  */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: ProductVariation::class, inversedBy: 'modification')]
    #[ORM\JoinColumn(name: 'variation', referencedColumnName: 'id')]
    private ProductVariation $variation;

    /** Постоянный уникальный идентификатор модификации */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProductModificationConst::TYPE)]
    private readonly ProductModificationConst $const;

    /** ID модификации категории */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(name: 'category_modification', type: ProductCategoryModificationUid::TYPE, nullable: true)]
    private ProductCategoryModificationUid $categoryModification;

    /** Заполненное значение */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    /** Артикул */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $article = null;

    /** Постфикс */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $postfix = null;

    /** Стоимость модификации */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'modification', targetEntity: Price\ProductModificationPrice::class, cascade: ['all'])]
    private ?Price\ProductModificationPrice $price = null;

    /** Количественный учет */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'modification', targetEntity: Quantity\ProductModificationQuantity::class, cascade: ['all'])]
    private ?Quantity\ProductModificationQuantity $quantity = null;

    /** Дополнительные фото модификации */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'modification', targetEntity: Image\ProductModificationImage::class, cascade: ['all'])]
    private Collection $image;

    public function __construct(ProductVariation $variation)
    {
        $this->id = new ProductModificationUid();
        $this->variation = $variation;
        $this->price = new Price\ProductModificationPrice($this);
        $this->quantity = new Quantity\ProductModificationQuantity($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ProductModificationUid
    {
        return $this->id;
    }

    public function getVariation(): ProductVariation
    {
        return $this->variation;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Const.
     */
    public function getConst(): ProductModificationConst
    {
        return $this->const;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if ($dto instanceof ProductModificationInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof ProductModificationInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /**
     * Image
     */
    public function getImage(): Collection
    {
        return $this->image;
    }


}
