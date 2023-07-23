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

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\CurrentQuantity\Variation;

use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductOfferVariationQuantity;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use Doctrine\ORM\EntityManagerInterface;

final class CurrentQuantityByVariation implements CurrentQuantityByVariationInterface
{
	private EntityManagerInterface $entityManager;
	
	
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	
	public function getVariationQuantity(
        ProductEventUid $event,
        ProductOfferUid $offer,
        ProductVariationUid $variation
	
	) : ?ProductOfferVariationQuantity
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$qb->select('quantity');
		
		$qb->from(ProductEntity\Event\ProductEvent::class, 'event');
		
		
		$qb->join(ProductEntity\Product::class,
			'product', 'WITH', 'product.id = event.product'
		);
		
		/** Торговое предложение */
		
		$qb->join(ProductEntity\Offers\ProductOffer::class,
			'offer', 'WITH', 'offer.id = :offer AND offer.event = event.id'
		);
		$qb->setParameter('offer', $offer, ProductOfferUid::TYPE);
		
		$qb->leftJoin(ProductEntity\Offers\ProductOffer::class,
			'current_offer', 'WITH', 'current_offer.const = offer.const AND current_offer.event = product.event'
		);
		
		
		/** Множественный вариант торгового предложения */
		
		$qb->join(ProductEntity\Offers\Variation\ProductVariation::class,
			'variation', 'WITH', 'variation.id = :variation AND variation.offer = offer.id'
		);
		$qb->setParameter('variation', $variation, ProductVariationUid::TYPE);
		
		$qb->leftJoin(ProductEntity\Offers\Variation\ProductVariation::class,
			'current_variation', 'WITH', 'current_variation.const = variation.const AND current_variation.offer = current_offer.id'
		);
		
		
		/** Текущее наличие */
		$qb->leftJoin(ProductOfferVariationQuantity::class,
			'quantity', 'WITH', 'quantity.variation = current_variation.id'
		);
		
		
		$qb->where('event.id = :event');
		$qb->setParameter('event', $event, ProductEventUid::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
}