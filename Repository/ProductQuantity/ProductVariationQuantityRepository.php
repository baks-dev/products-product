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

namespace BaksDev\Products\Product\Repository\ProductQuantity;

use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use Doctrine\ORM\EntityManagerInterface;

final class ProductVariationQuantityRepository implements ProductVariationQuantityInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** Метод возвращает количественный учет множественного варианта */
    public function getProductVariationQuantity(
        ProductUid $product,
        ProductOfferConst $offer,
        ProductVariationConst $variation
    ): ?ProductVariationQuantity {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('quantity');

        $qb->from(Product::class, 'product');
        $qb->where('product.id = :product');
        $qb->setParameter('product', $product, ProductUid::TYPE);

        $qb->join(
            ProductEvent::class,
            'event',
            'WITH',
            'event.id = product.event'
        );

        // Торговое предложение

        $qb->join(
            ProductOffer::class,
            'offer',
            'WITH',
            'offer.event = event.id AND offer.const = :offer_const'
        );

        $qb->setParameter('offer_const', $offer, ProductOfferConst::TYPE);

        // Множественный вариант

        $qb->join(
            ProductVariation::class,
            'variation',
            'WITH',
            'variation.offer = offer.id AND variation.const = :variation_const'
        );

        $qb->setParameter('variation_const', $variation, ProductVariationConst::TYPE);


        $qb->leftJoin(
            ProductVariationQuantity::class,
            'quantity',
            'WITH',
            'quantity.variation = variation.id'
        );


        // Только если у модификации указан количественный учет

        $qb->join(
            CategoryProductVariation::class,
            'category_variation',
            'WITH',
            'category_variation.id = variation.categoryVariation AND category_variation.quantitative = true'
        );

        return $qb->getQuery()->getOneOrNullResult();
    }
}
