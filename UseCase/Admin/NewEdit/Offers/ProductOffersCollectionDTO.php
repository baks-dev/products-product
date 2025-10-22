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
 *
 */

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers;

use BaksDev\Products\Category\Type\Offers\Id\CategoryProductOffersUid;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\ProductOffersInterface;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Cost\ProductOfferCostDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Image\ProductOfferImageCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Opt\ProductOfferOptDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Price\ProductOfferPriceDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Quantity\ProductOfferQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\ProductVariationCollectionDTO;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductOffer */
final class ProductOffersCollectionDTO implements ProductOffersInterface
{
    /** ID торгового предложения категории */
    private ?CategoryProductOffersUid $categoryOffer;

    /** Постоянный уникальный идентификатор ТП */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly ProductOfferConst $const;

    /** Штрихкод товара */
    private ProductBarcode $barcode;

    /** Индивидуальное название */
    private ?string $name = null;

    /** Заполненное значение */
    private ?string $value = null;

    /** Артикул */
    private ?string $article = null;

    /** Постфикс */
    private ?string $postfix = null;


    /** Стоимость торгового предложения */
    #[Assert\Valid]
    private ?ProductOfferPriceDTO $price = null;

    /** Себестоимость торгового предложения */
    #[Assert\Valid]
    private ?ProductOfferCostDTO $cost = null;

    /** Себестоимость торгового предложения (по курсу) */
    #[Assert\Valid]
    private ?ProductOfferOptDTO $opt = null;

    /** Количественный учет */
    #[Assert\Valid]
    private ProductOfferQuantityDTO $quantity;

    /** Дополнительные фото торгового предложения */
    #[Assert\Valid]
    private ArrayCollection $image;

    /** Коллекция вариаций в торговом предложении  */
    #[Assert\Valid]
    private ArrayCollection $variation;


    public function __construct()
    {
        $this->image = new ArrayCollection();
        $this->variation = new ArrayCollection();
        $this->quantity = new ProductOfferQuantityDTO();
    }


    /** Постоянный уникальный идентификатор ТП */

    public function getConst(): ProductOfferConst
    {
        if(false === (new ReflectionProperty(self::class, 'const')->isInitialized($this)))
        {
            $this->const = new ProductOfferConst();

            if(false === (new ReflectionProperty(self::class, 'barcode')->isInitialized($this)))
            {
                $this->barcode = new ProductBarcode(ProductBarcode::generate());
            }
        }

        return $this->const;
    }

    public function setConst(ProductOfferConst $const): void
    {
        if(false === (new ReflectionProperty(self::class, 'const')->isInitialized($this)))
        {
            $this->const = $const;
        }
    }

    /**
     * Barcode
     */
    public function getBarcode(): ProductBarcode
    {
        if(false === (new ReflectionProperty(self::class, 'barcode')->isInitialized($this)))
        {
            $this->barcode = new ProductBarcode(ProductBarcode::generate());
        }

        return $this->barcode;
    }

    public function setBarcode(?ProductBarcode $barcode): self
    {
        if(is_null($barcode))
        {
            $barcode = new ProductBarcode(ProductBarcode::generate());
        }


        $this->barcode = $barcode;
        return $this;
    }


    /** Артикул */

    public function getArticle(): ?string
    {
        return $this->article;
    }

    public function setArticle(?string $article): void
    {
        $this->article = $article;
    }

    /** Стоимость торгового предложения */


    public function getPrice(): ?ProductOfferPriceDTO
    {
        return $this->price;
    }

    public function setPrice(?ProductOfferPriceDTO $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCost(): ?ProductOfferCostDTO
    {
        return $this->cost;
    }

    public function setCost(?ProductOfferCostDTO $cost): self
    {
        $this->cost = $cost;
        return $this;
    }


    public function getOpt(): ?ProductOfferOptDTO
    {
        return $this->opt;
    }

    public function setOpt(?ProductOfferOptDTO $opt): self
    {
        $this->opt = $opt;
        return $this;
    }

    /** Количественный учет */

    public function getQuantity(): ProductOfferQuantityDTO
    {
        return $this->quantity;
    }

    /** Дополнительные фото торгового предложения */

    public function getImage(): ArrayCollection
    {
        return $this->image;
    }

    public function addImage(ProductOfferImageCollectionDTO $image): void
    {

        $filter = $this->image->filter(function(ProductOfferImageCollectionDTO $element) use ($image) {
            return !$image->file && $image->getName() === $element->getName();
        });

        if($filter->isEmpty())
        {
            $this->image->add($image);

        }
    }

    public function removeImage(ProductOfferImageCollectionDTO $image): void
    {
        $this->image->removeElement($image);
    }

    /** Коллекция торговых предложений */

    public function getVariation(): ArrayCollection
    {
        return $this->variation;
    }

    public function addVariation(ProductVariationCollectionDTO $variation): void
    {

        $filter = $this->variation->filter(function(ProductVariationCollectionDTO $element) use (
            $variation
        ) {
            return $variation->getValue() === $element->getValue() && $variation->getBarcode() === $element->getBarcode();
        });

        if($filter->isEmpty())
        {
            $this->variation->add($variation);
        }

    }

    /** Заполненное значение */

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function removeVariation(ProductVariationCollectionDTO $variation): void
    {
        $this->variation->removeElement($variation);
    }


    /** ID торгового предложения категории */
    public function getCategoryOffer(): ?CategoryProductOffersUid
    {
        return $this->categoryOffer;
    }


    public function setCategoryOffer(?CategoryProductOffersUid $categoryOffer): void
    {
        $this->categoryOffer = $categoryOffer;
    }

    /** Постфикс */

    public function getPostfix(): ?string
    {
        return $this->postfix;
    }

    public function setPostfix(?string $postfix): void
    {
        $this->postfix = $postfix;
    }

    /**
     * Name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}