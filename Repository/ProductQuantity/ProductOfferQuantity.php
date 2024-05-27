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
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use Doctrine\ORM\EntityManagerInterface;

final class ProductOfferQuantity implements ProductOfferQuantityInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** Метод возвращает количественный учет торгового предложения */
    public function getProductOfferQuantity(
        ProductUid $product,
        ProductOfferConst $offer
    ): ? ProductEntity\Offers\Quantity\ProductOfferQuantity {
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


        $qb->leftJoin(
            ProductEntity\Offers\Quantity\ProductOfferQuantity::class,
            'quantity',
            'WITH',
            'quantity.offer = offer.id'
        );


        // Только если у оргового предложения указан количественный учет

        $qb->join(
            CategoryEntity\Offers\ProductCategoryOffers::class,
            'category_offer',
            'WITH',
            'category_offer.id = offer.categoryOffer AND category_offer.quantitative = true'
        );

        return $qb->getQuery()->getOneOrNullResult();
    }
}
