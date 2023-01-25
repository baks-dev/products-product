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
        
        $qb->from(EntityProduct\Offers\Offer\Offer::class, 'offer');
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
        
        $qb->from(EntityProduct\Offers\Offer\Offer::class, 'offer');
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
          EntityProduct\Offers\Offer\Offer::TABLE,
          'product_offers_offer_article',
          'product_offers_offer_article.product_offers_id = product_offers.id AND product_offers_offer_article.article IS NOT NULL');
    
    
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
          'product_trans.event_id = product_offers.event_id AND product_trans.local = :local');
        
        
        /* Категория продукта */
        $qb->join(
          'product_offers',
          EntityProduct\Category\Category::TABLE,
          'product_event_category',
          'product_event_category.event_id = product_offers.event_id AND product_event_category.root = true'
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
          'category_trans.event_id = category.event AND category_trans.local = :local');
        $qb->addSelect('product_trans.name');
    
        $qb->setParameter('local', $this->local, Locale::TYPE);
        
        return $qb->executeQuery()->fetchAssociative();

    }
}