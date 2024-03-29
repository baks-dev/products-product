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

use BaksDev\Products\Category\Entity as CategoryEntity;
use BaksDev\Products\Product\Entity as ProductEntity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Doctrine\ORM\EntityManagerInterface;

final class ProductModificationQuantityRepository implements ProductModificationQuantityRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** Метод возвращает количественный учет модификации множественного варианта */
    public function getProductModificationQuantity(
        ProductUid               $product,
        ProductOfferConst        $offer,
        ProductVariationConst    $variation,
        ProductModificationConst $modification
    ): ?ProductModificationQuantity {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('quantity');

        $qb->from(ProductEntity\Product::class, 'product');

        $qb->where('product.id = :product');
        $qb->setParameter('product', $product, ProductUid::TYPE);

        $qb->join(
            ProductEntity\Event\ProductEvent::class,
            'event',
            'WITH',
            'event.id = product.event'
        );

        // Торговое предложение

        $qb->join(
            ProductEntity\Offers\ProductOffer::class,
            'offer',
            'WITH',
            'offer.event = event.id AND offer.const = :offer_const'
        );

        $qb->setParameter('offer_const', $offer, ProductOfferConst::TYPE);

        // Множественный вариант

        $qb->join(
            ProductEntity\Offers\Variation\ProductVariation::class,
            'variation',
            'WITH',
            'variation.offer = offer.id AND variation.const = :variation_const'
        );

        $qb->setParameter('variation_const', $variation, ProductVariationConst::TYPE);

        // Модификация множественного варианта

        $qb->join(
            ProductEntity\Offers\Variation\Modification\ProductModification::class,
            'modification',
            'WITH',
            'modification.variation = variation.id AND modification.const = :modification_const'
        );

        $qb->leftJoin(
            ProductEntity\Offers\Variation\Modification\Quantity\ProductModificationQuantity::class,
            'quantity',
            'WITH',
            'quantity.modification = modification.id'
        );

        $qb->setParameter('modification_const', $modification, ProductModificationConst::TYPE);

        // Только если у модификации указан количественный учет

        $qb->join(
            CategoryEntity\Offers\Variation\Modification\ProductCategoryModification::class,
            'category_modification',
            'WITH',
            'category_modification.id = modification.categoryModification AND category_modification.quantitative = true'
        );

        return $qb->getQuery()->getOneOrNullResult();
    }
}
