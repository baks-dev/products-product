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

namespace BaksDev\Products\Product\Repository\AllProducts;

use BaksDev\Core\Services\Paginator\PaginatorInterface;

use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Product\Entity;
use BaksDev\Core\Services\Switcher\Switcher;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;

use BaksDev\Products\Product\Forms\ProductFilter\ProductFilterInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

use BaksDev\Core\Form\Search\SearchDTO;

use BaksDev\Core\Type\Locale\Locale;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Cache\DefaultQueryCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllProducts implements AllProductsInterface
{
	private Connection $connection;
	
	private Switcher $switcher;
	
	private PaginatorInterface $paginator;
	
	private TranslatorInterface $translator;
	
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
		Switcher $switcher,
		PaginatorInterface $paginator,
	)
	{
		$this->connection = $connection;
		$this->switcher = $switcher;
		$this->paginator = $paginator;
		$this->translator = $translator;
	}
	
	
	public function get(SearchDTO $search, ProductFilterInterface $filter) : PaginatorInterface
	{
		
		$qb = $this->connection->createQueryBuilder();
		
		/** ЛОКАЛЬ */
		$locale = new Locale($this->translator->getLocale());
		$qb->setParameter('local', $locale, Locale::TYPE);
		
		
		$qb->select('product.id');
		$qb->addSelect('product.event');
		
		$qb->from(Entity\Product::TABLE, 'product');
		
		$qb->join('product', Entity\Event\ProductEvent::TABLE, 'product_event', 'product_event.id = product.event');
		
		$qb->addSelect('product_trans.name AS product_name');
		$qb->addSelect('product_trans.preview AS product_preview');
		$qb->leftJoin(
			'product_event',
			Entity\Trans\ProductTrans::TABLE,
			'product_trans',
			'product_trans.event = product_event.id AND product_trans.local = :local'
		);
		
		if($filter->getProfile())
		{
			$qb->andWhere('product_info.profile = :profile');
			$qb->setParameter('profile', $filter->getProfile(), UserProfileUid::TYPE);
		}
		
		/* ProductInfo */
		
		$qb->addSelect('product_info.url');
		
		$qb->leftJoin(
			'product_event',
			Entity\Info\ProductInfo::TABLE,
			'product_info',
			'product_info.product = product.id'
		);
		
		/* ProductModify */
		
//		$qb->addSelect('product_modify.mod_date');
//		$qb->leftJoin(
//			'product_event',
//			Entity\Modify\ProductModify::TABLE,
//			'product_modify',
//			'product_modify.event = product_event.id'
//		);
		
		
		/** Торговое предложение */
		
		$qb->addSelect('product_offer.value as product_offer_value');
		$qb->addSelect('product_offer.postfix as product_offer_postfix');
		$qb->leftJoin(
			'product_event',
			Entity\Offers\ProductOffer::TABLE,
			'product_offer',
			'product_offer.event = product_event.id'
		);
		
		
		/* Цена торгового предожения */
		$qb->leftJoin(
			'product_offer',
			Entity\Offers\Price\ProductOfferPrice::TABLE,
			'product_offer_price',
			'product_offer_price.offer = product_offer.id'
		);
		
		/* Тип торгового предложения */
		$qb->addSelect('category_offer.reference as product_offer_reference');
		$qb->leftJoin(
			'product_offer',
			CategoryEntity\Offers\ProductCategoryOffers::TABLE,
			'category_offer',
			'category_offer.id = product_offer.category_offer'
		);
		
		
		
		
		
		/** Множественные варианты торгового предложения */
		
		$qb->addSelect('product_offer_variation.value as product_variation_value');
		$qb->addSelect('product_offer_variation.postfix as product_variation_postfix');

		$qb->leftJoin(
			'product_offer',
			Entity\Offers\Variation\ProductOfferVariation::TABLE,
			'product_offer_variation',
			'product_offer_variation.offer = product_offer.id'
		);
		
		/* Цена множественного врианта */
		$qb->leftJoin(
			'category_offer_variation',
			Entity\Offers\Variation\Price\ProductOfferVariationPrice::TABLE,
			'product_variation_price',
			'product_variation_price.variation = product_offer_variation.id'
		)
		;
		
		/* Тип множественного враианта торгового предложения */
		$qb->addSelect('category_offer_variation.reference as product_variation_reference');
		$qb->leftJoin(
			'product_offer_variation',
			CategoryEntity\Offers\Variation\ProductCategoryOffersVariation::TABLE,
			'category_offer_variation',
			'category_offer_variation.id = product_offer_variation.category_variation'
		);
		
		
		
		
		/** Модификация множественного варианта */
		$qb->addSelect('product_offer_modification.value as product_modification_value');
		$qb->addSelect('product_offer_modification.postfix as product_modification_postfix');

		$qb->leftJoin(
			'product_offer_variation',
			Entity\Offers\Variation\Modification\ProductOfferVariationModification::TABLE,
			'product_offer_modification',
			'product_offer_modification.variation = product_offer_variation.id '
		)
		;
		
		
		/** Получаем тип модификации множественного варианта */
		$qb->addSelect('category_offer_modification.reference as product_modification_reference');
		$qb->leftJoin(
			'product_offer_modification',
			CategoryEntity\Offers\Variation\Modification\ProductCategoryOffersVariationModification::TABLE,
			'category_offer_modification',
			'category_offer_modification.id = product_offer_modification.category_modification'
		);
		
		
		//$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");
		
		
		/** Артикул продукта */
		
		/** Артикул продукта */
		
		$qb->addSelect("
					CASE
					   WHEN product_offer_modification.article IS NOT NULL THEN product_offer_modification.article
					   WHEN product_offer_variation.article IS NOT NULL THEN product_offer_variation.article
					   WHEN product_offer.article IS NOT NULL THEN product_offer.article
					   WHEN product_info.article IS NOT NULL THEN product_info.article
					   ELSE NULL
					END AS product_article
				"
		);
		
		
		/** Фото продукта */
		
		$qb->leftJoin(
			'product_event',
			Entity\Photo\ProductPhoto::TABLE,
			'product_photo',
			'product_photo.event = product_event.id AND product_photo.root = true'
		);
		
		$qb->leftJoin(
			'product_offer',
			Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE,
			'product_offer_variation_image',
			'product_offer_variation_image.variation = product_offer_variation.id AND product_offer_variation_image.root = true'
		);
		
		$qb->leftJoin(
			'product_offer',
			Entity\Offers\Image\ProductOfferImage::TABLE,
			'product_offer_images',
			'product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
		);
		
		$qb->addSelect("
			CASE
			   WHEN product_offer_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' , '/', product_offer_variation_image.dir, '/', product_offer_variation_image.name, '.')
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".Entity\Offers\Image\ProductOfferImage::TABLE."' , '/', product_offer_images.dir, '/', product_offer_images.name, '.')
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".Entity\Photo\ProductPhoto::TABLE."' , '/', product_photo.dir, '/', product_photo.name, '.')
			   ELSE NULL
			END AS product_image
		"
		);
		
		/** Флаг загрузки файла CDN */
		$qb->addSelect("
			CASE
			   WHEN product_offer_variation_image.name IS NOT NULL THEN
					product_offer_variation_image.ext
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.ext
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.ext
			   ELSE NULL
			END AS product_image_ext
		");
		
		/** Флаг загрузки файла CDN */
		$qb->addSelect("
			CASE
			   WHEN product_offer_variation_image.name IS NOT NULL THEN
					product_offer_variation_image.cdn
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.cdn
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.cdn
			   ELSE NULL
			END AS product_image_cdn
		");
		
		
		/* Категория */
		$qb->join(
			'product_event',
			Entity\Category\ProductCategory::TABLE,
			'product_event_category',
			'product_event_category.event = product_event.id AND product_event_category.root = true'
		);
		
		if($filter->getCategory())
		{
			$qb->andWhere('product_event_category.category = :category');
			$qb->setParameter('category', $filter->getCategory(), ProductCategoryUid::TYPE);
		}
		
		$qb->join(
			'product_event_category',
			CategoryEntity\ProductCategory::TABLE,
			'category',
			'category.id = product_event_category.category'
		);
		
		$qb->addSelect('category_trans.name AS category_name');
		
		$qb->leftJoin(
			'category',
			CategoryEntity\Trans\ProductCategoryTrans::TABLE,
			'category_trans',
			'category_trans.event = category.event AND category_trans.local = :local'
		);
		
		
		
		if($search->query)
		{
			$search->query = mb_strtolower(trim($search->query));
			
			$searcher = $this->connection->createQueryBuilder();
			
			/* name */
			$searcher->orWhere('LOWER(product_trans.name) LIKE :query');
			$searcher->orWhere('LOWER(product_trans.name) LIKE :switcher');
			
			/* preview */
			$searcher->orWhere('LOWER(product_trans.preview) LIKE :query');
			$searcher->orWhere('LOWER(product_trans.preview) LIKE :switcher');

			/* article */
			$searcher->orWhere('LOWER(product_info.article) LIKE :query');
			$searcher->orWhere('LOWER(product_info.article) LIKE :switcher');

			/* offer article */
			$searcher->orWhere('LOWER(product_offer.article) LIKE :query');
			$searcher->orWhere('LOWER(product_offer.article) LIKE :switcher');

			$searcher->orWhere('LOWER(product_offer_modification.article) LIKE :query');
			$searcher->orWhere('LOWER(product_offer_modification.article) LIKE :switcher');

			$searcher->orWhere('LOWER(product_offer_variation.article) LIKE :query');
			$searcher->orWhere('LOWER(product_offer_variation.article) LIKE :switcher');
			
			
			$qb->andWhere('('.$searcher->getQueryPart('where').')');
			$qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
			$qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
			
			dump($this->switcher->toEng($search->query));
			
		}
		
		$qb->orderBy('product.event', 'DESC');
		
		//dd($this->connection->prepare('EXPLAIN (ANALYZE)  '.$qb->getSQL())->executeQuery($qb->getParameters())->fetchAllAssociativeIndexed());
		
		//dd(current($qb->fetchAllAssociative()));
		
		//$qb->select('*');
		
		return $this->paginator->fetchAllAssociative($qb);
		
	}
	
	
	public function count()
	{
		//$cache->delete('AllProductsQueryCountCache');
		
		return (new FilesystemAdapter())->get('AllProductsQueryCountCache', function(ItemInterface $item) {
			$item->expiresAfter(60 * 60); // 60 сек = 1 мин
			
			$qb = $this->connection->createQueryBuilder();
			$qb->select('COUNT(*)');
			$qb->from(Entity\Product::TABLE, 'product');
			
			return $qb->executeQuery()->fetchOne();
		});
		
	}
	
}