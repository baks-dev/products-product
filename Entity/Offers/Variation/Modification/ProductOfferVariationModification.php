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

use BaksDev\Products\Category\Type\Offers\Modification\ProductCategoryOffersVariationModificationUid;
use BaksDev\Products\Category\Type\Offers\Variation\ProductCategoryOffersVariationUid;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductOfferVariation;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductOfferVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductOfferVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductOfferVariationModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductOfferVariationModificationUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Вариант в торговом предложения */


#[ORM\Entity]
#[ORM\Table(name: 'product_offer_variation_modification')]
#[ORM\Index(columns: ['const'])]
#[ORM\Index(columns: ['article'])]
class ProductOfferVariationModification extends EntityEvent
{
    public const TABLE = 'product_offer_variation_modification';

    /** ID модификации множественного варианта варианта */
    #[ORM\Id]
    #[ORM\Column(type: ProductOfferVariationModificationUid::TYPE)]
    private ProductOfferVariationModificationUid $id;

    /** ID множественного варианта  */
    #[ORM\ManyToOne(targetEntity: ProductOfferVariation::class, inversedBy: 'modification')]
    #[ORM\JoinColumn(name: 'variation', referencedColumnName: 'id')]
    private ProductOfferVariation $variation;

    /** Постоянный уникальный идентификатор модификации */
    #[ORM\Column(type: ProductOfferVariationModificationConst::TYPE)]
    private readonly ProductOfferVariationModificationConst $const;

    /** ID одификации категории */
    #[ORM\Column(name: 'category_modification', type: ProductCategoryOffersVariationModificationUid::TYPE, nullable: true)]
    private ProductCategoryOffersVariationModificationUid $categoryModification;

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
    #[ORM\OneToOne(mappedBy: 'modification', targetEntity: Price\ProductOfferVariationModificationPrice::class, cascade: ['all'])]
    private ?Price\ProductOfferVariationModificationPrice $price = null;

    /** Количественный учет */
    #[ORM\OneToOne(mappedBy: 'modification', targetEntity: Quantity\ProductOfferVariationModificationQuantity::class, cascade: ['all'])]
    private ?Quantity\ProductOfferVariationModificationQuantity $quantity = null;

    /** Дополнительные фото модификации */
    #[ORM\OneToMany(mappedBy: 'modification', targetEntity: Image\ProductOfferVariationModificationImage::class, cascade: ['all'])]
    private Collection $image;


    public function __construct(ProductOfferVariation $variation)
    {
        $this->id = new ProductOfferVariationModificationUid();
        $this->variation = $variation;

        $this->price = new Price\ProductOfferVariationModificationPrice($this);
        $this->quantity = new Quantity\ProductOfferVariationModificationQuantity($this);
    }


    public function __clone()
    {
        $this->id = new ProductOfferVariationModificationUid();
    }


    public function getId(): ProductOfferVariationModificationUid
    {
        return $this->id;
    }


    public function getVariation(): ProductOfferVariation
    {
        return $this->variation;
    }


    public function getValue(): ?string
    {
        return $this->value;
    }


    public function getDto($dto): mixed
    {
        if ($dto instanceof ProductOfferVariationModificationInterface) {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if ($dto instanceof ProductOfferVariationModificationInterface) {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
