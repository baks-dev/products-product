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

namespace BaksDev\Products\Product\Repository\AllProductsByCategory;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Device\Devices\Desktop;
use BaksDev\DeliveryTransport\BaksDevDeliveryTransportBundle;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Event\CategoryProductEvent;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Trans\CategoryProductOffersTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\Trans\CategoryProductModificationTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\Trans\CategoryProductVariationTrans;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
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
use BaksDev\Products\Promotion\BaksDevProductsPromotionBundle;
use BaksDev\Products\Promotion\Entity\Event\Invariable\ProductPromotionInvariable;
use BaksDev\Products\Promotion\Entity\Event\Period\ProductPromotionPeriod;
use BaksDev\Products\Promotion\Entity\Event\Price\ProductPromotionPrice;
use BaksDev\Products\Promotion\Entity\ProductPromotion;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class AllProductsByCategoryRepository implements AllProductsByCategoryInterface
{
    private ProductCategoryFilterDTO|null $filter = null;

    private array|null $property = null;

    private UserProfileUid|false $profile = false;

    private CategoryProductUid|false $category = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage
    ) {}

    /** Фильтра по offer, variation, modification в зависимости от настроек */
    public function filter(ProductCategoryFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /** Фильтр по свойствам категории */
    public function property(?array $property): self
    {
        if(empty($property))
        {
            return $this;
        }

        $this->property = $property;

        return $this;
    }

    public function forProfile(UserProfile|UserProfileUid|null|false $profile): self
    {
        if(empty($profile))
        {
            $this->category = false;
            return $this;
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    public function forCategory(CategoryProduct|CategoryProductUid|null|false $category): self
    {
        if(empty($category))
        {
            $this->category = false;
            return $this;
        }

        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        $this->category = $category;

        return $this;
    }

    /** Метод возвращает все товары в категории */
    public function fetchAllProductByCategory(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->addSelect('product_category.category');

        if(false === ($this->category instanceof CategoryProductUid))
        {
            $dbal->from(Product::class, 'product');

            $dbal->leftJoin(
                'product',
                ProductCategory::class,
                'product_category',
                '
                    product_category.event = product.event 
                    AND product_category.root IS TRUE
                ',
            );
        }
        else
        {
            $dbal
                ->from(ProductCategory::class, 'product_category')
                ->where('product_category.category = :category')
                ->andWhere('product_category.root IS TRUE')
                ->setParameter(
                    key: 'category',
                    value: $this->category,
                    type: CategoryProductUid::TYPE,
                );

            $dbal->join(
                'product_category',
                Product::class,
                'product',
                'product.event = product_category.event',
            );
        }

        $dbal->addSelect('product.id');

        $dbal
            ->addSelect('product_info.url')
            ->addSelect('product_info.sort')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );


        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local',
            );

        $dbal
            ->addSelect('product_desc.preview')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                '
                    product_desc.event = product.event 
                    AND product_desc.local = :local 
                    AND product_desc.device = :device
                ',
            )
            ->setParameter(
                key: 'device',
                value: new Device(Desktop::class),
                type: Device::TYPE,
            );

        $dbal
            ->addSelect('product_modify.mod_date AS modify')
            ->leftJoin(
                'product',
                ProductModify::class,
                'product_modify',
                'product_modify.event = product.event',
            );


        /**
         * Торговое предложение
         */

        $dbal
            ->addSelect('product_offer.value AS offer_value')
            ->addSelect('product_offer.postfix AS offer_postfix')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );

        /* Цена торгового предожения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id',
        );

        /* Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );


        /* Получаем название торгового предложения */
        $dbal
            ->addSelect('category_offer_trans.name as offer_name')
            ->addSelect('category_offer_trans.postfix as offer_name_postfix')
            ->leftJoin(
                'category_offer',
                CategoryProductOffersTrans::class,
                'category_offer_trans',
                'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
            );


        /**
         * Множественный вариант
         */

        $dbal
            ->addSelect('product_variation.value AS variation_value')
            ->addSelect('product_variation.postfix AS variation_postfix')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );


        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );

        $dbal->leftJoin(
            'category_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id',
        );

        $dbal
            ->addSelect('category_variation.reference as variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation',
            );

        /* Получаем название множественного варианта */
        $dbal
            ->addSelect('category_variation_trans.name as variation_name')
            ->addSelect('category_variation_trans.postfix as variation_name_postfix')
            ->leftJoin(
                'category_variation',
                CategoryProductVariationTrans::class,
                'category_variation_trans',
                'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local',
            );

        /**
         * Модификация множественного варианта торгового предложения
         */

        $dbal
            ->addSelect('product_modification.value AS modification_value')
            ->addSelect('product_modification.postfix AS modification_postfix')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id',
            );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        $dbal->leftJoin(
            'category_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id',
        );

        /** Получаем тип модификации множественного варианта */
        $dbal
            ->addSelect('category_modification.reference as modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification',
            );

        /* Получаем название типа модификации */
        $dbal
            ->addSelect('category_modification_trans.name as modification_name')
            ->addSelect('category_modification_trans.postfix as modification_name_postfix')
            ->leftJoin(
                'category_modification',
                CategoryProductModificationTrans::class,
                'category_modification_trans',
                'category_modification_trans.modification = category_modification.id AND category_modification_trans.local = :local',
            );



        /** Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );


        /** Стоимость */
        $dbal->addSelect('
			COALESCE(
                NULLIF(product_modification_price.price, 0), 
                NULLIF(product_variation_price.price, 0), 
                NULLIF(product_offer_price.price, 0), 
                NULLIF(product_price.price, 0),
                0
            ) AS product_price
		');


        /* Предыдущая стоимость продукта */

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
        $dbal->addSelect(
            "
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.currency
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0  
			   THEN product_variation_price.currency
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL 
			   THEN product_price.currency
			   
			   ELSE NULL
			END AS product_currency
		");


        /** Остатки товара на складе */

        $dbal->addSelect(
            '
			CASE

			    WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
			   THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)

			   WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve  
			   THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			
			   WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
			   THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)

			   WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
			   THEN (product_price.quantity - product_price.reserve)
	
			   ELSE 0
			END AS product_quantity
		');


        $dbal
            ->addSelect('product_invariable.id AS product_invariable')
            ->leftJoin(
                'product_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id
                    
                    AND
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN product_invariable.offer = product_offer.const
                            ELSE product_invariable.offer IS NULL
                        END
                    AND 
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN product_invariable.variation = product_variation.const
                            ELSE product_invariable.variation IS NULL
                        END
                    AND
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN product_invariable.modification = product_modification.const
                            ELSE product_invariable.modification IS NULL
                        END
                ');


        /** Общая скидка (наценка) из профиля магазина */
        if(true === $dbal->bindProjectProfile())
        {

            $dbal
                ->join(
                    'product',
                    UserProfile::class,
                    'project_profile',
                    'project_profile.id = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->addSelect('project_profile_discount.value AS project_discount')
                ->leftJoin(
                    'project_profile',
                    UserProfileDiscount::class,
                    'project_profile_discount',
                    '
                        project_profile_discount.event = project_profile.event',
                );
        }


        /**
         * ProductsPromotion
         */
        if(true === class_exists(BaksDevProductsPromotionBundle::class) && true === $dbal->isProjectProfile())
        {
            $dbal
                ->leftJoin(
                    'product_invariable',
                    ProductPromotionInvariable::class,
                    'product_promotion_invariable',
                    '
                        product_promotion_invariable.product = product_invariable.id
                        AND product_promotion_invariable.profile = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->leftJoin(
                    'product_promotion_invariable',
                    ProductPromotion::class,
                    'product_promotion',
                    'product_promotion.id = product_promotion_invariable.main',
                );

            $dbal
                ->addSelect('product_promotion_price.value AS promotion_price')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPrice::class,
                    'product_promotion_price',
                    'product_promotion_price.event = product_promotion.event',
                );

            $dbal
                ->addSelect('
                    CASE
                        WHEN 
                            CURRENT_DATE >= product_promotion_period.date_start
                            AND
                             (
                                product_promotion_period.date_end IS NULL OR CURRENT_DATE <= product_promotion_period.date_end
                             )
                        THEN true
                        ELSE false
                    END AS promotion_active
                ')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPeriod::class,
                    'product_promotion_period',
                    '
                        product_promotion_period.event = product_promotion.event',
                );
        }


        /** Фото продукта */

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            '
			product_modification_image.modification = product_modification.id AND
			product_modification_image.root = true
			',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id AND
			product_variation_image.root = true
			',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
			product_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductPhoto::class,
            'product_photo',
            '
			product_offer_images.name IS NULL AND
			product_photo.event = product.event AND
			product_photo.root = true
			',
        );

        $dbal->addSelect(
            "
			CASE
			 WHEN product_modification_image.name IS NOT NULL 
			 THEN CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name)
					
			   WHEN product_variation_image.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)
					
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)
					
			   WHEN product_photo.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
					
			   ELSE NULL
			END AS product_image
		",
        );

        /** Расширение файла */
        $dbal->addSelect(
            "
			CASE
                WHEN product_modification_image.ext IS NOT NULL AND product_modification_image.name IS NOT NULL 
                THEN product_modification_image.ext
					
			   WHEN product_variation_image.ext IS NOT NULL AND product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.ext
					
			   WHEN product_offer_images.ext IS NOT NULL AND product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.ext
					
			   WHEN product_photo.ext IS NOT NULL AND product_photo.name IS NOT NULL 
			   THEN product_photo.ext
					
			   ELSE NULL
			END AS product_image_ext
		",
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect(
            "
			CASE
			    WHEN product_modification_image.cdn IS NOT NULL AND product_modification_image.name IS NOT NULL 
			    THEN product_modification_image.cdn
					
			   WHEN product_variation_image.cdn IS NOT NULL AND product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.cdn
					
			   WHEN product_offer_images.cdn IS NOT NULL AND product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.cdn
					
			   WHEN product_photo.cdn IS NOT NULL AND product_photo.name IS NOT NULL 
			   THEN product_photo.cdn
					
			   ELSE NULL
			END AS product_image_cdn
		");


        /** Свойства, учавствующие в карточке */

        $dbal->leftJoin(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category',
        );


        $dbal
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event',
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->addSelect('category_trans.description AS category_desc')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local',
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event',
        );


        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id 
            AND category_section_field.card = TRUE',
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id 
            AND category_section_field_trans.local = :local',
        );


        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event 
            AND product_property.field = category_section_field.const',
        );


        /* Артикул продукта */

        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');


        /* Артикул продукта */

        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification.barcode IS NOT NULL 
			   THEN product_modification.barcode
			   
			   WHEN product_variation.barcode IS NOT NULL 
			   THEN product_variation.barcode
			   
			   WHEN product_offer.barcode IS NOT NULL 
			   THEN product_offer.barcode
			   
			   WHEN product_info.barcode IS NOT NULL 
			   THEN product_info.barcode
			   
			   ELSE NULL
			END AS product_barcode
		',
        );


        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT

				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort,
					'field_uid', category_section_field.id,
					'field_const', category_section_field.const,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)

		)
			AS category_section_field",
        );


        /**  Вес товара  */

        if(class_exists(BaksDevDeliveryTransportBundle::class))
        {

            $dbal
                ->addSelect('product_package.length AS product_parameter_length')
                ->addSelect('product_package.width AS product_parameter_width')
                ->addSelect('product_package.height AS product_parameter_height')
                ->addSelect('product_package.weight AS product_parameter_weight')
                ->leftJoin(
                    'product_modification',
                    DeliveryPackageProductParameter::class,
                    'product_package',
                    '
                    
                    product_package.product = product.id  

                    AND
                        
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN product_package.offer = product_offer.const
                            ELSE product_package.offer IS NULL
                        END
                            
                    AND 
                    
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN product_package.variation = product_variation.const
                            ELSE product_package.variation IS NULL
                        END
                        
                    AND
                    
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN product_package.modification = product_modification.const
                            ELSE product_package.modification IS NULL
                        END

            ');

        }


        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->allGroupByExclude();

        //        return $dbal
        //            ->enableCache('products-product', 86400)
        //            ->fetchAllAssociative();

        return $dbal
            ->enableCache('products-product', '1 day')
            ->fetchAllHydrate(AllProductsByCategoryResult::class);

    }


    /** @deprecated */
    public function fetchAllProductByCategoryAssociative(
        CategoryProductUid|string $category,
        string $expr = 'AND',
    ): PaginatorInterface
    {

        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /** Категория */
        $dbal
            ->from(CategoryProduct::class, 'category')
            ->where('category.id = :category')
            ->setParameter('category', $category, CategoryProductUid::TYPE);

        $dbal->join(
            'category',
            CategoryProductEvent::class,
            'category_event',
            'category_event.id = category.event OR category_event.parent = category.id',
        );

        $dbal
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
                'category_event',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category_event.id',
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category_event',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category_event.id AND category_trans.local = :local',
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event',
        );

        /** Свойства, участвующие в карточке */
        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.card = TRUE OR category_section_field.photo = TRUE OR category_section_field.name = TRUE)',
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local',
        );

        /** Категория продукта */
        $dbal
            ->leftJoin(
                'category',
                ProductCategory::class,
                'product_category',
                'product_category.category = category_event.category',
            );

        /** Продукты */
        $dbal
            ->addSelect('product.id')
            ->addSelect('product.event')
            ->join(
                'product_category',
                Product::class,
                'product',
                'product.event = product_category.event',
            );

        $dbal
            ->addSelect('product_active.active_from')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE AND
                    (product_active.active_to IS NULL OR product_active.active_to > NOW())
                ');

        $dbal->leftJoin(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event',
        );

        $dbal->leftJoin(
            'product',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.const',
        );

        /** Агрегация свойств категории */
        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_photo', category_section_field.photo,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)
			
		)
			AS category_section_field",
        );

        /** ФИЛЬТР СВОЙСТВ */
        if($this->property)
        {
            if($expr === 'AND')
            {
                foreach($this->property as $type => $item)
                {
                    if($item === true)
                    {
                        $item = 'true';
                    }

                    $prepareKey = uniqid('key_', false);
                    $prepareValue = uniqid('val_', false);
                    $aliase = uniqid('aliase', false);

                    $ProductCategorySectionFieldUid = new CategoryProductSectionFieldUid($type);
                    $ProductPropertyJoin = $aliase.'.field = :'.$prepareKey.' AND '.$aliase.'.value = :'.$prepareValue;

                    $dbal->setParameter(
                        $prepareKey,
                        $ProductCategorySectionFieldUid,
                        CategoryProductSectionFieldUid::TYPE,
                    );
                    $dbal->setParameter($prepareValue, $item);

                    $dbal->join(
                        'product',
                        ProductProperty::class,
                        $aliase,
                        $aliase.'.event = product.event '.$expr.' '.$ProductPropertyJoin,
                    );
                }
            }
            else
            {

                foreach($this->property as $type => $item)
                {
                    if($item === true)
                    {
                        $item = 'true';
                    }

                    $prepareKey = uniqid('', false);
                    $prepareValue = uniqid('', false);

                    $ProductCategorySectionFieldUid = new CategoryProductSectionFieldUid($type);
                    $ProductPropertyJoin[] = 'product_property_filter.field = :'.$prepareKey.' AND product_property_filter.value = :'.$prepareValue;

                    $dbal->setParameter(
                        $prepareKey,
                        $ProductCategorySectionFieldUid,
                        CategoryProductSectionFieldUid::TYPE,
                    );
                    $dbal->setParameter($prepareValue, $item);

                }

                $dbal->join(
                    'product',
                    ProductProperty::class,
                    'product_property_filter',
                    'product_property_filter.event = product.event AND '.implode(' '.$expr.' ', $ProductPropertyJoin),
                );
            }
        }

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local',
            );

        $dbal
            //            ->addSelect('product_desc.preview AS product_preview')
            //            ->addSelect('product_desc.description AS product_description')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.device = :device ',
            )
            ->setParameter('device', 'pc');

        /** Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency');

        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url')
            ->addSelect('product_info.sort')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );

        /** OFFER */
        if($this->filter?->getOffer())
        {
            $dbal->join(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event AND product_offer.value = :offer',
            );

            $dbal->setParameter('offer', $this->filter->getOffer());
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );
        }

        /**  Тип торгового предложения */
        $dbal
            ->addSelect('category_offer.card AS category_offer_card ')
            ->addSelect('category_offer.reference AS product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );

        /** Группировка в зависимости от настройки группировки торгового предложения */
        $dbal
            ->addSelect(
                '
                CASE
                    WHEN category_offer.card IS NOT NULL AND category_offer.card IS TRUE
                    THEN product_offer.value
                    ELSE NULL
                END AS product_offer_value
            ')
            ->addGroupBy('
                CASE
                    WHEN category_offer.card IS NOT NULL AND category_offer.card IS TRUE
                    THEN product_offer.value
                END
            ');

        /** Цена торгового предложения */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id',
            )
            ->addGroupBy('product_offer_price.currency');

        $dbal
            //            ->addSelect('SUM(product_offer_quantity.quantity) AS product_offer_quantity')
            ->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id',
            );

        /** VARIATION */
        if($this->filter?->getVariation())
        {
            $dbal->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id AND product_variation.value = :variation',
            );

            $dbal->setParameter('variation', $this->filter->getVariation());
        }
        else
        {
            $dbal->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );
        }

        /** Тип множественного варианта */
        $dbal
            ->addSelect('category_variation.card AS category_variation_card')
            ->addSelect('category_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation',
            );

        /** Группировка в зависимости от настройки группировки множественного варианта */
        $dbal
            ->addSelect(
                '
                CASE
                    WHEN category_variation.card IS NOT NULL AND category_variation.card IS TRUE
                    THEN product_variation.value
                    ELSE NULL
                END AS product_variation_value')
            ->addGroupBy('
                CASE
                    WHEN category_variation.card IS NOT NULL AND category_variation.card IS TRUE
                    THEN product_variation.value
                END',
            );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'category_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        )
            ->addGroupBy('product_variation_price.currency');

        $dbal
            //            ->addSelect('SUM(product_variation_quantity.quantity) AS product_variation_quantity')
            ->leftJoin(
                'category_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id',
            );

        /** MODIFICATION */
        if($this->filter?->getModification())
        {
            $dbal->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id AND product_modification.value = :modification',
            );

            $dbal->setParameter('modification', $this->filter->getModification());
        }
        else
        {
            $dbal->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id',
            );
        }

        /** Тип модификации множественного варианта */
        $dbal
            ->addSelect('category_modification.card AS category_modification_card ')
            ->addSelect('category_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification',
            );

        /** Группировка в зависимости от настройки группировки модификации множественного варианта */
        $dbal
            ->addSelect('
                CASE
                    WHEN category_modification.card IS NOT NULL AND category_modification.card IS TRUE
                    THEN product_modification.value
                    ELSE NULL
                END
            AS product_modification_value
            ')
            ->addGroupBy('
                CASE
                    WHEN category_modification.card IS NOT NULL AND category_modification.card IS TRUE
                    THEN product_modification.value
                END',
            );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        )
            ->addGroupBy('product_modification_price.currency');

        $dbal
            //            ->addSelect('SUM(product_modification_quantity.quantity) AS product_modification_quantity')
            ->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id',
            )
            ->addGroupBy('product_modification_price.currency');

        /** Product Invariable */
        $dbal
            ->leftJoin(
                'product_modification',
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
                                (product_variation.const IS NOT NULL AND product_invariable.variation = product_variation.const) OR
                                (product_variation.const IS NULL AND product_invariable.variation IS NULL)
                            )
                           AND
                           (
                                (product_modification.const IS NOT NULL AND product_invariable.modification = product_modification.const) OR
                                (product_modification.const IS NULL AND product_invariable.modification IS NULL)
                           )
                    ');

        /** Агрегация данных о продукте */
        $dbal->addSelect(
            "JSON_AGG
			( DISTINCT

					JSONB_BUILD_OBJECT
					(
						/* свойства для сортирвоки JSON */
						'0', CONCAT(product_offer.value, product_variation.value, product_modification.value),

						'offer_uid', product_offer.id, /* значение торгового предложения */
						'offer_value', product_offer.value, /* значение торгового предложения */
						'offer_reference', category_offer.reference, /* тип (field) торгового предложения */
						'offer_postfix', product_offer.postfix, /* значение торгового предложения */
						'offer_article', product_offer.article, /* артикул торгового предложения */

						'variation_uid', product_variation.id, /* значение множественного варианта */
						'variation_value', product_variation.value, /* значение множественного варианта */
						'variation_reference', category_variation.reference, /* тип (field) множественного варианта */
						'variation_postfix', product_variation.postfix, /* значение множественного варианта */
						'variation_article', category_variation.article, /* валюта множественного варианта */

						'modification_uid', product_modification.id, /* значение модификации */
						'modification_value', product_modification.value, /* значение модификации */
						'modification_reference', category_modification.reference, /* тип (field) модификации */
						'modification_postfix', product_modification.postfix, /* значение модификации */
						'modification_article', category_modification.article, /* артикул модификации */

						'product_invariable', product_invariable.id
					)
			)
			AS product_offers",
        );

        /** Фото продукта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            '
                product_modification_image.modification = product_modification.id AND
                product_modification_image.root = true',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
                product_variation_image.variation = product_variation.id AND
                product_variation_image.root = true',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
                product_variation_image.name IS NULL AND
                product_offer_images.offer = product_offer.id AND
                product_offer_images.root = true',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductPhoto::class,
            'product_photo',
            '
                product_offer_images.name IS NULL AND
                product_photo.event = product.event AND
                product_photo.root = true
			');

        /** Агрегация фото продуктов из offer, variation, modification */
        $dbal->addSelect(
            "
            JSON_AGG
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
        
                            WHEN product_variation_image.ext IS NOT NULL
                            THEN JSONB_BUILD_OBJECT
                                (
                                    'img_root', product_variation_image.root,
                                    'img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                                    'img_ext', product_variation_image.ext,
                                    'img_cdn', product_variation_image.cdn
                                )
        
                            WHEN product_modification_image.ext IS NOT NULL
                            THEN JSONB_BUILD_OBJECT
                                (
                                    'img_root', product_modification_image.root,
                                    'img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                                    'img_ext', product_modification_image.ext,
                                    'img_cdn', product_modification_image.cdn
                                )
        
                            WHEN product_photo.ext IS NOT NULL
                            THEN JSONB_BUILD_OBJECT
                                (
                                    'img_root', product_photo.root,
                                    'img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                                    'img_ext', product_photo.ext,
                                    'img_cdn', product_photo.cdn
                                )
                            ELSE NULL
                        END
                    )
                    AS product_root_images",
        );

        /** Минимальная стоимость продукта */
        $dbal->addSelect("CASE

                /* СТОИМОСТЬ МОДИФИКАЦИИ */
                WHEN (ARRAY_AGG(
                                    DISTINCT product_modification_price.price ORDER BY product_modification_price.price
                                 )
                                 FILTER
                                 (
                                    WHERE  product_modification_price.price > 0
                                 )
                             )[1] > 0

                             THEN (ARRAY_AGG(
                                    DISTINCT product_modification_price.price ORDER BY product_modification_price.price
                                 )
                                 FILTER
                                 (
                                    WHERE  product_modification_price.price > 0
                                 )
                             )[1]

                 /* СТОИМОСТЬ ВАРИАНТА */
                 WHEN (ARRAY_AGG(
                                    DISTINCT product_variation_price.price ORDER BY product_variation_price.price
                                 )
                                 FILTER
                                 (
                                    WHERE  product_variation_price.price > 0
                                 )
                             )[1] > 0

                 THEN (ARRAY_AGG(
                                    DISTINCT product_variation_price.price ORDER BY product_variation_price.price
                                 )
                                 FILTER
                                 (
                                    WHERE  product_variation_price.price > 0
                                 )
                             )[1]


                 /* СТОИМОСТЬ ТП */
                    WHEN (ARRAY_AGG(
                                    DISTINCT product_offer_price.price ORDER BY product_offer_price.price
                                 )
                                 FILTER
                                 (
                                    WHERE  product_offer_price.price > 0
                                 )
                             )[1] > 0

                    THEN (ARRAY_AGG(
                                    DISTINCT product_offer_price.price ORDER BY product_offer_price.price
                                 )
                                 FILTER
                                 (
                                    WHERE  product_offer_price.price > 0
                                 )
                             )[1]


        			   WHEN product_price.price IS NOT NULL
        			   THEN product_price.price

        			   ELSE NULL
        			END AS product_price
        		");

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
        $dbal->addSelect(
            "
			CASE

			   WHEN MIN(product_modification_price.price) IS NOT NULL AND MIN(product_modification_price.price) > 0
			   THEN product_modification_price.currency

			   WHEN MIN(product_variation_price.price) IS NOT NULL AND MIN(product_variation_price.price) > 0
			   THEN product_variation_price.currency

			   WHEN MIN(product_offer_price.price) IS NOT NULL AND MIN(product_offer_price.price) > 0
			   THEN product_offer_price.currency

			   WHEN product_price.price IS NOT NULL
			   THEN product_price.currency

			   ELSE NULL

			END AS product_currency
		",
        );

        /** Количественный учет */
        $dbal->addSelect("
			CASE

			   WHEN SUM(product_modification_quantity.quantity) > 0
			   THEN SUM(product_modification_quantity.quantity)

			   WHEN SUM(product_variation_quantity.quantity) > 0
			   THEN SUM(product_variation_quantity.quantity)

			   WHEN SUM(product_offer_quantity.quantity) > 0
			   THEN SUM(product_offer_quantity.quantity)

			   WHEN SUM(product_price.quantity) > 0
			   THEN SUM(product_price.quantity)

			   ELSE 0

			END AS product_quantity
		");

        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->addOrderBy('SUM(product_modification_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_variation_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_offer_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_price.quantity)', 'DESC');

        $dbal->allGroupByExclude();

        return $this->paginator->fetchAllAssociative($dbal);
    }


}
