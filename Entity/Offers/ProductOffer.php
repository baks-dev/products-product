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

namespace BaksDev\Products\Product\Entity\Offers;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Offers\Id\ProductCategoryOffersUid;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

// Торговые предложения

#[ORM\Entity()]
#[ORM\Table(name: 'product_offer')]
#[ORM\Index(columns: ['const'])]
#[ORM\Index(columns: ['article'])]
class ProductOffer extends EntityEvent
{
    public const TABLE = 'product_offer';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductOfferUid::TYPE)]
    private ProductOfferUid $id;

    /** ID события */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'offer')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ProductEvent $event;

    /** ID торгового предложения категории */
    #[Assert\Uuid]
    #[Assert\Type(ProductCategoryOffersUid::class)]
    #[ORM\Column(name: 'category_offer', type: ProductCategoryOffersUid::TYPE, nullable: true)]
    private ?ProductCategoryOffersUid $categoryOffer = null;

    /** Постоянный уникальный идентификатор ТП */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProductOfferConst::TYPE)]
    private readonly ProductOfferConst $const;

    /** Заполненное значение */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $value = null;

    /** Артикул */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $article = null;

    /** Штрихкод товара */
    #[ORM\Column(type: ProductBarcode::TYPE, nullable: true)]
    private ?ProductBarcode $barcode = null;

    /** Постфикс */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $postfix = null;

    /** Стоимость торгового предложения */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'offer', targetEntity: Price\ProductOfferPrice::class, cascade: ['all'])]
    private ?Price\ProductOfferPrice $price = null;

    /** Количественный учет */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'offer', targetEntity: Quantity\ProductOfferQuantity::class, cascade: ['all'])]
    private ?Quantity\ProductOfferQuantity $quantity = null;

    /** Дополнительные фото торгового предложения */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: Image\ProductOfferImage::class, cascade: ['all'])]
    #[ORM\OrderBy(['root' => 'DESC'])]
    private Collection $image;

    /** Коллекция вариаций в торговом предложении  */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: Variation\ProductVariation::class, cascade: ['all'])]
    private Collection $variation;

    public function __construct(ProductEvent $event)
    {
        $this->event = $event;
        $this->id = new ProductOfferUid();
        $this->price = new Price\ProductOfferPrice($this);
        $this->quantity = new Quantity\ProductOfferQuantity($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ProductOfferUid
    {
        return $this->id;
    }

    /**
     * Const.
     */
    public function getConst(): ProductOfferConst
    {
        return $this->const;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if ($dto instanceof ProductOffersInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof ProductOffersInterface || $dto instanceof self)
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

    /**
     * Variation
     */
    public function getVariation(): Collection
    {
        return $this->variation;
    }

}
