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

namespace BaksDev\Products\Product\Repository\ProductByVariation;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;

final class ProductByVariationRepository implements ProductByVariationInterface
{
    public function __construct(private readonly ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Метод возвращает массив идентификаторов продукта
     */
    public function getProductByVariationOrNull(ProductVariationUid|ProductVariationConst $variation): ?array
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        if($variation instanceof ProductVariationConst)
        {
            $qb
                ->addSelect('variation.id AS variation_id')
                ->from(ProductVariation::class, 'variation')
                ->where('variation.const = :const')
                ->setParameter(
                    key: 'const',
                    value: $variation,
                    type: ProductVariationConst::TYPE
                );
        }

        if($variation instanceof ProductVariationUid)
        {
            $qb
                ->from(ProductVariation::class, 'var')
                ->where('var.id = :variation')
                ->setParameter(
                    key: 'variation',
                    value: $variation,
                    type: ProductVariationUid::TYPE
                );

            $qb
                ->join(
                    ProductVariation::class,
                    'variation',
                    'WITH',
                    'variation.const = var.const'
                );
        }

        $qb
            ->addSelect('offer.id AS offer_id')
            ->join(
                ProductOffer::class,
                'offer',
                'WITH',
                'offer.id = variation.offer'
            );

        $qb
            ->addSelect('event.id AS event_id')
            ->join(
                ProductEvent::class,
                'event',
                'WITH',
                'event.id = offer.event'
            );

        $qb
            ->addSelect('product.id AS product_id')
            ->join(
                Product::class,
                'product',
                'WITH',
                'product.event = event.id'
            );


        return $qb->getOneOrNullResult();
    }
}
