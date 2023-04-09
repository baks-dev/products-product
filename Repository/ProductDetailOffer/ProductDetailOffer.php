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

namespace BaksDev\Products\Product\Repository\ProductDetailOffer;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Category\Entity as CategoryEntity;

use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductDetailOffer implements ProductDetailOfferInterface
{
	private Connection $connection;
	
	private TranslatorInterface $translator;
	
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
	)
	{
		$this->connection = $connection;
		$this->translator = $translator;
	}
	
	
	public function fetchProductOfferAssociative(
		ProductUid $product,
		$offer = null,
		$variation = null,
		$modification = null,
	) : array|bool
	{
		$qb = $this->connection->createQueryBuilder();
		
		$qb->select('product.id');
		
		$qb->from(ProductEntity\Product::TABLE, 'product');
		
		
		$qb->join('product',
			ProductEntity\Event\ProductEvent::TABLE,
			'product_event',
			'product_event.id = product.event'
		);
		
		/** Цена товара */
		$qb->leftJoin(
			'product_event',
			ProductEntity\Price\ProductPrice::TABLE,
			'product_price',
			'product_price.event = product_event.id'
		);
		
		/** Торговое предложение */
		
		$qb->addSelect('product_offer.value as product_offer_value');
		$qb->addSelect('product_offer.category_offer as category_offer');
		$qb->leftJoin(
			'product_event',
			ProductEntity\Offers\ProductOffer::TABLE,
			'product_offer',
			'product_offer.event = product_event.id'
		);
		
		
		/* Цена торгового предожения */
		$qb->leftJoin(
			'product_offer',
			ProductEntity\Offers\Price\ProductOfferPrice::TABLE,
			'product_offer_price',
			'product_offer_price.offer = product_offer.id'
		)
			//->addGroupBy('product_offer_price.price')
			//->addGroupBy('product_offer_price.currency')
		;
		
		/* Получаем тип торгового предложения */
		$qb->addSelect('category_offer.reference AS product_offer_reference');
		$qb->leftJoin(
			'product_offer',
			CategoryEntity\Offers\ProductCategoryOffers::TABLE,
			'category_offer',
			'category_offer.id = product_offer.category_offer'
		);
		
		/** Получаем название торгового предложения */
		$qb->addSelect('category_offer_trans.name as product_offer_name');
		$qb->leftJoin(
			'category_offer',
			CategoryEntity\Offers\Trans\ProductCategoryOffersTrans::TABLE,
			'category_offer_trans',
			'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local'
		);
		
		
		
		
		
		/** Множественные варианты торгового предложения */
		
				$qb->addSelect('product_offer_variation.value as product_variation_value')
		//			->addGroupBy('product_offer_variation.value')
				;
		
		$qb->leftJoin(
			'product_offer',
			ProductEntity\Offers\Variation\ProductOfferVariation::TABLE,
			'product_offer_variation',
			'product_offer_variation.offer = product_offer.id'
		)
			//->addGroupBy('product_offer_variation.article')
		;
		
		
		/* Цена множественного варианта */
		$qb->leftJoin(
			'category_offer_variation',
			ProductEntity\Offers\Variation\Price\ProductOfferVariationPrice::TABLE,
			'product_variation_price',
			'product_variation_price.variation = product_offer_variation.id'
		)
			//->addGroupBy('product_variation_price.price')
			//->addGroupBy('product_variation_price.currency')
		;
		
		/* Получаем тип множественного варианта */
		$qb->addSelect('category_offer_variation.reference as product_variation_reference')
			//	->addGroupBy('category_offer_variation.reference')
		;
		$qb->leftJoin(
			'product_offer_variation',
			CategoryEntity\Offers\Variation\ProductCategoryOffersVariation::TABLE,
			'category_offer_variation',
			'category_offer_variation.id = product_offer_variation.category_variation'
		);
		
		/* Получаем название множественного варианта */
		$qb->addSelect('category_offer_variation_trans.name as product_variation_name')
			//	->addGroupBy('category_offer_variation_trans.name')
		;
		$qb->leftJoin(
			'category_offer_variation',
			CategoryEntity\Offers\Variation\Trans\ProductCategoryOffersVariationTrans::TABLE,
			'category_offer_variation_trans',
			'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local'
		);
		
		
		
		/** Модификация множественного варианта торгового предложения */
		
		$qb->addSelect('product_offer_modification.value as product_modification_value')
		//->addGroupBy('product_offer_modification.value')
		;
		
		$qb->leftJoin(
			'product_offer_variation',
			ProductEntity\Offers\Variation\Modification\ProductOfferVariationModification::TABLE,
			'product_offer_modification',
			'product_offer_modification.variation = product_offer_variation.id'
		)
			//->addGroupBy('product_offer_modification.article')
		;
		
		//		if($modification) {
		//			$qb->setParameter('product_modification_value', $modification);
		//		}
		
		
		
		
		
		/** Цена Модификации множественного варианта */
		$qb->leftJoin(
			'product_offer_modification',
			ProductEntity\Offers\Variation\Modification\Price\ProductOfferVariationModificationPrice::TABLE,
			'product_modification_price',
			'product_modification_price.modification = product_offer_modification.id'
		)
			//->addGroupBy('product_modification_price.price')
			//->addGroupBy('product_modification_price.currency')
		;
		
		/** Получаем тип множественного варианта */
				$qb->addSelect('category_offer_modification.reference as product_modification_reference')
		//			->addGroupBy('category_offer_modification.reference')
				;
		$qb->leftJoin(
			'product_offer_modification',
			CategoryEntity\Offers\Variation\Modification\ProductCategoryOffersVariationModification::TABLE,
			'category_offer_modification',
			'category_offer_modification.id = product_offer_modification.category_modification'
		);
		
		/** Получаем название типа */
				$qb->addSelect('category_offer_modification_trans.name as product_modification_name')
		//			->addGroupBy('category_offer_modification_trans.name')
				;
		$qb->leftJoin(
			'category_offer_modification',
			CategoryEntity\Offers\Variation\Modification\Trans\ProductCategoryOffersVariationModificationTrans::TABLE,
			'category_offer_modification_trans',
			'category_offer_modification_trans.modification = category_offer_modification.id AND category_offer_modification_trans.local = :local'
		);
		
		//$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");
		
		
		$qb->addSelect('product_variation_price.price');
		/** Стоимость продукта */
		$qb->addSelect("
			CASE
			   WHEN product_modification_price.price IS NOT NULL THEN product_modification_price.price
			   WHEN product_variation_price.price IS NOT NULL THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE NULL
			END AS product_price
		"
		);

		/** Валюта продукта */
		$qb->addSelect("
			CASE
			   WHEN product_modification_price.price IS NOT NULL THEN product_modification_price.currency
			   WHEN product_variation_price.price IS NOT NULL THEN product_variation_price.currency
			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.currency
			   WHEN product_price.price IS NOT NULL THEN product_price.currency
			   ELSE NULL
			END AS product_currency
		"
		);
		
		
		$qb->where('product.id = :product');
		$qb->setParameter('product', $product, ProductUid::TYPE);
		$qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);
		
		//$qb->select('id');
		//$qb->from(ClasssName::TABLE, 'wb_order');
		
		return $qb->fetchAllAssociative();
		
		
	}
}