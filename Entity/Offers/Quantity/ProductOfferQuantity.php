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

namespace BaksDev\Products\Product\Entity\Offers\Quantity;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Стоимость торгового предложения */


#[ORM\Entity]
#[ORM\Table(name: 'product_offer_quantity')]
class ProductOfferQuantity extends EntityEvent
{
	public const TABLE = 'product_offer_quantity';
	
	/** ID торгового предложения */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'quantity', targetEntity: ProductOffer::class)]
	private ProductOffer $offer;
	
	/** В наличие */
	#[ORM\Column(name: 'quantity', type: Types::INTEGER, nullable: true)]
	private ?int $quantity = null; // 0 - нет в наличие
	
	/** Резерв */
	#[ORM\Column(name: 'reserve', type: Types::INTEGER, nullable: true)]
	private ?int $reserve = null;
	
	
	public function __construct(ProductOffer $offer)
	{
		$this->offer = $offer;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductOfferQuantityInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof ProductOfferQuantityInterface)
		{
			if(empty($dto->getQuantity()) && empty($dto->getReserve()))
			{
				return false;
			}
			
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}
