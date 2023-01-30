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

namespace BaksDev\Products\Product\Entity\Active;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Статусы активности Продукта */


#[ORM\Entity()]
#[ORM\Table(name: 'product_active')]
#[ORM\Index(columns: ['active'])]
class ProductActive extends EntityEvent
{
	public const TABLE = 'product_active';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'active', targetEntity: ProductEvent::class, cascade: ['persist'])]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private ProductEvent $event;
	
	/** Статус активности товара */
	#[ORM\Column(name: 'active', type: Types::BOOLEAN, nullable: false)]
	private bool $active = true;
	
	/** Начало активности */
	#[ORM\Column(name: 'active_from', type: Types::DATETIME_IMMUTABLE, nullable: false)]
	private DateTimeImmutable $activeFrom;
	
	/** Окончание активности */
	#[ORM\Column(name: 'active_to', type: Types::DATETIME_IMMUTABLE, nullable: true)]
	private ?DateTimeImmutable $activeTo = null;
	
	
	public function __construct(ProductEvent $event)
	{
		$this->event = $event;
		$this->activeFrom = new DateTimeImmutable();
	}
	
	
	/**
	 * @throws Exception
	 */
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductActiveInterface)
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
		$format = "H:i";
		
		if($dto->activeFromTime())
		{
			$time = date_parse_from_format($format, $dto->activeFromTime()->format($format)); /* парсим время */
			$getActiveFrom = $dto->getActiveFrom()->setTime($time['hour'], $time['minute']);
			$dto->setActiveFrom($getActiveFrom);
		}
		
		if($dto->getActiveTo() && $dto->activeToTime())
		{
			$time = date_parse_from_format($format, $dto->activeToTime()->format($format)); /* парсим время */
			$getActiveTo = $dto->getActiveTo()->setTime($time['hour'], $time['minute']);
			$dto->setActiveTo($getActiveTo);
		}
		
		if($dto instanceof ProductActiveInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}
