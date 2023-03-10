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

use BaksDev\Products\Category\Type\Offers\Id\ProductCategoryOffersUid;
use BaksDev\Products\Product\Entity\Event\ProductEvent;

use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Торговые предложения */


#[ORM\Entity()]
#[ORM\Table(name: 'product_offer')]
#[ORM\Index(columns: ['const'])]
#[ORM\Index(columns: ['article'])]
class ProductOffer extends EntityEvent
{
	public const TABLE = 'product_offer';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ProductOfferUid::TYPE)]
	private ProductOfferUid $id;
	
	/** ID события */
	#[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'offer')]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private ProductEvent $event;
	
	/** ID торгового предложения категории */
	#[ORM\Column(name: 'category_offer', type: ProductCategoryOffersUid::TYPE, nullable: true)]
	private ProductCategoryOffersUid $categoryOffer;
	
	/** Постоянный уникальный идентификатор ТП */
	#[ORM\Column(type: ProductOfferConst::TYPE)]
	private ProductOfferConst $const;
	
	/** Заполненное значение */
	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $value = null;
	
	/** Артикул */
	#[ORM\Column(type: Types::STRING, nullable: true)]
	private ?string $article = null;
	
	/** Стоимость торгового предложения */
	#[ORM\OneToOne(mappedBy: 'offer', targetEntity: Price\ProductOfferPrice::class, cascade: ['all'])]
	private ?Price\ProductOfferPrice $price = null;
	
	/** Количественный учет */
	#[ORM\OneToOne(mappedBy: 'offer', targetEntity: Quantity\ProductOfferQuantity::class, cascade: ['all'])]
	private ?Quantity\ProductOfferQuantity $quantity = null;
	
	/** Дополнительные фото торгового предложения */
	#[ORM\OneToMany(mappedBy: 'offer', targetEntity: Image\ProductOfferImage::class, cascade: ['all'])]
	private Collection $image;
	
	/** Коллекция вариаций в торговом предложении  */
	#[ORM\OneToMany(mappedBy: 'offer', targetEntity: Variation\ProductOfferVariation::class, cascade: ['all'])]
	private Collection $variation;
	
	
	public function __construct(ProductEvent $event)
	{
		$this->event = $event;
		$this->id = new ProductOfferUid();
		$this->const = new ProductOfferConst();
		//$this->variation = new ArrayCollection();
		
		$this->price = new Price\ProductOfferPrice($this);
		$this->quantity = new Quantity\ProductOfferQuantity($this);
		//$this->images = new ArrayCollection();
	}
	
	
	public function __clone()
	{
		$this->id = new ProductOfferUid();
	}
	
	
	/**
	 * @return ProductOfferUid
	 */
	public function getId() : ProductOfferUid
	{
		return $this->id;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductOffersInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ProductOffersInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	
	
}
