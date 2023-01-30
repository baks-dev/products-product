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

namespace BaksDev\Products\Product\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Files\ProductFiles;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
use BaksDev\Products\Product\Entity\Offers\ProductOffers;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Seo\ProductSeo;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Entity\Video\ProductVideo;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* События Product */


#[ORM\Entity]
#[ORM\Table(name: 'product_event')]
#[ORM\Index(columns: ['product'])]
class ProductEvent extends EntityEvent
{
	public const TABLE = 'product_event';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ProductEventUid::TYPE)]
	private ProductEventUid $id;
	
	/** ID Product */
	#[ORM\Column(type: ProductUid::TYPE, nullable: false)]
	private ?ProductUid $product = null;
	
	/** Категории */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductCategory::class, cascade: ['all'])]
	private Collection $category;
	
	/** Статусы активности продукта */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: ProductActive::class, cascade: ['all'])]
	private ProductActive $active;
	
	/** Базовые Стоимость и наличие */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: ProductPrice::class, cascade: ['all'])]
	private ProductPrice $price;
	
	/** Модификатор */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: ProductModify::class, cascade: ['all'])]
	private ProductModify $modify;
	
	/** Перевод */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductTrans::class, cascade: ['all'])]
	private Collection $translate;
	
	/** Фото продукта */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductPhoto::class, cascade: ['all'])]
	private Collection $photo;
	
	/** Файлы (документы) продукта */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductFiles::class, cascade: ['all'])]
	private Collection $file;
	
	/** Видео */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductVideo::class, cascade: ['all'])]
	private Collection $video;
	
	/**  Настройки SEO информации */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductSeo::class, cascade: ['all'])]
	private Collection $seo;
	
	/** Тоговые предложения */
	//#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductOffers::class, cascade: ['all'])]
	// private Collection $offer;
	
	/** Свойства продукта */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: ProductProperty::class, cascade: ['all'])]
	private Collection $property;
	
	
	public function __construct()
	{
		
		$this->id = new ProductEventUid();
		//$this->active = new Active($this);
		//$this->price = new Price($this);
		$this->modify = new ProductModify($this);
	}
	
	
	public function __clone()
	{
		$this->id = new ProductEventUid();
	}
	
	
	/**
	 * @return ProductEventUid
	 */
	public function getId() : ProductEventUid
	{
		return $this->id;
	}
	
	
	/**
	 * @return ProductUid|null
	 */
	public function getProduct() : ?ProductUid
	{
		return $this->product;
	}
	
	
	public function setProduct(ProductUid|Product $product) : void
	{
		$this->product = $product instanceof Product ? $product->getId() : $product;
	}
	
	
	//    public function isModifyActionEquals(ModifyActionEnum $action) : bool
	//    {
	//        return $this->modify->equals($action);
	//    }
	
	/**
	 * @throws Exception
	 */
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductEventInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	/**
	 * @throws Exception
	 */
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ProductEventInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function getNameByLocale(Locale $locale) : ?string
	{
		$name = null;
		
		/** @var ProductTrans $trans */
		foreach($this->translate as $trans)
		{
			if($name = $trans->getNameByLocal($locale))
			{
				break;
			}
		}
		
		return $name;
	}
	
}