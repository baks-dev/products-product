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

namespace BaksDev\Products\Product\Repository\ProductByVariation;

use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use Doctrine\ORM\EntityManagerInterface;

final class ProductByVariation implements ProductByVariationInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Метод возвращает массив идентификаторов продукта
     */
    public function getProductByVariationConstOrNull(ProductVariationConst $const) : ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('product.id AS product_id');
        $qb->addSelect('event.id AS event_id');
        $qb->addSelect('offer.id AS offer_id');
        $qb->addSelect('variation.id AS variation_id');

        $qb->from(ProductVariation::class, 'variation');

        $qb->join(ProductOffer::class,
            'offer',
            'WITH',
            'offer.id = variation.offer'
        );

        $qb->join(ProductEvent::class,
            'event',
            'WITH',
            'event.id = offer.event'
        );

        $qb->join(Product::class,
            'product',
            'WITH',
            'product.event = event.id'
        );

        $qb->where('variation.const = :const');
        $qb->setParameter('const', $const, ProductVariationConst::TYPE);

        return $qb->getQuery()->getOneOrNullResult();
    }
}