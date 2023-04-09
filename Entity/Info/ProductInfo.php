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
	#[ORM\Column(type: Types::STRING, nullable: true)]
	private ?string $article = null;
	
	/** Профиль пользователя, которому принадлежит товар */
	#[ORM\Column(type: UserProfileUid::TYPE, nullable: true)]
	private UserProfileUid $profile;
	
	
	public function __construct(Product|ProductUid $product)
	{
		$this->product = $product instanceof Product ? $product->getId() : $product;
	}
	
	
	

	public function getProduct() : ProductUid
	{
		return $this->product;
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
