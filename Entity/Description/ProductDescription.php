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

namespace BaksDev\Products\Product\Entity\Description;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Перевод Product */


#[ORM\Entity()]
#[ORM\Table(name: 'product_description')]
class ProductDescription extends EntityEvent
{
	public const TABLE = 'product_description';
	
	/** Связь на событие */
	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: "description")]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: "id", nullable: false)]
	private ProductEvent $event;

    /** Девайс (pc, mobile, tablet) */
    #[ORM\Id]
    #[ORM\Column(type: Device::TYPE, nullable: false, options: ['default' => 'pc'] )]
    private Device $device;
	
	/** Локаль */
	#[ORM\Id]
	#[ORM\Column(type: Locale::TYPE, length: 2, nullable: false)]
	private Locale $local;


	/** Краткое описание */
	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $preview;
	
	/** Краткое описание */
	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $description;
	
	
	public function __construct(ProductEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }
	
	public function getDto($dto): mixed
	{
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

		if($dto instanceof ProductDescriptionInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}

	public function setEntity($dto): mixed
	{
		if($dto instanceof ProductDescriptionInterface || $dto instanceof self)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}

}
