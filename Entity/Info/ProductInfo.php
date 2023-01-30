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

namespace BaksDev\Products\Product\Entity\Info;

use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Неизменяемые данные Продукта */


#[ORM\Entity]
#[ORM\Table(name: 'product_info')]
class ProductInfo extends EntityEvent
{
	public const TABLE = 'product_info';
	
	/** ID Product */
	#[ORM\Id]
	#[ORM\Column(type: ProductUid::TYPE)]
	private ProductUid $product;
	
	/** Семантическая ссылка на товар */
	#[ORM\Column(type: Types::STRING, unique: true)]
	private string $url;
	
	/** Артикул товара */
	#[ORM\Column(type: Types::STRING)]
	private string $article;
	
	/** Профиль пользователя, которому принадлежит товар */
	#[ORM\Column(type: UserProfileUid::TYPE, nullable: true)]
	private UserProfileUid $profile;
	
	
	public function __construct(Product|ProductUid $product)
	{
		$this->product = $product instanceof Product ? $product->getId() : $product;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductInfoInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ProductInfoInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}
