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

namespace BaksDev\Products\Product\Repository\ProductQuantity;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;

final class ProductModificationQuantityRepository implements ProductModificationQuantityInterface
{
    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    /** Метод возвращает количественный учет модификации множественного варианта */
    public function getProductModificationQuantity(
        ProductUid $product,
        ProductOfferConst $offer,
        ProductVariationConst $variation,
        ProductModificationConst $modification
    ): ?ProductModificationQuantity
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter(
                key: 'product',
                value: $product,
                type: ProductUid::TYPE
            );

        $qb->join(
            ProductEvent::class,
            'event',
            'WITH',
            'event.id = product.event'
        );

        // Торговое предложение

        $qb
            ->join(
                ProductOffer::class,
                'offer',
                'WITH',
                'offer.event = event.id AND offer.const = :offer_const'
            )
            ->setParameter(
                key: 'offer_const',
                value: $offer,
                type: ProductOfferConst::TYPE
            );

        // Множественный вариант

        $qb
            ->join(
                ProductVariation::class,
                'variation',
                'WITH',
                'variation.offer = offer.id AND variation.const = :variation_const'
            )
            ->setParameter(
                key: 'variation_const',
                value: $variation,
                type: ProductVariationConst::TYPE
            );

        // Модификация множественного варианта

        $qb
            ->join(
                ProductModification::class,
                'modification',
                'WITH',
                'modification.variation = variation.id AND modification.const = :modification_const'
            )
            ->setParameter(
                key: 'modification_const',
                value: $modification,
                type: ProductModificationConst::TYPE
            );

        $qb
            ->select('quantity')
            ->leftJoin(
                ProductModificationQuantity::class,
                'quantity',
                'WITH',
                'quantity.modification = modification.id'
            );


        // Только если у модификации указан количественный учет

        $qb->join(
            CategoryProductModification::class,
            'category_modification',
            'WITH',
            'category_modification.id = modification.categoryModification AND category_modification.quantitative = true'
        );

        return $qb->getOneOrNullResult();
    }
}
