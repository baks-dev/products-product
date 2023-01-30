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

namespace BaksDev\Products\Product\Entity\Property;

use BaksDev\Products\Category\Type\Section\Field\Id\ProductCategorySectionFieldUid;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Свойства продукта */


#[ORM\Entity()]
#[ORM\Table(name: 'product_property')]
class ProductProperty extends EntityEvent
{
	public const TABLE = 'product_property';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'property')]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id', nullable: false)]
	private ProductEvent $event;
	
	/** Связь на поле в категории */
	#[ORM\Id]
	#[ORM\Column(type: ProductCategorySectionFieldUid::TYPE)]
	private ProductCategorySectionFieldUid $field;
	
	/** Заполненное значение */
	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $value = null;
	
	
	public function __construct(ProductEvent $event) { $this->event = $event; }
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductPropertyInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ProductPropertyInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}
