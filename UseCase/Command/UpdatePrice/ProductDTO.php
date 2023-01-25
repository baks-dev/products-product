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

namespace BaksDev\Products\Product\UseCase\Command\UpdatePrice;


use BaksDev\Products\Product\Entity\Event\ProductEventInterface;
use BaksDev\Products\Product\Entity\Offers\OffersInterface;
use BaksDev\Products\Product\Entity\Price\PriceInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Command\UpdatePrice\Offers\OffersCollectionDTO;
use BaksDev\Products\Product\UseCase\Command\UpdatePrice\Price\PriceDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductDTO implements ProductEventInterface
{
    /**
     * Идентификатор
     * @var ProductEventUid
     */
    #[Assert\Uuid]
    #[Assert\NotBlank]
    private readonly ProductEventUid $id;
    
    #[Assert\Valid]
    private PriceDTO $price;
    
    #[Assert\Valid]
    private ArrayCollection $offers;
    
    
    public function __construct() {
        $this->price = new PriceDTO();
        $this->offers = new ArrayCollection();
    }
    
    public function getEvent() : ProductEventUid
    {
        return $this->id;
    }
    
    public function setId(ProductEventUid $id) : void
    {
        $this->id = $id;
    }
    
    
    
    /* OFFERS */
    
    /**
     * @return ArrayCollection
     */
    public function getOffers() : ArrayCollection
    {
        return $this->offers;
    }
    
    public function addOffer(OffersCollectionDTO $offer) : void
    {
        $this->offers->add($offer);
        
//        if(!$this->offers->contains($offer))
//        {
//            $this->offers[] = $offer;
//        }
    }
    
    public function getOfferClass() : OffersInterface
    {
        return new OffersCollectionDTO();
    }
    
    
    
    /* PRICE */
    
    /**
     * @return PriceDTO
     */
    public function getPrice() : PriceDTO
    {
        return $this->price;
    }
    
    /**
     * @param PriceDTO $price
     */
    public function setPrice(PriceDTO $price) : void
    {
        $this->price = $price;
    }
    
    /**
     * @return PriceDTO
     */
    public function getPriceClass() : PriceInterface
    {
        return new PriceDTO();
    }

}

