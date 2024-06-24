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

namespace BaksDev\Products\Product\Repository\CurrentProductEvent;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;

final readonly class CurrentProductEventRepository implements CurrentProductEventInterface
{
    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Метод возвращает активное событие продукции
     */
    public function findByProduct(Product|ProductUid|string $product): ?ProductEvent
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter('product', $product, ProductUid::TYPE);
        $qb
            ->select('event')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = product.event AND event.main = product.id'
            );


        return $qb->getOneOrNullResult();
    }

    /**
     * Метод возвращает активное событие продукции по идентификатору события
     */
    public function findByEvent(ProductEvent|ProductEventUid|string $last): ?ProductEvent
    {
        if(is_string($last))
        {
            $last = new ProductEventUid($last);
        }

        if($last instanceof ProductEvent)
        {
            $last = $last->getId();
        }

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(ProductEvent::class, 'last')
            ->where('last.id = :last')
            ->setParameter('last', $last, ProductEventUid::TYPE);

        $qb
            ->join(
                Product::class,
                'product',
                'WITH',
                'product.id = last.main'
            );

        $qb
            ->select('event')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = product.event'
            );


        return $qb->getOneOrNullResult();
    }

}
