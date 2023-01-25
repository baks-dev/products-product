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

namespace BaksDev\Products\Product\Type\Offers\Id;

use App\System\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ProductOfferUid extends Uid
{
	public const TYPE = 'product_offer_id';
	
	private Uuid $value;
	private ?string $name;
	/**
	 * @var null
	 */
	private $offers;
	
	public function __construct(AbstractUid|string|null $value = null, string $option = null, $offers = null)
	{
		parent::__construct($value, $option);
		$this->offers = $offers;
	}
	
//	public function __construct(AbstractUid|string|null $value = null, string $name = null, $offers = null)
//	{
//
//
//		if($value === null)
//		{
//			$value = Uuid::v7();
//		}
//
//		else if(is_string($value))
//		{
//			$value = new Uuid($value);
//		}
//
//		$this->value = $value;
//		$this->name = $name;
//		$this->offers = $offers;
//	}
	
	
//	public function __toString() : string
//	{
//		return $this->value;
//	}
//
//	public function getValue() : AbstractUid
//	{
//		return $this->value;
//	}
	
//	/**
//	 * @return string|null
//	 */
//	public function getName() : ?string
//	{
//		return $this->name;
//	}
	
	/**
	 * @return mixed|null
	 */
	public function getOffers() : mixed
	{
		return $this->offers;
	}
	
//	public function equals(ProductOfferUid $value) : bool
//	{
//
//		//dump($this->value);
//		//dd($value);
//
//
//		return $this->value === $value->value;
//	}
	
}