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

namespace App\Module\Products\Product\Repository\AllProducts;

use App\Module\Materials\Stock\Forms\StockFilter\StockFilterInterface;
use App\Module\Products\Category\Entity as CategoryEntity;
use App\Module\Products\Category\Type\Id\CategoryUid;
use App\Module\Products\Product\Entity;
use App\Module\Products\Product\Forms\ProductFilter\ProductFilterInterface;
use App\Module\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use App\System\Handler\Search\SearchDTO;
use App\System\Helper\Switcher\Switcher;
use App\System\Type\Locale\Locale;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Cache\DefaultQueryCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllProductsQuery implements AllProductsInterface
{
    private Connection $connection;
    
    private Switcher $switcher;
    private Locale $locale;
    
    public function __construct(Connection $connection, TranslatorInterface $translator, Switcher $switcher)
    {
        $this->connection = $connection;
        $this->locale = new Locale($translator->getLocale());
        $this->switcher = $switcher;
    }
    
    public function get(SearchDTO $search, ProductFilterInterface $filter) : QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        
        
        $qb->setParameter('local', $this->locale, Locale::TYPE);
        
        $qb->select('product.id');
        $qb->addSelect('product.event');
        
        $qb->from(Entity\Product::TABLE, 'product');
        
        $qb->join('product', Entity\Event\ProductEvent::TABLE, 'product_event', 'product_event.id = product.event');
        
        $qb->addSelect('product_trans.name');
        $qb->addSelect('product_trans.preview');
        $qb->join(
          'product_event',
          Entity\Trans\Trans::TABLE,
          'product_trans',
          'product_trans.event_id = product_event.id AND product_trans.local = :local');
        
        /* Общее фото */
        $qb->addSelect('product_photo.name AS photo_name');
        $qb->addSelect('product_photo.dir AS photo_dir');
        $qb->addSelect('product_photo.ext AS photo_ext');
        $qb->addSelect('product_photo.cdn AS photo_cdn');
        
        $qb->leftJoin(
          'product_event',
          Entity\Photo\Photo::TABLE,
          'product_photo',
          'product_photo.event_id = product_event.id AND product_photo.root = true'
        );
    
		
		
        
        if($filter->getProfile())
        {
            $qb->andWhere('product_info.profile = :profile');
            $qb->setParameter('profile', $filter->getProfile(), UserProfileUid::TYPE);
        }
        
        $qb->addSelect('product_info.url');
        $qb->addSelect('product_info.article');
        $qb->join(
          'product_event',
          Entity\Info\Info::TABLE,
          'product_info',
          'product_info.product = product.id'
        );
    
        
        $qb->addSelect('product_modify.mod_date');
        $qb->leftJoin(
          'product_event',
          Entity\Modify\Modify::TABLE,
          'product_modify',
          'product_modify.event_id = product_event.id'
        );
        
        $qb->leftJoin(
          'product_event',
          Entity\Offers\Offers::TABLE,
          'product_offers',
          'product_offers.event_id = product_event.id'
        );
        
        //        $qb->where("product_event.id = 'fd52dc37-533f-42cf-b8d2-cd7c8d688d17' ");
        //        dd($qb->fetchAllAssociative());
    
    
    
    
    
        /* Свойство торгового предложения в категории */
    
        $qb->addSelect('product_offers_offer.value as offer');
        $qb->addSelect('product_offers_offer.const as offer_const');
        $qb->leftJoin(
          'product_offers',
          Entity\Offers\Offer\Offer::TABLE,
          'product_offers_offer',
          'product_offers_offer.product_offers_id = product_offers.id AND product_offers_offer.article IS NULL');
    
    
        
        
        
        $qb->addSelect('product_offer.article AS offer_article');
        $qb->addSelect('product_offer.const');
        $qb->leftJoin(
          'product_offers',
          Entity\Offers\Offer\Offer::TABLE,
          'product_offer',
          'product_offer.product_offers_id = product_offers.id AND product_offer.article IS NOT NULL'
        );
        
        
        $qb->addSelect('product_offer_images.name AS image_name');
        $qb->addSelect('product_offer_images.dir AS image_dir');
        $qb->addSelect('product_offer_images.ext AS image_ext');
        $qb->addSelect('product_offer_images.cdn AS image_cdn');
        
        $qb->leftJoin(
          'product_offer',
          Entity\Offers\Offer\Image\Image::TABLE,
          'product_offer_images',
          'product_offer_images.offer_id = product_offer.id AND product_offer_images.root = true'
        );
	
		
        /* Категория */
        $qb->join(
          'product_event',
          Entity\Category\Category::TABLE,
          'product_event_category',
          'product_event_category.event_id = product_event.id AND product_event_category.root = true'
        );
    
		
		
		
        if($filter->getCategory())
        {
            $qb->andWhere('product_event_category.category = :category');
            $qb->setParameter('category', $filter->getCategory(), CategoryUid::TYPE);
        }
        
        
        $qb->join(
          'product_event_category',
          CategoryEntity\Category::TABLE,
          'category',
          'category.id = product_event_category.category'
        );
        
        $qb->addSelect('category_trans.name AS category_name');
        
        $qb->join(
          'category',
          CategoryEntity\Trans\Trans::TABLE,
          'category_trans',
          'category_trans.event_id = category.event AND category_trans.local = :local');
    
    

        if($search->query)
        {
            $search->query = mb_strtolower(trim($search->query));
        
            $searcher = $this->connection->createQueryBuilder();
            
        
            /* name */
            $searcher->orWhere('LOWER(product_trans.name) LIKE :query');
            $searcher->orWhere('LOWER(product_trans.name) LIKE :switcher');
        
//            /* preview */
//            $searcher->orWhere('LOWER(product_trans.preview) LIKE :query');
//            $searcher->orWhere('LOWER(product_trans.preview) LIKE :switcher');
    
    
            /* article */
            $searcher->orWhere('LOWER(product_info.article) LIKE :query');
            $searcher->orWhere('LOWER(product_info.article) LIKE :switcher');
            
            /* offer article */
            $searcher->orWhere('LOWER(product_offer.article) LIKE :query');
            $searcher->orWhere('LOWER(product_offer.article) LIKE :switcher');
            
        
            $qb->andWhere('('.$searcher->getQueryPart('where').')' );
            $qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
            $qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
        
        }
        
        
        
        $qb->orderBy('product_modify.mod_date', 'DESC');
        
        //dd($qb->fetchAllAssociative());
        
        //$qb->select('*');
        
        return $qb;
        
    }
    
    public function count()
    {
        //$cache->delete('AllProductsQueryCountCache');
        
        return (new FilesystemAdapter())->get('AllProductsQueryCountCache', function (ItemInterface $item)
        {
            $item->expiresAfter(60 * 60); // 60 сек = 1 мин
 
            $qb = $this->connection->createQueryBuilder();
            $qb->select('COUNT(*)');
            $qb->from(Entity\Product::TABLE, 'product');
            return $qb->executeQuery()->fetchOne();
        });
        
    }
    
}