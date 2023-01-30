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

namespace BaksDev\Products\Product\Repository\ProductByOfferConst;

use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class ProductByOfferConstRepository implements ProductByOfferConstInterface
{
	
	private EntityManagerInterface $entityManager;
	
	
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	
	/** Получаем продукт с активным событием по постоянному уникальному идентификатор ТП */
	
	public function get(ProductOfferConst $const) : ?Entity\Product
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$qb->select('product');
		
		$qb->from(Entity\Offers\Offer\Offer::class, 'offer');
		$qb->join(Entity\Offers\Offers::class, 'offers', 'WITH', 'offers.id = offer.productOffer');
		$qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = offers.event');
		$qb->join(Entity\Product::class, 'product', 'WITH', 'product.id = event.product');
		
		$qb->where('offer.const = :const');
		$qb->setParameter('const', $const, ProductOfferConst::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
	
	public function getProductUid(ProductOfferConst $const) : ?ProductUid
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$select = sprintf('new %s(product.id)', ProductUid::class);
		
		$qb->select($select);
		
		$qb->from(Entity\Offers\Offer\Offer::class, 'offer');
		$qb->join(Entity\Offers\Offers::class, 'offers', 'WITH', 'offers.id = offer.productOffer');
		$qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = offers.event');
		$qb->join(Entity\Product::class, 'product', 'WITH', 'product.id = event.product');
		
		$qb->where('offer.const = :const');
		$qb->setParameter('const', $const, ProductOfferConst::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
}