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
        )",
			ProductOfferUid::class
		);
		
		$qb->select($select);
		
		//$qb->select('offers.id as offers_id');
		$qb->addSelect($select);
		
		$qb->from(Entity\Product::class, 'product');
		$qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = product.event');
		$qb->join(Entity\Offers\Offers::class, 'offers', 'WITH', 'offers.event = event.id');
		$qb->join(Entity\Offers\Offer\ProductOfferVariation::class, 'offer', 'WITH', 'offer.productOffer = offers.id');
		
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