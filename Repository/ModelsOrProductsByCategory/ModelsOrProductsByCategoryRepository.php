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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ModelsOrProductsByCategory;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
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
use BaksDev\Products\Product\Entity\Description\ProductDescription;
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
use InvalidArgumentException;

final class ModelsOrProductsByCategoryRepository implements ModelsOrProductsByCategoryInterface
{
    private CategoryProductUid|false $category = false;

    private ProductCategoryFilterDTO|null $filter = null;

    private array|null $property = null;

    private int|false $maxResult = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}

    /** Максимальное количество записей в результате */
    public function maxResult(int $max): self
    {
        $this->maxResult = $max;
        return $this;
    }

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

    /** Категория продуктов */
    public function category(CategoryProduct|CategoryProductUid|string $category): self
    {
        if(is_string($category))
        {
            $category = new CategoryProductUid($category);
        }

        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        $this->category = $category;

        return $this;
    }

    public function findAll(string $expr = 'AND'): array|false
    {
        if(false == $this->maxResult)
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $maxResult');
        }

        $dbal = $this->builder($expr);

        $dbal->setMaxResults($this->maxResult);

        $result = $dbal
            ->enableCache('products-product', 86400)
            ->fetchAllAssociative();

        return empty($result) ? false : $result;
    }

    public function findPaginator(string $expr = 'AND'): PaginatorInterface
    {
        $dbal = $this->builder($expr);
        return $this->paginator->fetchAllAssociative($dbal);
    }

    /** Билдер запроса */
    private function builder(string $expr): DBALQueryBuilder
    {

        if(false == $this->category)
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $category');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local'
            );

        $dbal
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.device = :device AND product_desc.local = :local'
            )
            ->setParameter('device', 'pc');

        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url')
            ->addSelect('product_info.sort')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
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

        /** Цена PRODUCT */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event AND product_price.price > 0'
        )
            ->addGroupBy('product_price.price')
            ->addGroupBy('product_price.currency');

        /** OFFER */
        if($this->filter?->getOffer())
        {
            $dbal->join(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event AND product_offer.value = :offer'
            );

            $dbal->setParameter('offer', $this->filter->getOffer());
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event'
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
                'category_offer.id = product_offer.category_offer'
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

        /** Агрегация торговых предложений */
        $dbal->addSelect(
            "
            JSON_AGG( DISTINCT
                CASE
                    WHEN product_offer.value IS NOT NULL THEN
                        JSONB_BUILD_OBJECT (
                            'offer_id', product_offer.id,
                            'offer_value', product_offer.value,
                            'offer_postfix', product_offer.postfix
                        )
                    ELSE NULL
                END
            ) AS offer_value_agg"
        );

        /** Цена торгового предложения */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id AND product_offer_price.price > 0'
            )
            //            ->addGroupBy('product_offer_price.price')
            ->addGroupBy('product_offer_price.currency');

        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id'
            );

        /** VARIATION */
        if($this->filter?->getVariation())
        {
            $dbal->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id AND product_variation.value = :variation'
            );

            $dbal->setParameter('variation', $this->filter->getVariation());
        }
        else
        {
            $dbal->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
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
                'category_variation.id = product_variation.category_variation'
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
                END'
            );

        /** Агрегация множественных вариантов */
        $dbal->addSelect(
            "
             JSON_AGG( DISTINCT
                 CASE
                     WHEN product_variation.value IS NOT NULL THEN
                         JSONB_BUILD_OBJECT (
                             'variation_id', product_variation.id,
                             'variation_value', product_variation.value,
                             'variation_postfix', product_variation.postfix
                         )
                     ELSE NULL
                 END
             ) AS variation_value_agg"
        );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'category_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id AND product_variation_price.price > 0'
        )
            ->addGroupBy('product_variation_price.currency');

        $dbal
            ->leftJoin(
                'category_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id'
            );

        /** MODIFICATION */
        if($this->filter?->getModification())
        {
            $dbal->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id AND product_modification.value = :modification'
            );

            $dbal->setParameter('modification', $this->filter->getModification());
        }
        else
        {
            $dbal->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id'
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
                'category_modification.id = product_modification.category_modification'
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
                END'
            );

        /** Агрегация модификация множественных вариантов */
        $dbal->addSelect(
            "
            JSON_AGG( DISTINCT
                CASE
                    WHEN product_modification.value IS NOT NULL THEN
                        JSONB_BUILD_OBJECT (
                            'modification_id', product_modification.id,
                            'modification_value', product_modification.value,
                            'modification_postfix', product_modification.postfix
                        )
                    ELSE NULL
                END
            ) AS modification_value_agg"
        );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id AND product_modification_price.price > 0'
        )
            ->addGroupBy('product_modification_price.currency');

        /** Количество множественного варианта */
        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id'
            )
            ->addGroupBy('product_modification_price.currency');

        /** Product Invariable */
        $dbal
            ->leftJoin(
                'product_variation',
                ProductInvariable::class,
                'product_invariable',
                '
                            product_invariable.product = product.id
                            AND
                                CASE 
                                    WHEN product_offer.const IS NOT NULL THEN product_invariable.offer = product_offer.const
                                    ELSE product_invariable.offer IS NULL
                                END
                            AND 
                                CASE
                                    WHEN product_variation.const IS NOT NULL THEN product_invariable.variation = product_variation.const
                                    ELSE product_invariable.variation IS NULL
                                END
                            AND
                                CASE
                                    WHEN product_modification.const IS NOT NULL THEN product_invariable.modification = product_modification.const
                                    ELSE product_invariable.modification IS NULL
                                END
                        ');

        /** Агрегация Invariable */
        $dbal->addSelect('JSON_AGG( DISTINCT product_invariable.id) AS invariable');

        /** Фото продукта */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event AND product_photo.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id AND product_variation_image.root = true'
        );

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id AND product_modification_image.root = true'
        );

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
                    AS product_root_images"
        );

        /** Категория */
        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true'
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
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event AND category_info.active IS TRUE'
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );

        /** Свойства, участвующие в карточке */
        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event'
        );

        $dbal
            ->leftJoin(
                'category_section',
                CategoryProductSectionField::class,
                'category_section_field',
                'category_section_field.section = category_section.id AND
                          (
                            category_section_field.card = TRUE OR
                            category_section_field.photo = TRUE OR
                            category_section_field.name = TRUE
                          )
                      '
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

        /** Агрегация свойств для карточки */
        $dbal->addSelect(
            "JSON_AGG
        		( DISTINCT

        				JSONB_BUILD_OBJECT
        				(
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
                        CategoryProductSectionFieldUid::TYPE
                    );
                    $dbal->setParameter($prepareValue, $item);

                    $dbal->join(
                        'product',
                        ProductProperty::class,
                        $aliase,
                        $aliase.'.event = product.event '.$expr.' '.$ProductPropertyJoin
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

        /** Минимальная стоимость */
        $dbal->addSelect('
            COALESCE(
                MIN(product_modification_price.price),
                MIN(product_variation_price.price),
                MIN(product_offer_price.price),
                MIN(product_price.price)
            ) AS product_price
		');

        /** Валюта */
        $dbal->addSelect('
            CASE

			   WHEN MIN(product_modification_price.price) IS NOT NULL
			   THEN product_modification_price.currency

			   WHEN MIN(product_variation_price.price) IS NOT NULL
			   THEN product_variation_price.currency

			   WHEN MIN(product_offer_price.price) IS NOT NULL
			   THEN product_offer_price.currency

			   WHEN product_price.price IS NOT NULL
			   THEN product_price.currency

			   ELSE NULL

			END AS product_currency
		');

        /** Количественный учет */
        $dbal->addSelect('
			CASE

			   WHEN SUM(product_modification_quantity.quantity) - SUM(product_modification_quantity.reserve) > 0
			   THEN SUM(product_modification_quantity.quantity) - SUM(product_modification_quantity.reserve)

			   WHEN SUM(product_variation_quantity.quantity) - SUM(product_variation_quantity.reserve) > 0
			   THEN SUM(product_variation_quantity.quantity) - SUM(product_variation_quantity.reserve)

			   WHEN SUM(product_offer_quantity.quantity) - SUM(product_offer_quantity.reserve) > 0
			   THEN SUM(product_offer_quantity.quantity) - SUM(product_offer_quantity.reserve)

			   WHEN SUM(product_price.quantity) - SUM(product_price.reserve) > 0
			   THEN SUM(product_price.quantity) - SUM(product_price.reserve)

			   ELSE 0

			END AS product_quantity
		');

        if($this->category)
        {
            $dbal
                ->andWhere('product_event_category.category = :category')
                ->setParameter('category', $this->category, CategoryProductUid::TYPE);
        }

        $dbal->allGroupByExclude();
        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->addOrderBy('SUM(product_modification_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_variation_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_offer_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_price.quantity)', 'DESC');

        return $dbal;
    }
}