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

namespace BaksDev\Products\Product\Repository\AllExistsProducts;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
//use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Category\Entity\Offers\ProductCategoryOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\ProductCategoryModification;
use BaksDev\Products\Category\Entity\Offers\Variation\ProductCategoryVariation;
use BaksDev\Products\Category\Entity\Trans\ProductCategoryTrans;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllExistsProducts implements AllExistsProductsInterface
{
    private PaginatorInterface $paginator;
    private DBALQueryBuilder $DBALQueryBuilder;

    private ?SearchDTO $search = null;
    private ?ProductFilterDTO $filter = null;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    )
    {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function filter(ProductFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }


    /** Получает всю продукцию, имеющуюся в наличие */
    public function getAllProducts(): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $qb->select('product.id');
        $qb->addSelect('product.event');

        $qb->from(Product::TABLE, 'product');

        $qb->leftJoin('product', ProductEvent::TABLE, 'product_event', 'product_event.id = product.event');

        $qb->addSelect('product_trans.name AS product_name');

        $qb->leftJoin(
            'product_event',
            ProductTrans::TABLE,
            'product_trans',
            'product_trans.event = product_event.id AND product_trans.local = :local'
        );


        $qb
            ->addSelect('product_desc.preview AS product_preview')
            ->addSelect('product_desc.description AS product_description')
            ->leftJoin(
                'product_event',
                ProductDescription::TABLE,
                'product_desc',
                'product_desc.event = product_event.id AND product_desc.device = :device '

            )->setParameter('device', 'pc');


        //$qb->andWhere('product_info.profile = :profile OR product_info.profile IS NULL');
        //$qb->setParameter('profile', $profile, UserProfileUid::TYPE);


        /* ProductInfo */

        $qb->addSelect('product_info.url');

        $qb->leftJoin(
            'product_event',
            ProductInfo::TABLE,
            'product_info',
            'product_info.product = product.id'
        );


        /** Ответственное лицо (Профиль пользователя) */

        $qb->leftJoin(
            'product_info',
            UserProfile::TABLE,
            'users_profile',
            'users_profile.id = product_info.profile'
        );

        $qb->addSelect('users_profile_personal.username AS users_profile_username');
        $qb->leftJoin(
            'users_profile',
            UserProfilePersonal::TABLE,
            'users_profile_personal',
            'users_profile_personal.event = users_profile.event'
        );

        /* ProductModify */

        //		$qb->addSelect('product_modify.mod_date');
        //		$qb->leftJoin(
        //			'product_event',
        //			Entity\Modify\ProductModify::TABLE,
        //			'product_modify',
        //			'product_modify.event = product_event.id'
        //		);


        /* Базовая Цена товара */
        $qb->leftJoin(
            'product_event',
            ProductPrice::TABLE,
            'product_price',
            'product_price.event = product_event.id'
        );


        /** Торговое предложение */

        $qb->addSelect('product_offer.id as product_offer_id');
        $qb->addSelect('product_offer.value as product_offer_value');
        $qb->addSelect('product_offer.postfix as product_offer_postfix');

        $qb->leftJoin(
            'product_event',
            ProductOffer::TABLE,
            'product_offer',
            'product_offer.event = product_event.id'
        );

        if($this->filter->getOffer())
        {
            $qb->andWhere('product_offer.value = :offer');
            $qb->setParameter('offer', $this->filter->getOffer());
        }


        //        /* Цена торгового предожения */
        //        $qb->leftJoin(
        //            'product_offer',
        //            Entity\Offers\Price\ProductOfferPrice::TABLE,
        //            'product_offer_price',
        //            'product_offer_price.offer = product_offer.id'
        //        );

        /* Тип торгового предложения */
        $qb->addSelect('category_offer.reference as product_offer_reference');

        $qb->leftJoin(
            'product_offer',
            ProductCategoryOffers::TABLE,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );


        /* Наличие и резерв торгового предложения */
        $qb->leftJoin(
            'product_offer',
            ProductOfferQuantity::TABLE,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        );



        /** Множественные варианты торгового предложения */

        $qb->addSelect('product_variation.id as product_variation_id');
        $qb->addSelect('product_variation.value as product_variation_value');
        $qb->addSelect('product_variation.postfix as product_variation_postfix');

        $qb->leftJoin(
            'product_offer',
            ProductVariation::TABLE,
            'product_variation',
            'product_variation.offer = product_offer.id'
        );

        /* Наличие и резерв множественного варианта */
        $qb->leftJoin(
            'product_variation',
            ProductVariationQuantity::TABLE,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        );


        if($this->filter->getVariation())
        {
            $qb->andWhere('product_variation.value = :variation');
            $qb->setParameter('variation', $this->filter->getVariation());
        }


        /* Цена множественного варианта */
        $qb->leftJoin(
            'category_variation',
            ProductVariationPrice::TABLE,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );


        /* Тип множественного варианта торгового предложения */
        $qb->addSelect('category_variation.reference as product_variation_reference');
        $qb->leftJoin(
            'product_variation',
            ProductCategoryVariation::TABLE,
            'category_variation',
            'category_variation.id = product_variation.category_variation'
        );


        /** Модификация множественного варианта */
        $qb->addSelect('product_modification.id as product_modification_id');
        $qb->addSelect('product_modification.value as product_modification_value');
        $qb->addSelect('product_modification.postfix as product_modification_postfix');

        $qb->leftJoin(
            'product_variation',
            ProductModification::TABLE,
            'product_modification',
            'product_modification.variation = product_variation.id '
        );

        /* Наличие и резерв модификации множественного варианта */
        $qb->leftJoin(
            'product_modification',
            ProductModificationQuantity::TABLE,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        );


        if($this->filter->getModification())
        {
            $qb->andWhere('product_modification.value = :modification');
            $qb->setParameter('modification', $this->filter->getModification());
        }

        /** Получаем тип модификации множественного варианта */
        $qb->addSelect('category_modification.reference as product_modification_reference');
        $qb->leftJoin(
            'product_modification',
            ProductCategoryModification::TABLE,
            'category_modification',
            'category_modification.id = product_modification.category_modification'
        );


        //$qb->addSelect("'".Entity\Offers\Variation\Image\ProductOfferVariationImage::TABLE."' AS upload_image_dir ");

        /** Артикул продукта */

        $qb->addSelect("
					CASE
					   WHEN product_modification.article IS NOT NULL THEN product_modification.article
					   WHEN product_variation.article IS NOT NULL THEN product_variation.article
					   WHEN product_offer.article IS NOT NULL THEN product_offer.article
					   WHEN product_info.article IS NOT NULL THEN product_info.article
					   ELSE NULL
					END AS product_article
				"
        );


        /** Фото продукта */

        $qb->leftJoin(
            'product_event',
            ProductPhoto::TABLE,
            'product_photo',
            'product_photo.event = product_event.id AND product_photo.root = true'
        );

        $qb->leftJoin(
            'product_offer',
            ProductVariationImage::TABLE,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id AND product_variation_image.root = true'
        );

        $qb->leftJoin(
            'product_offer',
            ProductOfferImage::TABLE,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
        );

        $qb->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductVariationImage::TABLE."' , '/', product_variation_image.name)
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductOfferImage::TABLE."' , '/', product_offer_images.name)
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".ProductPhoto::TABLE."' , '/', product_photo.name)
			   ELSE NULL
			END AS product_image
		"
        );

        /** Флаг загрузки файла CDN */
        $qb->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					product_variation_image.ext
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
			   WHEN product_variation_image.name IS NOT NULL THEN
					product_variation_image.cdn
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
            ProductCategory::TABLE,
            'product_event_category',
            'product_event_category.event = product_event.id AND product_event_category.root = true'
        );

        if($this->filter->getCategory())
        {
            $qb->andWhere('product_event_category.category = :category');
            $qb->setParameter('category', $this->filter->getCategory(), ProductCategoryUid::TYPE);
        }

        $qb->join(
            'product_event_category',
            \BaksDev\Products\Category\Entity\ProductCategory::TABLE,
            'category',
            'category.id = product_event_category.category'
        );

        $qb->addSelect('category_trans.name AS category_name');

        $qb->leftJoin(
            'category',
            ProductCategoryTrans::TABLE,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );


        $UANTITY = '
			CASE
			   WHEN product_modification_quantity.quantity IS NOT NULL AND product_modification_quantity.reserve IS NOT NULL THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)
			   WHEN product_modification_quantity.quantity IS NOT NULL THEN product_modification_quantity.quantity

			   WHEN product_variation_quantity.quantity IS NOT NULL AND product_variation_quantity.reserve  IS NOT NULL THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			   WHEN product_variation_quantity.quantity IS NOT NULL THEN product_variation_quantity.quantity

			   WHEN product_offer_quantity.quantity IS NOT NULL  AND product_offer_quantity.reserve IS NOT NULL THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)
			   WHEN product_offer_quantity.quantity IS NOT NULL THEN product_offer_quantity.quantity

			   WHEN product_price.quantity IS NOT NULL AND product_price.reserve IS NOT NULL THEN (product_price.quantity - product_price.reserve)
			   WHEN product_price.quantity IS NOT NULL THEN product_price.quantity
			   
			   ELSE 0
			   
			END';

        $qb->addSelect($UANTITY.' AS product_quantity ');
        $qb->andWhere($UANTITY.' > 0');


        if($this->search->getQuery())
        {

            $qb
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('product.id')
                ->addSearchEqualUid('product.event')
                ->addSearchEqualUid('product_variation.id')
                ->addSearchEqualUid('product_modification.id')
                ->addSearchLike('product_trans.name')
                //->addSearchLike('product_trans.preview')
                ->addSearchLike('product_info.article')
                ->addSearchLike('product_offer.article')
                ->addSearchLike('product_modification.article')
                ->addSearchLike('product_modification.article')
                ->addSearchLike('product_variation.article');

        }

        $qb->orderBy('product.event', 'DESC');

        return $this->paginator->fetchAllAssociative($qb);

    }


}