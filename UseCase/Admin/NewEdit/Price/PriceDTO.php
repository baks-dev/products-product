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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Price;

use BaksDev\Products\Product\Entity\Price\ProductPriceInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Measurement\Type\Measurement;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Validator\Constraints as Assert;

final class PriceDTO implements ProductPriceInterface
{
	/** Стоимость */
	#[Assert\NotBlank]
	private ?Money $price;
	
	/** Валюта */
	#[Assert\NotBlank]
	private Currency $currency;
	
	/** Цена по запросу */
	private bool $request = false;
	
	/** В наличие */
	private ?int $quantity = null; // 0 - нет в наличие
	
	/** Резерв */
	private ?int $reserve = null;
	
	/** Единица измерения: */
	private Measurement $measurement;
	
	
	public function __construct()
	{
		$this->currency = new Currency();
	}
	
	
	public function getPrice() : ?Money
	{
		return $this->price;
	}
	
	
	public function setPrice(Money $price) : void
	{
		$this->price = $price;
		//$this->price = $price instanceof Money ? $price : new Money($price);
	}
	
	
	/**
	 * @return Currency
	 */
	public function getCurrency() : Currency
	{
		return $this->currency;
	}
	
	
	/**
	 * @param string $currency
	 */
	public function setCurrency(string $currency) : void
	{
		$this->currency = new Currency($currency);
	}
	
	
	/**
	 * @return bool
	 */
	public function getRequest() : bool
	{
		return $this->request;
	}
	
	
	/**
	 * @param bool $request
	 */
	public function setRequest(bool $request) : void
	{
		$this->request = $request;
	}
	
	
	public function getQuantity() : ?int
	{
		return $this->quantity;
	}
	
	
	public function setQuantity(?int $quantity) : void
	{
		$this->quantity = $quantity;
	}
	
	
	public function getReserve() : ?int
	{
		return $this->reserve;
	}
	
	
	public function setReserve(?int $reserve) : void
	{
		$this->reserve = $reserve;
	}
	
	
	/**
	 * @return Measurement
	 */
	public function getMeasurement() : Measurement
	{
		return $this->measurement;
	}
	
	
	/**
	 * @param Measurement $measurement
	 */
	public function setMeasurement(Measurement $measurement) : void
	{
		$this->measurement = $measurement;
	}
	
}