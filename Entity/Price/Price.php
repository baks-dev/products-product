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

namespace BaksDev\Products\Product\Entity\Price;

use BaksDev\Products\Product\Entity\Event\ProductEvent;
use App\System\Entity\EntityEvent;
use App\System\Type\Currency\Currency;
use App\System\Type\Currency\CurrencyEnum;
use App\System\Type\Measurement\Measurement;
use App\System\Type\Money\Money;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Стоимость Продукта */

#[ORM\Entity()]
#[ORM\Table(name: 'product_price')]

class Price extends EntityEvent
{
    public const TABLE = 'product_price';
    
    /** ID события */
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'price', targetEntity: ProductEvent::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    protected ProductEvent $event;
    
    /** Стоимость */
    #[ORM\Column(name: 'price', type: Money::TYPE, nullable: true)]
    protected ?Money $price;
    
    /** Валюта */
    #[ORM\Column(name: 'currency', type: Currency::TYPE, length: 3, nullable: false)]
    protected Currency $currency;
    
    /** Цена по запросу */
    #[ORM\Column(name: 'request', type: Types::BOOLEAN, nullable: false)]
    protected bool $request = false;
    
    /** В наличие */
    #[ORM\Column(name: 'quantity', type: Types::INTEGER, nullable: true)]
    protected ?int $quantity = null; // 0 - нет в наличие
    
    /** Резерв */
    #[ORM\Column(name: 'reserve', type: Types::INTEGER, nullable: true)]
    protected ?int $reserve = null;
    
    /** Единица измерения: */
    #[ORM\Column(name: 'measurement', type: Measurement::TYPE, length: 10, nullable: false)]
    protected Measurement $measurement;
    

    public function __construct(ProductEvent $event)
    {
        $this->event = $event;
        $this->currency = new Currency();
    }
    
    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof PriceInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    /**
     * @throws Exception
     */
    public function setEntity($dto) : mixed
    {
        if($dto instanceof PriceInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
//
//    /**
//     * @return Event
//     */
//    public function getEvent() : Event
//    {
//        return $this->event;
//    }
//
//    /**
//     * @param Event $event
//     */
//    public function setEvent(Event $event) : void
//    {
//        $this->event = $event;
//    }
//
//    /**
//     * @return Money
//     */
//    public function getPrice() : Money
//    {
//        return $this->price;
//    }
//
//
//    /**
//     * @param Money $price
//     */
//    public function setPrice(Money|int|float $price) : void
//    {
//        $this->price = $price instanceof Money ? $price : new Money($price);
//    }
//
//    /**
//     * @return string
//     */
//    public function getCurrency() : string
//    {
//        return $this->currency;
//    }
//
//    /**
//     * @param string $currency
//     */
//    public function setCurrency(string|CurrencyEnum $currency) : void
//    {
//        if($currency instanceof CurrencyEnum)
//        {
//            $currency =  $currency->value;
//        }
//
//        $this->currency = $currency;
//    }
//
//
//
//    /**
//     * @return bool
//     */
//    public function isRequest() : bool
//    {
//        return $this->request;
//    }
//
//    /**
//     * @param bool $request
//     */
//    public function setRequest(bool $request) : void
//    {
//        $this->request = $request;
//    }
//
//    /**
//     * @return int
//     */
//    public function getQuantity() : int
//    {
//        return $this->quantity;
//    }
//
//    /**
//     * @param int $quantity
//     */
//    public function setQuantity(int $quantity) : void
//    {
//        $this->quantity = $quantity;
//    }
//
//    /**
//     * @return int
//     */
//    public function getReserve() : int
//    {
//        return $this->reserve;
//    }
//
//    /**
//     * @param int $reserve
//     */
//    public function setReserve(int $reserve) : void
//    {
//        $this->reserve = $reserve;
//    }
//
//    /**
//     * @return Measurement
//     */
//    public function getMeasurement() : Measurement
//    {
//        return $this->measurement;
//    }
//
//    /**
//     * @param Measurement $measurement
//     */
//    public function setMeasurement(Measurement|MeasurementEnum $measurement) : void
//    {
//        if($measurement instanceof MeasurementEnum)
//        {
//            $measurement = new Measurement($measurement);
//        }
//
//        $this->measurement = $measurement;
//    }

}
