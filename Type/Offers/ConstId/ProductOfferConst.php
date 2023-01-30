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

namespace BaksDev\Products\Product\Type\Offers\ConstId;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class ProductOfferConst extends Uid
{
	public const TYPE = 'product_offer_const';
	
	private ?string $option;
	
	
	public function __construct(AbstractUid|string|null $value = null, string $option = null)
	{
		parent::__construct($value);
		$this->option = $option;
	}
	
	
	/**
	 * @return string|null
	 */
	public function getOption() : ?string
	{
		return $this->option;
	}
	
}