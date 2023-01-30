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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit;

use ArrayIterator;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Active\ActiveInterface;
use BaksDev\Products\Product\Entity\Category\CategoryInterface;
use BaksDev\Products\Product\Entity\Event\ProductEventInterface;
use BaksDev\Products\Product\Entity\Files\FilesInterface;
use BaksDev\Products\Product\Entity\Offers\OffersInterface;
use BaksDev\Products\Product\Entity\Photo\PhotoInterface;
use BaksDev\Products\Product\Entity\Price\PriceInterface;
use BaksDev\Products\Product\Entity\Property\PropertyInterface;
use BaksDev\Products\Product\Entity\Seo\SeoInterface;
use BaksDev\Products\Product\Entity\Trans\TransInterface;
use BaksDev\Products\Product\Entity\Video\VideoInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Active\ActiveDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Files\FilesCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Info\InfoDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\OffersCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Photo\PhotoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Price\PriceDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Property\PropertyCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Seo\SeoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Video\VideoCollectionDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductDTO implements ProductEventInterface
{
	/** Идентификатор */
	#[Assert\Uuid]
	private ?ProductEventUid $id = null;
	
	#[Assert\Valid]
	private InfoDTO $info;
	
	#[Assert\Valid]
	private ActiveDTO $active;
	
	#[Assert\Valid]
	private PriceDTO $price;
	
	#[Assert\Valid]
	private ArrayCollection $category;
	
	#[Assert\Valid]
	private ArrayCollection $file;
	
	#[Assert\Valid]
	private ArrayCollection $video;
	
	#[Assert\Valid]
	//private ArrayCollection $offer;
	
	#[Assert\Valid]
	private ArrayCollection $photo;
	
	#[Assert\Valid]
	private ArrayCollection $property;
	
	#[Assert\Valid]
	private ArrayCollection $seo;
	
	#[Assert\Valid]
	private ArrayCollection $translate;
	
	private ArrayCollection $collectionProperty;
	
	
	public function __construct()
	{
		$this->info = new InfoDTO();
		$this->active = new ActiveDTO();
		$this->price = new PriceDTO();
		
		$this->category = new ArrayCollection();
		$this->file = new ArrayCollection();
		//$this->offers = new ArrayCollection();
		$this->photo = new ArrayCollection();
		$this->property = new ArrayCollection();
		$this->seo = new ArrayCollection();
		$this->translate = new ArrayCollection();
		$this->video = new ArrayCollection();
	}
	
	
	public function getEvent() : ?ProductEventUid
	{
		return $this->id;
	}
	
	
	public function setId(ProductEventUid $id) : void
	{
		$this->id = $id;
	}
	
	
	/* INFO  */
	
	/**
	 * @return InfoDTO
	 */
	public function getInfo() : InfoDTO
	{
		return $this->info;
	}
	
	
	/**
	 * @param InfoDTO $info
	 */
	public function setInfo(InfoDTO $info) : void
	{
		$this->info = $info;
	}
	
	
	/* CATEGORIES */
	
	public function addCategory(CategoryCollectionDTO $category) : void
	{
		if(!$this->category->contains($category))
		{
			$this->category[] = $category;
		}
	}
	
	
	public function removeCategory(CategoryCollectionDTO $category) : void
	{
		$this->category->removeElement($category);
	}
	
	
	/**
	 * @return ArrayCollection
	 */
	public function getCategory() : ArrayCollection
	{
		if($this->category->isEmpty())
		{
			$CategoryCollectionDTO = new CategoryCollectionDTO();
			$this->addCategory($CategoryCollectionDTO);
		}
		
		return $this->category;
	}
	
	
	/* ACTIVE */
	
	/**
	 * @return ActiveDTO
	 */
	public function getActive() : ActiveDTO
	{
		return $this->active;
	}
	
	
	/**
	 * @param ActiveDTO $active
	 */
	public function setActive(ActiveDTO $active) : void
	{
		$this->active = $active;
	}
	
	
	/** Метод для инициализации и маппинга сущности на DTO в коллекции  */
	public function getActiveClass() : ActiveDTO
	{
		return new ActiveDTO();
	}
	
	/* FILES */
	
	/**
	 * @return ArrayCollection
	 */
	public function getFile() : ArrayCollection
	{
		if($this->file->isEmpty())
		{
			$this->addFile(new FilesCollectionDTO());
		}
		
		return $this->file;
	}
	
	
	public function addFile(FilesCollectionDTO $file) : void
	{
		if(!$this->file->contains($file))
		{
			$this->file->add($file);
		}
	}
	
	
	public function removeFile(FilesCollectionDTO $file) : void
	{
		$this->file->removeElement($file);
	}
	
	
	
	
	/* OFFERS */
	
	//    /**
	//     * @return ArrayCollection
	//     */
	//    public function getOffers() : ArrayCollection
	//    {
	////        if($this->offers->isEmpty())
	////        {
	////            $this->addOffer(new OffersCollectionDTO());
	////        }
	//
	//        return $this->offer;
	//    }
	//
	//    public function addOffer(OffersCollectionDTO $offer) : void
	//    {
	//        if(!$this->offer->contains($offer))
	//        {
	//            $this->offer[] = $offer;
	//        }
	//    }
	//
	//    public function removeOffer(OffersCollectionDTO $offer) : void
	//    {
	//        $this->offers->removeElement($offer);
	//    }
	//
	
	/* PHOTOS */
	
	/**
	 * @return ArrayCollection
	 */
	public function getPhoto() : ArrayCollection
	{
		if($this->photo->isEmpty())
		{
			$this->addPhoto(new PhotoCollectionDTO());
		}
		
		return $this->photo;
	}
	
	
	public function addPhoto(PhotoCollectionDTO $photo) : void
	{
		if(!$this->photo->contains($photo))
		{
			$this->photo->add($photo);
		}
	}
	
	
	public function removePhoto(PhotoCollectionDTO $photo) : void
	{
		$this->photo->removeElement($photo);
	}
	
	
	public function getVideo() : ArrayCollection
	{
		if($this->video->isEmpty())
		{
			$this->addVideo(new VideoCollectionDTO());
		}
		
		return $this->video;
	}
	
	
	public function addVideo(VideoCollectionDTO $video) : void
	{
		if(!$this->video->contains($video))
		{
			$this->video->add($video);
		}
	}
	
	
	public function removeVideo(VideoCollectionDTO $video) : void
	{
		$this->video->removeElement($video);
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
	
	
	
	/* PROPERTIES */
	
	/**
	 * @return ArrayIterator
	 * @throws Exception
	 */
	public function getProperty() : ArrayIterator
	{
		
		$iterator = $this->property->getIterator();
		
		$iterator->uasort(function($first, $second) {
			return $first->getSection() > $second->getSection() ? -1 : 1;
		});
		
		return $iterator;
	}
	
	
	public function addProperty(PropertyCollectionDTO $property) : void
	{
		if(!$this->property->contains($property))
		{
			$this->property->add($property);
		}
	}
	
	
	public function removeProperty(PropertyCollectionDTO $property) : void
	{
		$this->property->removeElement($property);
	}
	
	
	/* SEO  */
	
	public function addSeo(SeoCollectionDTO $seo) : void
	{
		if(!$this->seo->contains($seo))
		{
			$this->seo->add($seo);
		}
	}
	
	
	public function removeSeo(SeoCollectionDTO $seo) : void
	{
		$this->seo->removeElement($seo);
	}
	
	
	public function getSeo() : ArrayCollection
	{
		/* Вычисляем расхождение и добавляем неопределенные локали */
		foreach(Locale::diffLocale($this->seo) as $locale)
		{
			$CategorySeoDTO = new SeoCollectionDTO();
			$CategorySeoDTO->setLocal($locale);
			$this->addSeo($CategorySeoDTO);
		}
		
		return $this->seo;
	}
	
	
	/* TRANS */
	
	public function setTranslate(ArrayCollection $trans) : void
	{
		$this->translate = $trans;
	}
	
	
	public function getTranslate() : ArrayCollection
	{
		/* Вычисляем расхождение и добавляем неопределенные локали */
		foreach(Locale::diffLocale($this->translate) as $locale)
		{
			$ProductTransDTO = new Trans\ProductTransDTO();
			$ProductTransDTO->setLocal($locale);
			$this->addTran($ProductTransDTO);
		}
		
		return $this->translate;
	}
	
	
	public function addTran(Trans\ProductTransDTO $trans) : void
	{
		if(!$this->translate->contains($trans))
		{
			$this->translate->add($trans);
		}
	}
	
}

