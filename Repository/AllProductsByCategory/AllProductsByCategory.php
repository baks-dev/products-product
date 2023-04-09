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

namespace BaksDev\Products\Product\Repository\AllProductsByCategory;

use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Type\Event\ProductCategoryEventUid;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;

use BaksDev\Products\Category\Type\Section\Field\Id\ProductCategorySectionFieldUid;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Category\Entity as CategoryEntity;

use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use Doctrine\DBAL\Connection;
use Symfony\Component\Form\Form;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllProductsByCategory implements AllProductsByCategoryInterface
{
	
	private Connection $connection;
	
	private PaginatorInterface $paginator;
	
	private TranslatorInterface $translator;
	
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
		PaginatorInterface $paginator,
	)
	{
		$this->connection = $connection;
		$this->paginator = $paginator;
		$this->translator = $translator;
	}
	
	
	public function fetchAllProductByCategoryAssociative(
		ProductCategoryUid $category,
		ProductCategoryFilterDTO $filter,
		?array $property,
		string $expr = 'AND',
	) : PaginatorInterface
	{
		$qb = $this->connection->createQueryBuilder();
		
		
		
		/** ЛОКАЛЬ */
		$locale = new Locale($this->translator->getLocale());
		$qb->setParameter('local', $locale, Locale::TYPE);
		
		
		

		
		$qb->from(CategoryEntity\ProductCategory::TABLE, 'category');
		$qb->where('category.id = :category');
		$qb->setParameter('category', $category, ProductCategoryUid::TYPE);
		
		$qb->join(
			'category',
			CategoryEntity\Event\ProductCategoryEvent::TABLE,
			'category_event',
			'category_event.id = category.event OR category_event.parent = category.id'
		);
		
		
		$qb->addSelect('category_trans.name AS category_name')
			->addGroupBy('category_trans.name')
		;
		
		$qb->leftJoin(
			'category_event',
			CategoryEntity\Trans\ProductCategoryTrans::TABLE,
			'category_trans',
			'category_trans.event = category_event.id AND category_trans.local = :local'
		);
		
		$qb->leftJoin(
			'category',
			CategoryEntity\Section\ProductCategorySection::TABLE,
			'category_section',
			'category_section.event = category.event'
		);
		
		
		
		/** Свойства, учавствующие в карточке */
		
		
		$qb->leftJoin(
			'category_section',
			CategoryEntity\Section\Field\ProductCategorySectionField::TABLE,
			'category_section_field',
			'category_section_field.section = category_section.id AND (category_section_field.card = TRUE OR category_section_field.name = TRUE )'
		);

		$qb->leftJoin(
			'category_section_field',
			CategoryEntity\Section\Field\Trans\ProductCategorySectionFieldTrans::TABLE,
			'category_section_field_trans',
			'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local'
		);
		
		
		
		
		
		

		$qb->select('product.id')
			->groupBy('product.id')
		;
		$qb->addSelect('product.event')
			->addGroupBy('product.event')
		;
		
		
		
		
		
		/** Категория продукта */
		//$qb->from(ProductEntity\Category\ProductCategory::TABLE, 'product_category');
		$qb->leftJoin(
			'category',
			ProductEntity\Category\ProductCategory::TABLE,
			'product_category',
			'product_category.category = category_event.category'
		);
		
		
		
		$qb->join('product_category',
			ProductEntity\Product::TABLE,
			'product',
			'product.event = product_category.event'
		);
		
		
		
		$qb->join('product',
			ProductEntity\Event\ProductEvent::TABLE,
			'product_event',
			'product_event.id = product.event'
		);
		
		
		
		/** ФИЛЬТР СВОЙСТВ */
		if($property)
		{
			if($expr === 'AND')
			{
				foreach($property as $type => $item)
				{
					if($item === true)
					{
						$item = 'true';
					}
					
					$prepareKey = uniqid('key_', false);
					$prepareValue = uniqid('val_', false);
					$aliase = uniqid('aliase', false);
					
					$ProductCategorySectionFieldUid = new ProductCategorySectionFieldUid($type);
					$ProductPropertyJoin = $aliase.'.field = :'.$prepareKey.' AND '.$aliase.'.value = :'.$prepareValue;
					
					$qb->setParameter($prepareKey,
						$ProductCategorySectionFieldUid,
						ProductCategorySectionFieldUid::TYPE
					);
					$qb->setParameter($prepareValue, $item);
					
					$qb->join(
						'product',
						ProductEntity\Property\ProductProperty::TABLE,
						$aliase,
						$aliase.'.event = product.event '.$expr.' '.$ProductPropertyJoin
					);
				}
			}
			else
			{
				
				foreach($property as $type => $item)
				{
					if($item === true)
					{
						$item = 'true';
					}
					
					$prepareKey = uniqid('', false);
					$prepareValue = uniqid('', false);
					
					$ProductCategorySectionFieldUid = new ProductCategorySectionFieldUid($type);
					$ProductPropertyJoin[] = 'product_property_filter.field = :'.$prepareKey.' AND product_property_filter.value = :'.$prepareValue;
					
					$qb->setParameter($prepareKey,
						$ProductCategorySectionFieldUid,
						ProductCategorySectionFieldUid::TYPE
					);
					$qb->setParameter($prepareValue, $item);
					
				}
				
				$qb->join(
					'product',
					ProductEntity\Property\ProductProperty::TABLE,
					'product_property_filter',
					'product_property_filter.event = product.event AND '.implode(' '.$expr.' ', $ProductPropertyJoin)
				
				);
			}
		}
		
		$qb->addSelect('product_trans.name AS product_name')->addGroupBy('product_trans.name'); // Название продукта
		$qb->addSelect('product_trans.preview AS product_preview')->addGroupBy('product_trans.preview'
		); // Превью описание
		
		$qb->leftJoin(
			'product_event',
			ProductEntity\Trans\ProductTrans::TABLE,
			'product_trans',
			'product_trans.event = product_event.id AND product_trans.local = :local'
		);
		
		/** Цена товара */
		$qb->leftJoin(
			'product_event',
			ProductEntity\Price\ProductPrice::TABLE,
			'product_price',
			'product_price.event = product_event.id'
		)
			->addGroupBy('product_price.price')
			->addGroupBy('product_price.currency')
		;
		
		/* ProductInfo */
		
		$qb->addSelect('product_info.url')
			->addGroupBy('product_info.url')
			->addGroupBy('product_info.article'
		); // Артикул
		
		$qb->leftJoin(
			'product_event',
			ProductEntity\Info\ProductInfo::TABLE,
			'product_info',
			'product_info.product = product.id'
		);
		
		/** Торговое предложение */
		
		$method = 'leftJoin';
		if($filter->getOffer())
		{
			$method = 'join';
			$qb->setParameter('offer', $filter->getOffer());
		}
		
		//$qb->addSelect('product_offer.value as product_offer_value')->addGroupBy('product_offer.value'); // значение торгового предложения
		
		$qb->{$method}(
			'product_event',
			ProductEntity\Offers\ProductOffer::TABLE,
			'product_offer',
			'product_offer.event = product_event.id '.($filter->getOffer(
			) ? ' AND product_offer.value = :offer' : '').' '
		)
		;
		
		/* Получаем тип торгового предложения */
		//$qb->addSelect('category_offer.reference as product_offer_reference')->addGroupBy('category_offer.reference'); // тип (field) торгового предложения
		$qb->leftJoin(
			'product_offer',
			CategoryEntity\Offers\ProductCategoryOffers::TABLE,
			'category_offer',
			'category_offer.id = product_offer.category_offer'
		);
		

		/* Цена торгового предожения */
		$qb->leftJoin(
			'product_offer',
			ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
			'product_offer_price',
			'product_offer_price.offer = product_offer.id'
		)
			//->addGroupBy('product_offer_price.price')
			->addGroupBy('product_offer_price.currency')
		;
		

		/** Множественные варианты торгового предложения */
		
		$method = 'leftJoin';
		if($filter->getVariation())
		{
			$method = 'join';
			$qb->setParameter('variation', $filter->getVariation());
		}
		
		//		$qb->addSelect('product_offer_variation.value as product_variation_value')
		//			->addGroupBy('product_offer_variation.value')
		//		;
		
		$qb->{$method}(
			'product_offer',
			ProductEntity\Offers\Variation\ProductOfferVariation::TABLE,
			'product_offer_variation',
			'product_offer_variation.offer = product_offer.id '.($filter->getVariation(
			) ? ' AND product_offer_variation.value = :variation' : '').' '
		)//->addGroupBy('product_offer_variation.article')
		;
		
		/** Получаем тип множественного варианта */
		//		$qb->addSelect('category_offer_variation.reference as product_variation_reference')
		//			->addGroupBy('category_offer_variation.reference')
		//		;
		$qb->leftJoin(
			'product_offer_variation',
			CategoryEntity\Offers\Variation\ProductCategoryOffersVariation::TABLE,
			'category_offer_variation',
			'category_offer_variation.id = product_offer_variation.category_variation'
		);
		
		/** Цена множественного варианта */
		//$qb->addSelect('MIN(product_variation_price.price) as variation_price');
		//$qb->addSelect('product_variation_price.currency as variation_currency');
		
		$qb->leftJoin(
			'category_offer_variation',
			ProductEntity\Offers\Variation\Price\ProductOfferVariationPrice::TABLE,
			'product_variation_price',
			'product_variation_price.variation = product_offer_variation.id'
		)
			//->addGroupBy('product_variation_price.price')
			->addGroupBy('product_variation_price.currency')
		;
		
		/** Модификация множественного варианта торгового предложения */
		
		$method = 'leftJoin';
		if($filter->getModification())
		{
			$method = 'join';
			$qb->setParameter('modification', $filter->getModification());
		}
		
		//		$qb->addSelect('product_offer_modification.value as product_modification_value')
		//			->addGroupBy('product_offer_modification.value')
		//		;
		
		$qb->{$method}(
			'product_offer_variation',
			ProductEntity\Offers\Variation\Modification\ProductOfferVariationModification::TABLE,
			'product_offer_modification',
			'product_offer_modification.variation = product_offer_variation.id '.($filter->getModification(
			) ? ' AND product_offer_modification.value = :modification' : '').' '
		)//->addGroupBy('product_offer_modification.article')
		;
		
		/** Получаем тип модификации множественного варианта */
		//		$qb->addSelect('category_offer_modification.reference as category_offer_modification')
		//			->addGroupBy('category_offer_modification.reference')
		//		;
		$qb->leftJoin(
			'product_offer_modification',
			CategoryEntity\Offers\Variation\Modification\ProductCategoryOffersVariationModification::TABLE,
			'category_offer_modification',
			'category_offer_modification.id = product_offer_modification.category_modification'
		);
		
		//$qb->addSelect('MIN(product_modification_price.price) as modification_price');
		//$qb->addSelect('product_modification_price.currency as modification_currency');
		
		/** Цена множественного варианта */
		$qb->leftJoin(
			'product_offer_modification',
			ProductEntity\Offers\Variation\Modification\Price\ProductOfferVariationModificationPrice::TABLE,
			'product_modification_price',
			'product_modification_price.modification = product_offer_modification.id'
		)
			//->addGroupBy('product_modification_price.price')
			->addGroupBy('product_modification_price.currency')
		;
		
		$qb->addSelect("JSON_AGG
			( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						
						/* свойства для сортирвоки JSON */
						'0', CONCAT(product_offer.value, product_offer_variation.value, product_offer_modification.value),
						
						
						'offer_value', product_offer.value, /* значение торгового предложения */
						'offer_reference', category_offer.reference, /* тип (field) торгового предложения */
						'offer_article', product_offer.article, /* артикул торгового предложения */

						'variation_value', product_offer_variation.value, /* значение множественного варианта */
						'variation_reference', category_offer_variation.reference, /* тип (field) множественного варианта */
						'variation_article', category_offer_variation.article, /* валюта множественного варианта */

						'modification_value', product_offer_modification.value, /* значение модификации */
						'modification_reference', category_offer_modification.reference, /* тип (field) модификации */
						'modification_article', category_offer_modification.article /* артикул модификации */

					)
				
			)
			AS product_offers"
		);
		
		//$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");
		
		/** Артикул продукта */
		
		//		$qb->addSelect("
		//			CASE
		//			   WHEN product_offer_modification.article IS NOT NULL THEN product_offer_modification.article
		//			   WHEN product_offer_variation.article IS NOT NULL THEN product_offer_variation.article
		//			   WHEN product_offer.article IS NOT NULL THEN product_offer.article
		//			   WHEN product_info.article IS NOT NULL THEN product_info.article
		//			   ELSE NULL
		//			END AS product_article
		//		"
		//		);
		
		/** Фото продукта */
		
		$qb->leftJoin(
			'product_offer_modification',
			ProductEntity\Offers\Variation\Modification\Image\ProductOfferVariationModificationImage::TABLE,
			'product_offer_modification_image',
			'
			product_offer_modification_image.modification = product_offer_modification.id AND
			product_offer_modification_image.root = true
			'
		)
			->addGroupBy('product_offer_modification_image.name')
			->addGroupBy('product_offer_modification_image.dir')
			->addGroupBy('product_offer_modification_image.ext')
			->addGroupBy('product_offer_modification_image.cdn')
		;
		
		$qb->leftJoin(
			'product_offer',
			ProductEntity\Offers\Variation\Image\ProductOfferVariationImage::TABLE,
			'product_offer_variation_image',
			'
			product_offer_variation_image.variation = product_offer_variation.id AND
			product_offer_variation_image.root = true
			'
		)
			->addGroupBy('product_offer_variation_image.name')
			->addGroupBy('product_offer_variation_image.dir')
			->addGroupBy('product_offer_variation_image.ext')
			->addGroupBy('product_offer_variation_image.cdn')
		;
		
		$qb->leftJoin(
			'product_offer',
			ProductEntity\Offers\Image\ProductOfferImage::TABLE,
			'product_offer_images',
			'
			product_offer_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			'
		)
			->addGroupBy('product_offer_images.name')
			->addGroupBy('product_offer_images.dir')
			->addGroupBy('product_offer_images.ext')
			->addGroupBy('product_offer_images.cdn')
		;
		
		$qb->leftJoin(
			'product_offer',
			ProductEntity\Photo\ProductPhoto::TABLE,
			'product_photo',
			'
			product_offer_images.name IS NULL AND
			product_photo.event = product_event.id AND
			product_photo.root = true
			'
		)->addGroupBy('product_photo.name')
			->addGroupBy('product_photo.dir')
			->addGroupBy('product_photo.ext')
			->addGroupBy('product_photo.cdn')
		;
		
		$qb->addSelect("
			CASE
			 WHEN product_offer_modification_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Variation\Modification\Image\ProductOfferVariationModificationImage::TABLE."' , '/', product_offer_modification_image.dir, '/', product_offer_modification_image.name, '.')
			   WHEN product_offer_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' , '/', product_offer_variation_image.dir, '/', product_offer_variation_image.name, '.')
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Offers\Image\ProductOfferImage::TABLE."' , '/', product_offer_images.dir, '/', product_offer_images.name, '.')
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductEntity\Photo\ProductPhoto::TABLE."' , '/', product_photo.dir, '/', product_photo.name, '.')
			   ELSE NULL
			END AS product_image
		"
		);
		
		/** Флаг загрузки файла CDN */
		$qb->addSelect("
			CASE
			WHEN product_offer_modification_image.name IS NOT NULL THEN
					product_offer_modification_image.ext
			   WHEN product_offer_variation_image.name IS NOT NULL THEN
					product_offer_variation_image.ext
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.ext
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.ext
			   ELSE NULL
			END AS product_image_ext
		"
		);
		
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
		"
		);
		
		/** Минимальная стоиомсть продукта */
		$qb->addSelect("
			CASE
			   WHEN MIN(product_modification_price.price) IS NOT NULL AND MIN(product_modification_price.price) > 0 THEN MIN(product_modification_price.price)
			   WHEN MIN(product_variation_price.price) IS NOT NULL AND MIN(product_variation_price.price) > 0 THEN MIN(product_variation_price.price)
			   WHEN MIN(product_offer_price.price) IS NOT NULL AND MIN(product_offer_price.price) > 0 THEN MIN(product_offer_price.price)
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE NULL
			END AS product_price
		"
		);
		
		/** Валюта продукта */
		$qb->addSelect("
			CASE
			   WHEN MIN(product_modification_price.price) IS NOT NULL AND MIN(product_modification_price.price) > 0 THEN product_modification_price.currency
			   WHEN MIN(product_variation_price.price) IS NOT NULL AND MIN(product_variation_price.price) > 0  THEN product_variation_price.currency
			   WHEN MIN(product_offer_price.price) IS NOT NULL AND MIN(product_offer_price.price) > 0 THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		"
		);
		
		/* Категория */
//		$qb->join(
//			'product_event',
//			ProductEntity\Category\ProductCategory::TABLE,
//			'product_event_category',
//			'product_event_category.event = product_event.id AND product_event_category.root = true'
//		);
		
		
		
		
		
		//dd($filters);
		
		//dd($filters);
		
		$qb->leftJoin(
			'product',
			ProductEntity\Property\ProductProperty::TABLE,
			'product_property',
			'product_property.event = product.event AND product_property.field = category_section_field.id'
		);
		
		
		
		
		$qb->addSelect("JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
					'field_sort', category_section_field.sort,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				
				)
			
		)
			AS category_section_field"
		);
		
		//dump($qb->fetchAllAssociative());
		
		return $this->paginator->fetchAllAssociative($qb);
		//return $qb->fetchAllAssociative();
	}
	
}