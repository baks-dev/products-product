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

namespace BaksDev\Products\Product\Repository\UnCompressProductsImages;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Product;
use Generator;


final class UnCompressProductsImagesRepository implements UnCompressProductsImagesInterface
{
    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод возвращает идентификаторы изображений без CDN и его класс Entity
     */
    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(Product::class, 'product');

        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event AND product_photo.cdn IS FALSE'
        );

        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id'
        );

        $dbal->leftJoin(
            'product_variation',
            ProductModification::class,
            'product_modification',
            'product_modification.variation = product_modification.id'
        );


        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.cdn IS FALSE'
        );


        $dbal->leftJoin(
            'product_variation',
            ProductVariationImage::class,
            'product_variation_images',
            'product_variation_images.variation = product_variation.id AND product_variation_images.cdn IS FALSE'
        );

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_images',
            'product_modification_images.modification = product_modification.id AND product_modification_images.cdn IS FALSE'
        );


        $dbal->addSelect("
            COALESCE(
                product_modification_images.id,
                product_variation_images.id,
                product_offer_images.id,
                product_photo.id
            ) AS identifier
		");

        $dbal->addSelect("
            COALESCE(
                product_modification_images.name,
                product_variation_images.name,
                product_offer_images.name,
                product_photo.name
            ) AS name
		");


        $dbal->addSelect(
            "
			CASE
			
			   WHEN product_modification_images.name IS NOT NULL 
			   THEN '".ProductModificationImage::class."'
			   
			   WHEN product_variation_images.name IS NOT NULL 
			   THEN '".ProductVariationImage::class."'
			   
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN '".ProductOfferImage::class."'
			   
			   WHEN product_photo.name IS NOT NULL 
			   THEN '".ProductPhoto::class."'
			   
			   ELSE NULL
			END AS entity
		"
        );

        $dbal->where('COALESCE(
                product_modification_images.name,
                product_variation_images.name,
                product_offer_images.name,
                product_photo.name
            ) IS NOT NULL');

        return $dbal->fetchAllHydrate(UnCompressProductsImagesResult::class);
    }
}