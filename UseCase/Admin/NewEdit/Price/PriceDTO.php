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
    #[Assert\NotBlank]
	private ?int $quantity = 0; // 0 - нет в наличие

	/** Резерв */
	#[Assert\NotBlank]
	private ?int $reserve = 0;

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