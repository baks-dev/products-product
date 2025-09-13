<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Products\Product\UseCase\Admin\Cost\Offer;

use BaksDev\Products\Product\Entity\Offers\Cost\ProductOfferCostInterface;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductOfferCost */
final class UpdateOfferCostDTO implements ProductOfferCostInterface
{
    #[Assert\Type(ProductOfferUid::class)]
    #[Assert\NotBlank]
    private ?ProductOfferUid $offer;

    /** Себестоимость */
    #[Assert\Type(Money::class)]
    #[Assert\NotBlank]
    private ?Money $cost;

    /** Валюта */
    #[Assert\Type(Currency::class)]
    private ?Currency $currency;

    public function __construct(
        ProductOfferUid $offer,
        Money $cost,
        Currency $currency,
    )
    {
        $this->offer = $offer;
        $this->cost = $cost;
        $this->currency = $currency;
    }

    public function getProductOffer(): ?ProductOfferUid
    {
        return $this->offer;
    }

    public function getCost(): ?Money
    {
        return $this->cost;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }
}