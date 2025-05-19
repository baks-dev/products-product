<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\Cards\ProductCatalog;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Generator;

/** @see ProductCatalogResult */
final class ProductCatalogRepository implements ProductCatalogInterface
{
    private CategoryProductUid|false $categoryUid = false;

    private int|false $maxResult = false;

    private ?ProductCategoryFilterDTO $filter = null;

    private ?array $property = null;

    public function __construct(
        private readonly DBALQueryBuilder $dbal,
        private readonly UserProfileTokenStorageInterface $userProfileTokenStorage,
    ) {}

    public function filter(ProductCategoryFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function property(?array $property): self
    {
        if(empty($property))
        {
            return $this;
        }

        $this->property = $property;

        return $this;
    }

    /** Максимальное количество записей в результате */
    public function maxResult(int $max): self
    {
        $this->maxResult = $max;
        return $this;
    }

    /** Фильтр по категории */
    public function forCategory(CategoryProduct|CategoryProductUid|string $category): self
    {
        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $this->categoryUid = $category;

        return $this;
    }

    /**
     * Метод возвращает ограниченный по количеству элементов список продуктов из разных категорий
     *
     * @return Generator<int, ProductCatalogResult>|false
     */
    public function findAll(string $expr = 'AND'): Generator|false
    {
        $dbal = $this->builder($expr);

        $dbal->enableCache('products-product', 86400);

        $result = $dbal->fetchAllHydrate(ProductCatalogResult::class);

        return (true === $result->valid()) ? $result : false;
    }

    /**
     * Метод возвращает список продуктов из разных категорий
     *
     * @return array<int, ProductCatalogResult>|false
     */
    public function toArray(string $expr = 'AND'): array|false
    {
        $result = $this->findAll();

        return (false !== $result) ? iterator_to_array($result) : false;
    }

    public function builder($expr): DBALQueryBuilder
    {

        $dbal = $this->dbal
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id AS product_id')
            ->addSelect('product.event AS product_event')
            ->from(Product::class, 'product');

        $dbal->join('product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );

        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url AS product_url')
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            )
            ->addGroupBy('product_info.article')
            ->addGroupBy('product_info.sort');

        /** Категория */
        if($this->categoryUid instanceof CategoryProductUid)
        {
            $dbal->join(
                'product',
                ProductCategory::class,
                'product_event_category',
                '
                product_event_category.event = product.event AND 
                product_event_category.category = :category AND 
                product_event_category.root = true'
            )->setParameter(
                'category',
                $this->categoryUid,
                CategoryProductUid::TYPE
            );
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductCategory::class,
                'product_event_category',
                '
                product_event_category.event = product.event AND 
                product_event_category.root = true'
            );
        }


        /** ФИЛЬТР СВОЙСТВ */
        if($this->property)
        {
            if($expr === 'AND')
            {
                /** @var RangeIntegerFieldDTO $item */
                foreach($this->property as $type => $item)
                {
                    if($item === true)
                    {
                        $item = 'true';
                    }

                    if ($item instanceof RangeIntegerFieldDTO) {
                        $item = [
                            'min' => $item->getMin(),
                            'max' => $item->getMax(),
                        ];
                    }

                    $prepareKey = uniqid('key_', false);
                    $prepareValue = uniqid('val_', false);
                    $alias = uniqid('alias', false);

                    $ProductCategorySectionFieldUid = new CategoryProductSectionFieldUid($type);
                    $ProductPropertyJoin = $alias.'.field = :'.$prepareKey.' AND '.$alias.'.value = :'.$prepareValue;

                    $dbal->setParameter(
                        $prepareKey,
                        $ProductCategorySectionFieldUid,
                        CategoryProductSectionFieldUid::TYPE
                    );

                    if(is_array($item) && (isset($item['min']) || isset($item['max'])))
                    {
                        $RangeProductPropertyJoin = null;

                        foreach($item as $key => $value)
                        {
                            if(empty($value))
                            {
                                continue;
                            }

                            $prepareValue = uniqid('', false);

                            if($key === 'min')
                            {
                                $RangeProductPropertyJoin[] = 'product_property_filter.value >= :'.$prepareValue;

                            }

                            if($key === 'max')
                            {
                                $RangeProductPropertyJoin[] = 'product_property_filter.value <= :'.$prepareValue;

                            }

                            $dbal->setParameter($prepareValue, $value);
                        }
                        if($RangeProductPropertyJoin)
                        {

                            $ProductCategorySectionFieldUid = new CategoryProductSectionFieldUid($type);

                            $dbal->setParameter(
                                $prepareKey,
                                $ProductCategorySectionFieldUid,
                                CategoryProductSectionFieldUid::TYPE
                            );

                            $ProductPropertyJoin = 'product_property_filter.field = :'.$prepareKey.' 
                                AND ('.implode(' AND ', $RangeProductPropertyJoin).')';


                        }

                        continue;
                    }

                    else
                    {
                        $dbal->setParameter($prepareValue, $item);
                    }

                    $dbal->join(
                        'product',
                        ProductProperty::class,
                        $alias,
                        $alias.'.event = product.event '.$expr.' '.$ProductPropertyJoin
                    );
                }

                $dbal->join(
                    'product',
                    ProductProperty::class,
                    'product_property_filter',
                    'product_property_filter.event = product.event AND '.$ProductPropertyJoin
                );
            }
            else
            {
                foreach($this->property as $type => $item)
                {
                    if($item === true)
                    {
                        $item = 'true';
                    }

                    if(is_array($item) && (isset($item['min']) || isset($item['max'])))
                    {
                        $RangeProductPropertyJoin = null;

                        foreach($item as $key => $value)
                        {
                            if(empty($value))
                            {
                                continue;
                            }

                            $prepareValue = uniqid('', false);

                            if($key === 'min')
                            {
                                $RangeProductPropertyJoin[] = 'product_property_filter.value >= :'.$prepareValue;

                            }

                            if($key === 'max')
                            {
                                $RangeProductPropertyJoin[] = 'product_property_filter.value <= :'.$prepareValue;

                            }

                            $dbal->setParameter($prepareValue, $value);
                        }

                        if($RangeProductPropertyJoin)
                        {
                            $prepareKey = uniqid('', false);

                            $ProductCategorySectionFieldUid = new CategoryProductSectionFieldUid($type);

                            $dbal->setParameter(
                                $prepareKey,
                                $ProductCategorySectionFieldUid,
                                CategoryProductSectionFieldUid::TYPE
                            );

                            $ProductPropertyJoin[] = 'product_property_filter.field = :'.$prepareKey.' 
                                AND ('.implode(' AND ', $RangeProductPropertyJoin).')';
                        }

                        continue;
                    }

                    $prepareKey = uniqid('', false);
                    $prepareValue = uniqid('', false);

                    $ProductCategorySectionFieldUid = new CategoryProductSectionFieldUid($type);
                    $ProductPropertyJoin[] = 'product_property_filter.field = :'.$prepareKey.' AND product_property_filter.value = :'.$prepareValue;

                    $dbal->setParameter(
                        $prepareKey,
                        $ProductCategorySectionFieldUid,
                        CategoryProductSectionFieldUid::TYPE
                    );

                    $dbal->setParameter($prepareValue, $item);

                }

                $dbal->join(
                    'product',
                    ProductProperty::class,
                    'product_property_filter',
                    'product_property_filter.event = product.event AND '.implode(' '.$expr.' ', $ProductPropertyJoin)
                );
            }
        }

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local'
            );

