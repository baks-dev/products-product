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

namespace App\Module\Products\Product\Entity\Offers;



use App\Module\Products\Product\Entity\Event\ProductEvent;

use App\Module\Products\Product\Type\File\FileUid;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\System\Entity\EntityEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Торговые предложения */

#[ORM\Entity()]
#[ORM\Table(name: 'product_offers')]

class Offers extends EntityEvent
{
    public const TABLE = 'product_offers';
    
    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: ProductOfferUid::TYPE)]
    protected ProductOfferUid $id;
    
    /** ID события */
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, cascade: ['persist'], inversedBy: 'offers')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    protected ProductEvent $event;
    
    /** Коллекция вариаций в торговом предложении  */
    #[ORM\OneToMany(mappedBy: 'productOffer', targetEntity: Offer\Offer::class, cascade: ['all'])]
    protected Collection $offer;
    
    public function __construct(ProductEvent $event) {
        $this->event = $event;
        $this->id = new ProductOfferUid();
        $this->offer = new ArrayCollection();
    }
    
    public function __clone()
    {
        $this->id = new ProductOfferUid();
    }
    
    /**
     * @return ProductOfferUid
     */
    public function getId() : ProductOfferUid
    {
        return $this->id;
    }
    
    

    public function getDto($dto) : mixed
    {
        if($dto instanceof OffersInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    

    public function setEntity($dto) : mixed
    {
        if($dto instanceof OffersInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    
    
    
    
//    /**
//     * @return ProductOfferUid
//     */
//    public function getId() : ProductOfferUid
//    {
//        return $this->id;
//    }
//
//    /**
//     * @return Event|null
//     */
//    public function getEvent() : ?Event
//    {
//        return $this->event;
//    }
//
//    /**
//     * @param Event|null $event
//     */
//    public function setEvent(?Event $event) : void
//    {
//        $this->event = $event;
//    }
//
//
//    /**
//     * @return Collection
//     */
//    public function getOffer() : Collection
//    {
//        return $this->offer;
//    }
//
//    /**
//     * @param Offer $offer
//     * @return Offers
//     */
//    public function addOffer(Offer $offer) : self
//    {
//
//        if(!$this->offer->contains($offer))
//        {
//            $this->offer[] = $offer;
//            $offer->setProductOffer($this);
//        }
//
//        return $this;
//    }
//
//    /**
//     * @param Offer $offer
//     * @return $this
//     */
//    public function removeOffer(Offer $offer): self
//    {
//        if($this->offer->removeElement($offer))
//        {
//            if($offer->getProductOffer() === $this)
//            {
//                $offer->setProductOffer(null);
//            }
//        }
//
//        return $this;
//    }

}
