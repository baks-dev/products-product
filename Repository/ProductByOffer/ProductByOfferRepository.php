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

namespace BaksDev\Products\Product\Repository\ProductByOffer;

use BaksDev\Products\Product\Entity as EntityProduct;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Module\Products\Category\Entity as EntityCategory;

final class ProductByOfferRepository implements ProductByOfferInterface
{
	
	private EntityManagerInterface $entityManager;
	
	private Locale $local;
	
	
	public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
	{
		$this->entityManager = $entityManager;
		$this->local = new Locale($translator->getLocale());
	}
	
	
	/** Получаем продукт с активным событием по постоянному уникальному идентификатор ТП */
	
	public function get(ProductOfferConst $const) : ?EntityProduct\Product
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$qb->select('product');
		
		$qb->from(EntityProduct\Offers\Offer\ProductOfferVariation::class, 'offer');
		$qb->join(EntityProduct\Offers\Offers::class, 'offers', 'WITH', 'offers.id = offer.productOffer');
		$qb->join(EntityProduct\Event\ProductEvent::class, 'event', 'WITH', 'event.id = offers.event');
		$qb->join(EntityProduct\Product::class, 'product', 'WITH', 'product.id = event.product');
		
		$qb->where('offer.const = :const');
		$qb->setParameter('const', $const, ProductOfferConst::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
	
	public function getProductUid(ProductOfferConst $const) : ?ProductUid
	{
		$qb = $this->entityManager->createQueryBuilder();
		
		$qb->select('product.id');
		
		$qb->from(EntityProduct\Offers\Offer\ProductOfferVariation::class, 'offer');
		$qb->join(EntityProduct\Offers\Offers::class, 'offers', 'WITH', 'offers.id = offer.productOffer');
		$qb->join(EntityProduct\Event\ProductEvent::class, 'event', 'WITH', 'event.id = offers.event');
		$qb->join(EntityProduct\Product::class, 'product', 'WITH', 'product.id = event.product');
		
		$qb->where('offer.const = :const');
		$qb->setParameter('const', $const, ProductOfferConst::TYPE);
		
		return $qb->getQuery()->getOneOrNullResult();
	}
	
	
	public function getProductByOffersQuery(ProductOfferUid $offer)
	{
		$qb = $this->entityManager->getConnection()->createQueryBuilder();
		
		$qb->from(EntityProduct\Offers\Offers::TABLE, 'product_offers');
		$qb->where('product_offers.id = :offer');
		$qb->setParameter('offer', $offer, ProductOfferUid::TYPE);
		
		/* АРТИКУЛ и СВОЙСТВО */
		$qb->addSelect('product_offers_offer_article.value as offer_value');
		$qb->addSelect('product_offers_offer_article.article as offer_article');
		
		$qb->join(
			'product_offers',
			EntityProduct\Offers\Offer\ProductOfferVariation::TABLE,
			'product_offers_offer_article',
			'product_offers_offer_article.product_offers_id = product_offers.id AND product_offers_offer_article.article IS NOT NULL'
		);
		
		$qb->addSelect('product_offer_images.name AS image_name');
		$qb->addSelect('product_offer_images.dir AS image_dir');
		$qb->addSelect('product_offer_images.ext AS image_ext');
		$qb->addSelect('product_offer_images.cdn AS image_cdn');
		
		$qb->leftJoin(
			'product_offers_offer_article',
			EntityProduct\Offers\Offer\Image\Image::TABLE,
			'product_offer_images',
			'product_offer_images.offer_id = product_offers_offer_article.id AND product_offer_images.root = true'
		);
		
		$qb->join(
			'product_offers',
			EntityProduct\Trans\Trans::TABLE,
			'product_trans',
			'product_trans.event = product_offers.event AND product_trans.local = :local'
		);
		
		/* Категория продукта */
		$qb->join(
			'product_offers',
			EntityProduct\Category\Category::TABLE,
			'product_event_category',
			'product_event_category.event = product_offers.event AND product_event_category.root = true'
		);
		
		/* Категория */
		
		$qb->join(
			'product_event_category',
			EntityCategory\Category::TABLE,
			'category',
			'category.id = product_event_category.category'
		);
		
		$qb->addSelect('category_trans.name AS category_name');
		
		$qb->join(
			'category',
			EntityCategory\Trans\Trans::TABLE,
			'category_trans',
			'category_trans.event = category.event AND category_trans.local = :local'
		);
		$qb->addSelect('product_trans.name');
		
		$qb->setParameter('local', $this->local, Locale::TYPE);
		
		return $qb->executeQuery()->fetchAssociative();
		
	}
	
}