        /** Цена товара */
        $dbal
            ->leftJoin(
                'product_event',
                ProductPrice::class,
                'product_price',
                'product_price.event = product_event.id'
            )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency')
            ->addGroupBy('product_price.quantity')
            ->addGroupBy('product_price.reserve');


        /** Даты продукта */
        $dbal
            ->addSelect('product_active.active_from')
            //            ->addSelect('product_active.active_to')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                'product_active.event = product.event'
            );

        /** OFFERS */
        $method = 'leftJoin';

        if($this->filter?->getOffer())
        {
            $method = 'join';
            $dbal->setParameter('offer', $this->filter->getOffer());
        }

        $dbal
            ->addSelect('product_offer.id as product_offer_uid')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->{$method}(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event '.($this->filter?->getOffer() ? ' AND product_offer.value = :offer' : '').' '
            );

        /** Цена торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_price.price')
            ->addGroupBy('product_offer_price.currency');

        /** Наличие торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        )
            ->addGroupBy('product_offer_quantity.quantity')
            ->addGroupBy('product_offer_quantity.reserve');

        /** Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /** VARIATIONS */
        $method = 'leftJoin';

        if($this->filter?->getVariation())
        {
            $method = 'join';
            $dbal->setParameter('variation', $this->filter->getVariation());
        }

        $dbal
            ->addSelect('product_offer_variation.id as product_variation_uid')
            ->addSelect('product_offer_variation.value as product_variation_value')
            ->addSelect('product_offer_variation.postfix as product_variation_postfix')
            ->{$method}(
                'product_offer',
                ProductVariation::class,
                'product_offer_variation',
                'product_offer_variation.offer = product_offer.id '.($this->filter?->getVariation() ? ' AND product_offer_variation.value = :variation' : '').' '
            );


        /** Цена множественного варианта */
        $dbal->leftJoin(
            'category_offer_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_price.price')
            ->addGroupBy('product_variation_price.currency');

        /** Наличие множественного варианта */
        $dbal->leftJoin(
            'category_offer_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_offer_variation.id'
        )
            ->addGroupBy('product_variation_quantity.quantity')
            ->addGroupBy('product_variation_quantity.reserve');

        /** Получаем тип множественного варианта */
        $dbal
            ->addSelect('category_offer_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_offer_variation',
                CategoryProductVariation::class,
                'category_offer_variation',
                'category_offer_variation.id = product_offer_variation.category_variation'
            );

        /** MODIFICATION */
        $method = 'leftJoin';

        if($this->filter?->getModification())
        {
            $method = 'join';
            $dbal->setParameter('modification', $this->filter->getModification());
        }

        $dbal
            ->addSelect('product_offer_modification.id as product_modification_uid')
            ->addSelect('product_offer_modification.value as product_modification_value')
            ->addSelect('product_offer_modification.postfix as product_modification_postfix')
            ->{$method}(
                'category_offer_variation',
                ProductModification::class,
                'product_offer_modification',
                'product_offer_modification.variation = product_offer_variation.id '.($this->filter?->getModification() ? ' AND product_offer_modification.value = :modification' : '').' '
            );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_price.price')
            ->addGroupBy('product_modification_price.currency');

        /** Наличие множественного варианта */
        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_offer_modification.id'
        )
            ->addGroupBy('product_modification_quantity.quantity')
            ->addGroupBy('product_modification_quantity.reserve');

        /** Получаем тип множественного варианта */
        $dbal
            ->addSelect('category_offer_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_offer_modification',
                CategoryProductModification::class,
                'category_offer_modification',
                'category_offer_modification.id = product_offer_modification.category_modification'
            );

        /** Артикул продукта */
        $dbal->addSelect("
			CASE
			   WHEN product_offer_modification.article IS NOT NULL 
			   THEN product_offer_modification.article
			   
			   WHEN product_offer_variation.article IS NOT NULL 
			   THEN product_offer_variation.article
			   
			   WHEN product_offer.article IS NOT NULL 
			   THEN product_offer.article
			   
			   WHEN product_info.article IS NOT NULL 
			   THEN product_info.article
			   
			   ELSE NULL
			END AS product_article
		"
        );

        /** Фото продукта */
        $dbal->leftJoin(
            'product_offer_modification',
            ProductModificationImage::class,
            'product_offer_modification_image',
            'product_offer_modification_image.modification = product_offer_modification.id AND product_offer_modification_image.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_offer_variation_image',
            'product_offer_variation_image.variation = product_offer_variation.id AND product_offer_variation_image.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_variation_image.name IS NULL AND product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductPhoto::class,
            'product_photo',
            'product_offer_images.name IS NULL AND product_photo.event = product_event.id AND product_photo.root = true'
        );

        $dbal->addSelect(
            "JSON_AGG 
            (DISTINCT
				CASE 
                    WHEN product_offer_images.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_images.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                            'img_ext', product_offer_images.ext,
                            'img_cdn', product_offer_images.cdn
                        ) 
                    WHEN product_offer_variation_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_variation_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_offer_variation_image.name),
                            'img_ext', product_offer_variation_image.ext,
                            'img_cdn', product_offer_variation_image.cdn
                        )	
                    WHEN product_offer_modification_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_modification_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_offer_modification_image.name),
                            'img_ext', product_offer_modification_image.ext,
                            'img_cdn', product_offer_modification_image.cdn
                        )
                    WHEN product_photo.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_photo.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                            'img_ext', product_photo.ext,
                            'img_cdn', product_photo.cdn
                        )
                    END) AS product_images"
        );

        /** Стоимость продукта */
        $dbal->addSelect('
			COALESCE(
                NULLIF(product_modification_price.price, 0), 
                NULLIF(product_variation_price.price, 0), 
                NULLIF(product_offer_price.price, 0), 
                NULLIF(product_price.price, 0),
                0
            ) AS product_price
		');

        /** Предыдущая стоимость продукта */
        $dbal->addSelect("
			COALESCE(
                NULLIF(product_modification_price.old, 0),
                NULLIF(product_variation_price.old, 0),
                NULLIF(product_offer_price.old, 0),
                NULLIF(product_price.old, 0),
                0
            ) AS product_old_price
		");

        /** Валюта продукта */
        $dbal->addSelect("
			CASE
			   WHEN COALESCE(product_modification_price.price, 0) != 0 
			   THEN product_modification_price.currency
			   
			   WHEN COALESCE(product_variation_price.price, 0) != 0 
			   THEN product_variation_price.currency
			   
			   WHEN COALESCE(product_offer_price.price, 0) != 0 
			   THEN product_offer_price.currency
			   
			   WHEN COALESCE(product_price.price, 0) != 0 
			   THEN product_price.currency
			   
			   ELSE NULL
			END AS product_currency"
        );

        $dbal->leftJoin(
            'product_event_category',
            CategoryProduct::class,
            'category',
            'category.id = product_event_category.category'
        );

        $dbal
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
                'product_event_category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event'
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event'
        );

        /** Свойства, участвующие в карточке */
        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.card = TRUE OR category_section_field.photo = TRUE OR category_section_field.name = TRUE )'
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local'
        );

        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.const'
        );

        $dbal->addSelect("JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
					'field_sort', category_section_field.sort,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_photo', category_section_field.photo,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)
			
		)
			AS category_section_field"
        );

        /**
         * Product Invariable
         */
        $dbal
            ->addSelect('product_invariable.id AS product_invariable_id')
            ->leftJoin(
                'product_offer_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id AND 
                    (
                        (product_offer.const IS NOT NULL AND product_invariable.offer = product_offer.const) OR 
                        (product_offer.const IS NULL AND product_invariable.offer IS NULL)
                    )
                    AND
                    (
                        (product_offer_variation.const IS NOT NULL AND product_invariable.variation = product_offer_variation.const) OR 
                        (product_offer_variation.const IS NULL AND product_invariable.variation IS NULL)
                    )
                   AND
                   (
                        (product_offer_modification.const IS NOT NULL AND product_invariable.modification = product_offer_modification.const) OR 
                        (product_offer_modification.const IS NULL AND product_invariable.modification IS NULL)
                   )
            ');

        /** Персональная скидка из профиля авторизованного пользователя */
        if(true === $this->userProfileTokenStorage->isUser())
        {
            $profile = $this->userProfileTokenStorage->getProfileCurrent();

            if($profile instanceof UserProfileUid)
            {
                $dbal
                    ->addSelect('profile_info.discount AS profile_discount')
                    ->leftJoin(
                        'product',
                        UserProfileInfo::class,
                        'profile_info',
                        '
                        profile_info.profile = :profile AND 
                        profile_info.status = :profile_status'
                    )
                    ->setParameter(
                        key: 'profile',
                        value: $profile,
                        type: UserProfileUid::TYPE)
                    /** Активный статус профиля */
                    ->setParameter(
                        key: 'profile_status',
                        value: UserProfileStatusActive::class,
                        type: UserProfileStatus::TYPE
                    );
            }
        }


        /** Только с ценой */
        $dbal->andWhere("
 			CASE
			   WHEN product_modification_price.price  IS NOT NULL THEN product_modification_price.price
			   WHEN product_variation_price.price  IS NOT NULL THEN product_variation_price.price
			   WHEN product_offer_price.price IS NOT NULL THEN product_offer_price.price
			   WHEN product_price.price IS NOT NULL THEN product_price.price
			   ELSE 0
			END > 0
 		"
        );


        /** Только при наличии */
        $dbal->andWhere("
 			CASE
			   WHEN product_modification_quantity.quantity IS NOT NULL THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)
			   WHEN product_variation_quantity.quantity IS NOT NULL THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			   WHEN product_offer_quantity.quantity IS NOT NULL THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)
			   WHEN product_price.quantity  IS NOT NULL THEN (product_price.quantity - product_price.reserve)
			   ELSE 0
			END > 0
			"
        );

        /** Используем индекс сортировки для поднятия в топ списка */
        $dbal->addOrderBy('product_info.sort', 'DESC');

        /** Сортируем список по количеству резерва продукции, суммируем если группировка по иному свойству */
        $dbal->addOrderBy('product_modification_quantity.reserve', 'DESC');
        $dbal->addOrderBy('product_variation_quantity.reserve', 'DESC');
        $dbal->addOrderBy('product_offer_quantity.reserve', 'DESC');
        $dbal->addOrderBy('product_price.reserve', 'DESC');


        $dbal->allGroupByExclude();

        if(false !== $this->maxResult)
        {
            $dbal->setMaxResults($this->maxResult);
        }

        return $dbal;
    }

    public function analyze(string $expr = 'AND'): void
    {
        $this->builder($expr)->analyze();
    }
}