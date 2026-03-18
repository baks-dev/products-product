<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation;

use BaksDev\Products\Category\Type\Offers\Variation\CategoryProductVariationUid;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariationInterface;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Barcode\ProductVariationBarcodeDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Cost\ProductVariationCostDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Image\ProductVariationImageCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Modification\ProductModificationCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Opt\ProductVariationOptDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Price\ProductVariationPriceDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Quantity\ProductVariationQuantityDTO;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductVariation */
final class ProductVariationCollectionDTO implements ProductVariationInterface
{
    /** ID множественного варианта торгового предложения категории */
    private ?CategoryProductVariationUid $categoryVariation;

    /** Постоянный уникальный идентификатор варианта */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly ProductVariationConst $const;

    /** Штрихкоды товара */
    #[Assert\Valid]
    private ArrayCollection $barcode;

    /** Заполненное значение */
    private ?string $value = null;

    /** Артикул */
    private ?string $article = null;

    /** Постфикс */
    private ?string $postfix = null;

    /** Стоимость торгового предложения */
    #[Assert\Valid]
    private ?ProductVariationPriceDTO $price = null;

    #[Assert\Valid]
    private ?ProductVariationCostDTO $cost = null;

    #[Assert\Valid]
    private ?ProductVariationOptDTO $opt = null;

    /** Количественный учет */
    #[Assert\Valid]
    private ProductVariationQuantityDTO $quantity;

    /** Дополнительные фото торгового предложения */
    #[Assert\Valid]
    private ArrayCollection $image;

    /** Модификации множественных вариантов */
    #[Assert\Valid]
    private ArrayCollection $modification;

    public function __construct()
    {
        $this->image = new ArrayCollection();
        $this->modification = new ArrayCollection();
        $this->quantity = new ProductVariationQuantityDTO();
        $this->price = new ProductVariationPriceDTO();
        $this->cost = new ProductVariationCostDTO();
        $this->opt = new ProductVariationOptDTO();

        $this->barcode = new ArrayCollection();
    }


    /** Постоянный уникальный идентификатор варианта */
    public function getConst(): ProductVariationConst
    {
        if(false === (new ReflectionProperty(self::class, 'const')->isInitialized($this)))
        {
            $this->const = new ProductVariationConst();
        }

        return $this->const;
    }


    public function setConst(ProductVariationConst $const): self
    {
        if(false === (new ReflectionProperty(self::class, 'const')->isInitialized($this)))
        {
            $this->const = $const;
        }

        return $this;
    }

    /** Штрихкоды */

    /**
     * @return ArrayCollection<int, ProductVariationBarcodeDTO>
     */
    public function getBarcode(): ArrayCollection
    {
        return $this->barcode;
    }

    public function addBarcode(ProductVariationBarcodeDTO $barcode): void
    {
        $isExist = $this->barcode->exists(
            function(int $key, ProductVariationBarcodeDTO $ProductVariationBarcodeDTO) use ($barcode) {

                return $ProductVariationBarcodeDTO->getValue()->equals($barcode->getValue());
            });

        if(false === $isExist)
        {
            $this->barcode->add($barcode);
        }
    }

    /** Заполненное значение */

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function removeBarcode(ProductVariationBarcodeDTO $barcode): void
    {
        $this->barcode->removeElement($barcode);
    }

    /** Артикул */

    public function getArticle(): ?string
    {
        return $this->article;
    }


    public function setArticle(?string $article): self
    {
        $this->article = $article;
        return $this;
    }


    /** Стоимость торгового предложения */

    public function getPrice(): ?ProductVariationPriceDTO
    {
        return $this->price;
    }


    public function setPrice(?ProductVariationPriceDTO $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCost(): ?ProductVariationCostDTO
    {
        return $this->cost;
    }

    public function setCost(?ProductVariationCostDTO $cost): self
    {
        $this->cost = $cost;
        return $this;
    }

    public function getOpt(): ?ProductVariationOptDTO
    {
        return $this->opt;
    }

    public function setOpt(?ProductVariationOptDTO $opt): self
    {
        $this->opt = $opt;
        return $this;
    }

    /** Количественный учет */

    public function getQuantity(): ProductVariationQuantityDTO
    {
        return $this->quantity;
    }

    /** Дополнительные фото торгового предложения */

    public function getImage(): ArrayCollection
    {
        return $this->image;
    }


    public function addImage(ProductVariationImageCollectionDTO $image): void
    {
        $filter = $this->image->filter(function(ProductVariationImageCollectionDTO $element) use ($image) {
            return !$image->file && $image->getName() === $element->getName();
        });

        if($filter->isEmpty())
        {
            $this->image->add($image);
        }
    }


    public function removeImage(ProductVariationImageCollectionDTO $image): void
    {
        $this->image->removeElement($image);
    }


    /**
     * Модификации множественных вариантов
     *
     * @return ArrayCollection<int, ProductModificationCollectionDTO>
     */
    public function getModification(): ArrayCollection
    {
        return $this->modification;
    }


    public function addModification(ProductModificationCollectionDTO $modification): void
    {
        if(!$this->modification->contains($modification))
        {
            $this->modification->add($modification);
        }
    }


    public function removeModification(ProductModificationCollectionDTO $modification): void
    {
        $this->modification->removeElement($modification);
    }


    /** ID множественного варианта торгового предложения категории */

    public function getCategoryVariation(): ?CategoryProductVariationUid
    {
        return $this->categoryVariation;
    }


    public function setCategoryVariation(?CategoryProductVariationUid $categoryVariation): self
    {
        $this->categoryVariation = $categoryVariation;
        return $this;
    }


    /** Постфикс */

    public function getPostfix(): ?string
    {
        return $this->postfix;
    }


    public function setPostfix(?string $postfix): self
    {
        $this->postfix = $postfix;
        return $this;
    }


}
