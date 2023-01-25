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

namespace BaksDev\Products\Product\Repository\ProductOfferChoice;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\Module\Wildberries\Products\Product\Entity\WbProductCardVariation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Cache\DefaultCache;
use Doctrine\ORM\EntityManagerInterface;
use BaksDev\Products\Product\Entity;


final class ProductOfferChoiceRepository implements ProductOfferChoiceInterface
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function get(ProductUid $product = null)
    {

        //$product = new ProductUid('709b8681-1a30-4b9f-8d81-4f5d554f104c');

        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->setCacheable(true);
        $qb->setCacheRegion('ProductOfferChoiceRepositoryCache');
        
        
       // $select = sprintf("new %s(offer.id, CONCAT(offer.value, ',', offer.article)   )", ProductOfferUid::class);
        $select = sprintf("new %s(
            offer.id,
            COALESCE(CONCAT(offer.article, ',', offer.value), offer.value),
            offers.id
        )", ProductOfferUid::class);
        
        $qb->select($select);
        
        
        //$qb->select('offers.id as offers_id');
        $qb->addSelect($select);
    
        $qb->from(Entity\Product::class, 'product');
        $qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = product.event');
        $qb->join(Entity\Offers\Offers::class, 'offers', 'WITH', 'offers.event = event.id');
        $qb->join(Entity\Offers\Offer\Offer::class, 'offer', 'WITH', 'offer.productOffer = offers.id');
        
        /* только те, что имеют штрихкод */
        $qb->join(WbProductCardVariation::class, 'variation', 'WITH', 'variation.productOffer = offer.id');
        
        
        $qb->where('product.id = :product');
        $qb->setParameter('product', $product, ProductUid::TYPE);
        
        
        
        return $qb->getQuery()->getResult();
        
    
       // $qb->indexBy('offers', 'id');
        //dump($qb->getQuery()->getResult());
        
        $arr = new ArrayCollection();
    
        foreach($qb->getQuery()->getResult() as $item)
        {
            if($arr->containsKey((string) $item['offers_id']))
            {
                $k = $arr->get((string) $item['offers_id']); //$k->add($item['id']);
                $k->add(end($item));
                
            }
            else
            {

                $arr->set((string) $item['offers_id'], new ArrayCollection());
            }
        }
        
        return $arr;
        
       // dd($arr);
        
        
//

//
//
//        $qb->from(Entity\Offers\Offer\Offer::class, 'offer');
//
//
//        $qb->join(Entity\Product::class, 'product', 'WITH', 'product.id = event.product AND product.id = :product');
//
//
//
//
//        $qb->where('offer.article IS NULL');
//
//        //dd($qb->getQuery()->getResult());
        
        $qb->setMaxResults(100);
        
        
        
        return $qb->getQuery()->getResult();
    }
    
}