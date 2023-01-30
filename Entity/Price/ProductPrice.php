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

namespace BaksDev\Products\Product\Entity\Price;

use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Measurement\Type\Measurement;
use BaksDev\Reference\Money\Type\Money;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use InvalidArgumentException;

/* Стоимость Продукта */


#[ORM\Entity]
#[ORM\Table(name: 'product_price')]
class ProductPrice extends EntityEvent
{
	public const TABLE = 'product_price';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'price', targetEntity: ProductEvent::class, cascade: ['persist'])]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private ProductEvent $event;
	
	/** Стоимость */
	#[ORM\Column(type: Money::TYPE, nullable: true)]
	private ?Money $price;
	
	/** Валюта */
	#[ORM\Column(type: Currency::TYPE, length: 3, nullable: false)]
	private Currency $currency;
	
	/** Цена по запросу */
	#[ORM\Column(type: Types::BOOLEAN, nullable: false)]
	private bool $request = false;
	
	/** В наличие */
	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $quantity = null; // 0 - нет в наличие
	
	/** Резерв */
	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $reserve = null;
	
	/** Единица измерения: */
	#[ORM\Column(type: Measurement::TYPE, length: 10, nullable: false)]
	private Measurement $measurement;
	
	
	public function __construct(ProductEvent $event)
	{
		$this->event = $event;
		$this->currency = new Currency();
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductPriceInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ProductPriceInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}
