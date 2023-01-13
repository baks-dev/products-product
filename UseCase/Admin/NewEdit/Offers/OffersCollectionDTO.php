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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Offers;

use App\Module\Products\Product\Entity\Offers\OffersInterface;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\OfferDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class OffersCollectionDTO implements OffersInterface
{
 
    /** Коллекция орговых предложений */
    private ArrayCollection $offer;
    
    public function __construct() { $this->offer = new ArrayCollection(); }
    
    
    /**
     * @return ArrayCollection
     */
    public function getOffer() : ArrayCollection
    {
        return $this->offer;
    }
    
    /**
     * @param OfferDTO $offer
     */
    public function addOffer(OfferDTO $offer) : void
    {
        if(!$this->offer->contains($offer))
        {
            $this->offer[] = $offer;
        }
    }
    
    /**
     * @param OfferDTO $offer
     * @return void
     */
    public function removeOffer(OfferDTO $offer) : void
    {
        $this->offer->removeElement($offer);
    }
    
    /** Метод для инициализации и маппинга сущности на DTO в коллекции  */
    public function getOfferClass() : OfferDTO
    {
        return new OfferDTO();
    }
    
    
}

