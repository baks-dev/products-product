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

namespace BaksDev\Products\Product\Repository\ProductsDetailByUids\Tests;

use BaksDev\Products\Product\Repository\ProductsDetailByUids\ProductsDetailByUidsRepository;
use BaksDev\Products\Product\Repository\ProductsDetailByUids\ProductsDetailByUidsResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class ProductsDetailByUidsRepositoryTest extends KernelTestCase
{
    public function testFindAll()
    {
        /** @var ProductsDetailByUidsRepository $repository */
        $repository = self::getContainer()->get(ProductsDetailByUidsRepository::class);

        $events = [ProductEventUid::TEST];
        $offers = [ProductOfferUid::TEST];
        $variations = [ProductVariationUid::TEST];
        $modifications = [ProductModificationUid::TEST];

        $results = $repository
            ->events($events)
            ->offers($offers)
            ->variations($variations)
            ->modifications($modifications)
            ->findAll();

        if(false === $results)
        {
            self::assertTrue(true);
            return;
        }

        /** @var ProductsDetailByUidsResult $item */
        foreach($results as $item)
        {
            self::assertInstanceOf(ProductsDetailByUidsResult::class, $item);
            self::assertInstanceOf(ProductUid::class, $item->getProductId());
            self::assertInstanceOf(ProductOfferUid::class, $item->getProductOfferUid());
            self::assertInstanceOf(ProductVariationUid::class, $item->getProductVariationUid());
            self::assertInstanceOf(ProductModificationUid::class, $item->getProductModificationUid());
        }

        $result = $repository->toArray();

//        dd($result);

        self::assertTrue(true);
    }
}