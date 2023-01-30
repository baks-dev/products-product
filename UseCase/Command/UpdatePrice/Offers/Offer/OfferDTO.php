<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Products\Product\UseCase\Command\UpdatePrice\Offers\Offer;

use App\Module\Products\Category\Type\Offers\Id\OffersUid;
use BaksDev\Products\Product\Entity\Offers\Offer\OfferInterface;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\UseCase\Command\UpdatePrice\Offers\Offer\Image\ImageCollectionDTO;
use BaksDev\Products\Product\UseCase\Command\UpdatePrice\Offers\Offer\Price\PriceDTO;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class OfferDTO implements OfferInterface
{
	//    /** ID */
	//    private readonly ProductOfferUid $id;
	
	/** Постоянный уникальный идентификатор ТП */
	#[Assert\Uuid]
	private readonly ProductOfferConst $const;
	
	/** ID торгового предложения категории */
	#[Assert\Uuid]
	private readonly OffersUid $offer;
	
	/** Заполненное значение */
	private readonly string $value;
	
	/** Артикул */
	private readonly ?string $article;
	
	/** Стоимость торгового предложения */
	private ?PriceDTO $price;
	
	/** Дополнительные фото торгового предложения */
	private ArrayCollection $images;
	
	
	public function __construct()
	{
		$this->images = new ArrayCollection();
		$this->price = new PriceDTO();
	}
	
	
	/**
	 * @param ProductOfferUid $id
	 */
	public function setId(ProductOfferUid $id) : void
	{
		$this->id = $id;
	}
	
	
	/**
	 * @return ProductOfferUid
	 */
	public function getId() : ProductOfferUid
	{
		return $this->id;
	}
	
	
	public function equals(ProductOfferUid $id) : bool
	{
		return $this->id->equals($id);
	}
	
	
	/* OFFER */
	/**
	 * @return OffersUid
	 */
	public function getOffer() : OffersUid
	{
		return $this->offer;
	}
	
	
	public function setOffer(OffersUid|string $offer) : void
	{
		$this->offer = $offer instanceof OffersUid ? $offer : new OffersUid($offer);
	}
	
	
	/* IMAGE */
	
	/**
	 * @return ArrayCollection
	 */
	public function getImages() : ArrayCollection
	{
		return $this->images;
	}
	
	
	public function addImage(ImageCollectionDTO $image) : void
	{
		$this->images->add($image);
	}
	
	
	public function getImageClass() : ImageCollectionDTO
	{
		return new ImageCollectionDTO();
	}
	
	
	
	
	/* VALUE */
	
	/**
	 * @return string|null
	 */
	public function getValue() : ?string
	{
		return $this->value;
	}
	
	
	/**
	 * @param string|null $value
	 */
	public function setValue(mixed $value) : void
	{
		$this->value = is_string($value) ? $value : $value->value;
	}
	
	
	/* PRICE */
	
	/**
	 * @return PriceDTO
	 */
	public function getPrice() : PriceDTO
	{
		return $this->price;
	}
	
	
	/**
	 * @param PriceDTO $price
	 */
	public function setPrice(PriceDTO $price) : void
	{
		$this->price = $price;
	}
	
	
	public function getPriceClass() : PriceDTO
	{
		return new PriceDTO();
	}
	
	
	
	/* ARTICLE */
	
	/**
	 * @return string|null
	 */
	public function getArticle() : ?string
	{
		return $this->article;
	}
	
	
	public function setArticle(?string $article) : void
	{
		$this->article = $article;
	}
	
	/* CONST */
	
	/**
	 * @return ProductOfferConst
	 */
	public function getConst() : ProductOfferConst
	{
		return $this->const;
	}
	
	
	/**
	 * @param ProductOfferConst $const
	 */
	public function setConst(ProductOfferConst $const) : void
	{
		$this->const = $const;
	}
	
}

