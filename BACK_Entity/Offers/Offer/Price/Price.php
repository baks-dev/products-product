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

namespace BaksDev\Products\Product\Entity\Offers\Offer\Price;


use BaksDev\Products\Product\Entity\Offers\Offer\Offer;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Currency\Type\CurrencyEnum;
use BaksDev\Reference\Money\Type\Money;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Стоимость варианта торгового предложения */

#[ORM\Entity]
#[ORM\Table(name: 'product_offer_price')]

class Price extends EntityEvent
{
    public const TABLE = 'product_offer_price';
    
    /** ID события */
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'price', targetEntity: Offer::class)]
    private Offer $offer;
    
    /** Стоимость */
    #[ORM\Column(name: 'price', type: Money::TYPE, nullable: true)]
    private ?Money $price;
    
    /** Валюта */
    #[ORM\Column(name: 'currency', type: Currency::TYPE, length: 3, nullable: false)]
    private Currency $currency;
    
    /**
     * @param Offer $offer
     */
    public function __construct(Offer $offer) {
        $this->offer = $offer;
        $this->currency = new Currency();
    }
    

    public function getDto($dto) : mixed
    {
        if($dto instanceof PriceInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    public function setEntity($dto) : mixed
    {
        if($dto instanceof PriceInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
