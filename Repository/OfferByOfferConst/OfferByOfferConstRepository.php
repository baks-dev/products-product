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

namespace BaksDev\Products\Product\Repository\OfferByOfferConst;

use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class OfferByOfferConstRepository implements OfferByOfferConstInterface
{
	
	private EntityManagerInterface $entityManager;
	
	
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	
	/** Получаем продукт с активным событием по постоянному уникальному идентификатор ТП */
	
	public function get(ProductUid $product, ProductOfferConst $const) : ?Entity\Offers\Offer\ProductOfferVariation
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$qb->select('offer');
		
		$qb->from(Entity\Product::class, 'product');
		$qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = product.event');
		$qb->join(Entity\Offers\ProductOffers::class, 'offers', 'WITH', 'offers.event = event.id');
		$qb->join(Entity\Offers\Offer\ProductOfferVariation::class,
			'offer',
			'WITH',
			'offer.productOffer = offers.id AND offer.const = :const'
		);
		
		$qb->where('product.id = :product');
		
		$qb->setParameter('product', $product, ProductUid::TYPE);
		$qb->setParameter('const', $const, ProductOfferConst::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
	
	/** Получаем продукт с активным событием по постоянному уникальному идентификатор ТП */
	
	public function getProductOfferUid(ProductUid $product, ProductOfferConst $const) : ?ProductOfferUid
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$select = sprintf('new %s(offer.id)', ProductOfferUid::class);
		
		$qb->select($select);
		
		$qb->from(Entity\Product::class, 'product');
		$qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = product.event');
		$qb->join(Entity\Offers\Offers::class, 'offers', 'WITH', 'offers.event = event.id');
		$qb->join(Entity\Offers\Offer\ProductOfferVariation::class,
			'offer',
			'WITH',
			'offer.productOffer = offers.id AND offer.const = :const'
		);
		
		$qb->where('product.id = :product');
		
		$qb->setParameter('product', $product, ProductUid::TYPE);
		$qb->setParameter('const', $const, ProductOfferConst::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
}