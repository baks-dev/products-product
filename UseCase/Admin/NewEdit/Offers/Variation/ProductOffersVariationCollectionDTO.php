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


namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation;

use BaksDev\Products\Category\Type\Offers\Variation\ProductCategoryOffersVariationUid;
use BaksDev\Products\Product\Entity\Offers\OffersInterface;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductOfferVariationInterface;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\OfferDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductOffersVariationCollectionDTO implements ProductOfferVariationInterface
{
	/** ID множественного варианта торгового предложения категории */
	private ProductCategoryOffersVariationUid $categoryVariation;
	
	/** Заполненное значение */
	private ?string $value = null;
	
	/** Артикул */
	private ?string $article = null;
	
	/** Стоимость торгового предложения */
	private ?Price\ProductOfferVariationPriceDTO $price = null;
	
	/** Количественный учет */
	private ?Quantity\ProductOfferVariationQuantityDTO $quantity = null;
	
	/** Дополнительные фото торгового предложения */
	private ArrayCollection $image;
	

	public function __construct()
	{
		$this->image = new ArrayCollection();
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
	
	public function getPrice() : ?Price\ProductOfferVariationPriceDTO
	{
		return $this->price;
	}
	
	
	public function setPrice(?Price\ProductOfferVariationPriceDTO $price) : void
	{
		$this->price = $price;
	}
	
	
	/** Количественный учет */
	
	public function getQuantity() : ?Quantity\ProductOfferVariationQuantityDTO
	{
		return $this->quantity;
	}
	
	
	public function setQuantity(?Quantity\ProductOfferVariationQuantityDTO $quantity) : void
	{
		$this->quantity = $quantity;
	}
	
	
	/** Дополнительные фото торгового предложения */
	
	public function getImage() : ArrayCollection
	{
		return $this->image;
	}
	
	
	public function addImage(Image\ProductOfferVariationImageCollectionDTO $image) : void
	{
		if(!$this->image->contains($image))
		{
			$this->image->add($image);
		}
	}
	
	
	public function removeImage(Image\ProductOfferVariationImageCollectionDTO $image) : void
	{
		$this->image->removeElement($image);
	}
	
	
	/**
	 * @return ProductCategoryOffersVariationUid
	 */
	public function getCategoryVariation() : ProductCategoryOffersVariationUid
	{
		return $this->categoryVariation;
	}
	
	
	/**
	 * @param ProductCategoryOffersVariationUid $categoryVariation
	 */
	public function setCategoryVariation(ProductCategoryOffersVariationUid $categoryVariation) : void
	{
		$this->categoryVariation = $categoryVariation;
	}
	

}

