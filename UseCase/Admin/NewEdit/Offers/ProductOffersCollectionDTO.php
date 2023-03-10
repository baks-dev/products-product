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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers;

use BaksDev\Products\Category\Type\Offers\Id\ProductCategoryOffersUid;
use BaksDev\Products\Product\Entity\Offers\ProductOffersInterface;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\OfferDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductOffersCollectionDTO implements ProductOffersInterface
{
	/** ID торгового предложения категории */
	private ProductCategoryOffersUid $categoryOffer;
	
	/** Заполненное значение */
	private ?string $value = null;
	
	/** Артикул */
	private ?string $article = null;
	
	/** Стоимость торгового предложения */
	private ?Price\ProductOfferPriceDTO $price = null;
	
	/** Количественный учет */
	private ?Quantity\ProductOfferQuantityDTO $quantity = null;
	
	/** Дополнительные фото торгового предложения */
	private ArrayCollection $image;
	
	/** Коллекция вариаций в торговом предложении  */
	private ArrayCollection $variation;
	
	
	public function __construct()
	{
		$this->image = new ArrayCollection();
		$this->variation = new ArrayCollection();
	}
	
	
	/** Заполненное значение */
	
	public function getValue() : ?string
	{
		return $this->value;
	}
	
	
	public function setValue(?string $value) : void
	{
		$this->value = $value;
	}
	
	
	/** Артикул */
	
	public function getArticle() : ?string
	{
		return $this->article;
	}
	
	
	public function setArticle(?string $article) : void
	{
		$this->article = $article;
	}
	
	
	/** Стоимость торгового предложения */
	
	public function getPrice() : ?Price\ProductOfferPriceDTO
	{
		return $this->price;
	}
	
	
	public function setPrice(?Price\ProductOfferPriceDTO $price) : void
	{
		$this->price = $price;
	}
	
	
	/** Количественный учет */
	
	public function getQuantity() : ?Quantity\ProductOfferQuantityDTO
	{
		return $this->quantity;
	}
	
	
	public function setQuantity(?Quantity\ProductOfferQuantityDTO $quantity) : void
	{
		$this->quantity = $quantity;
	}
	
	
	/** Дополнительные фото торгового предложения */
	
	public function getImage() : ArrayCollection
	{
		return $this->image;
	}
	
	
	public function addImage(Image\ProductOfferImageCollectionDTO $image) : void
	{
		if(!$this->image->contains($image))
		{
			$this->image->add($image);
		}
	}
	
	
	public function removeImage(Image\ProductOfferImageCollectionDTO $image) : void
	{
		$this->image->removeElement($image);
	}
	
	
	/** Коллекция орговых предложений */
	
	public function getVariation() : ArrayCollection
	{
		return $this->variation;
	}
	
	
	public function addVariation(Variation\ProductOffersVariationCollectionDTO $variation) : void
	{
		if(!$this->variation->contains($variation))
		{
			$this->variation->add($variation);
		}
	}
	
	
	public function removeVariation(Variation\ProductOffersVariationCollectionDTO $variation) : void
	{
		$this->variation->removeElement($variation);
	}
	
	
	
	/** ID торгового предложения категории */
	public function getCategoryOffer() : ProductCategoryOffersUid
	{
		return $this->categoryOffer;
	}
	

	public function setCategoryOffer(ProductCategoryOffersUid $categoryOffer) : void
	{
		$this->categoryOffer = $categoryOffer;
	}
	
}

