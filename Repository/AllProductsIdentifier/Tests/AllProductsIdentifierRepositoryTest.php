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

namespace BaksDev\Products\Product\Repository\AllProductsIdentifier\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\ProductsIdentifierResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
class AllProductsIdentifierRepositoryTest extends KernelTestCase
{
    private static ProductsIdentifierResult|false $data;

    public static function setUpBeforeClass(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier->findAll();

        foreach($result as $data)
        {
            if(false === ($data->getProductModificationId() instanceof ProductModificationUid))
            {
                continue;
            }

            self::assertInstanceOf(ProductUid::class, $data->getProductId());
            self::assertInstanceOf(ProductEventUid::class, $data->getProductEvent());

            $data->getProductOfferId() ? self::assertInstanceOf(ProductOfferUid::class, $data->getProductOfferId()) : self::assertFalse($data->getProductOfferId());
            $data->getProductOfferConst() ? self::assertInstanceOf(ProductOfferConst::class, $data->getProductOfferConst()) : self::assertFalse($data->getProductOfferConst());

            $data->getProductVariationId() ? self::assertInstanceOf(ProductVariationUid::class, $data->getProductVariationId()) : self::assertFalse($data->getProductVariationId());
            $data->getProductVariationConst() ? self::assertInstanceOf(ProductVariationConst::class, $data->getProductVariationConst()) : self::assertFalse($data->getProductVariationConst());

            $data->getProductModificationId() ? self::assertInstanceOf(ProductModificationUid::class, $data->getProductModificationId()) : self::assertFalse($data->getProductModificationId());
            $data->getProductModificationConst() ? self::assertInstanceOf(ProductModificationConst::class, $data->getProductModificationConst()) : self::assertFalse($data->getProductModificationConst());

            self::$data = $data;

            break;
        }

        self::assertNotFalse(self::$data, 'Не найдено ни одной продукции с полной вложенностью');
    }

    public function testProductCase(): void
    {
        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forProduct(self::$data->getProductId())
            ->findAll();

        foreach($result as $data)
        {
            self::assertInstanceOf(ProductUid::class, $data->getProductId());
            self::assertTrue($data->getProductId()->equals(self::$data->getProductId()));

            self::assertInstanceOf(ProductEventUid::class, $data->getProductEvent());
            self::assertTrue($data->getProductEvent()->equals(self::$data->getProductEvent()));
        }

        self::assertTrue(true);
    }

    public function testOfferCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forOfferConst(self::$data->getProductOfferConst())
            ->findAll();

        foreach($result as $data)
        {
            self::assertInstanceOf(ProductUid::class, $data->getProductId());
            self::assertTrue($data->getProductId()->equals(self::$data->getProductId()));

            self::assertInstanceOf(ProductEventUid::class, $data->getProductEvent());
            self::assertTrue($data->getProductEvent()->equals(self::$data->getProductEvent()));

            self::assertInstanceOf(ProductOfferUid::class, $data->getProductOfferId());
            self::assertTrue($data->getProductOfferId()->equals(self::$data->getProductOfferId()));

            self::assertInstanceOf(ProductOfferConst::class, $data->getProductOfferConst());
            self::assertTrue($data->getProductOfferConst()->equals(self::$data->getProductOfferConst()));
        }


        self::assertTrue(true);
    }


    public function testVariationCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forVariationConst(self::$data->getProductVariationConst())
            ->findAll();

        foreach($result as $data)
        {
            self::assertInstanceOf(ProductUid::class, $data->getProductId());
            self::assertTrue($data->getProductId()->equals(self::$data->getProductId()));


            self::assertInstanceOf(ProductEventUid::class, $data->getProductEvent());
            self::assertTrue($data->getProductEvent()->equals(self::$data->getProductEvent()));


            self::assertInstanceOf(ProductOfferUid::class, $data->getProductOfferId());
            self::assertTrue($data->getProductOfferId()->equals(self::$data->getProductOfferId()));

            self::assertInstanceOf(ProductOfferConst::class, $data->getProductOfferConst());
            self::assertTrue($data->getProductOfferConst()->equals(self::$data->getProductOfferConst()));


            self::assertInstanceOf(ProductVariationUid::class, $data->getProductVariationId());
            self::assertTrue($data->getProductVariationId()->equals(self::$data->getProductVariationId()));

            self::assertInstanceOf(ProductVariationConst::class, $data->getProductVariationConst());
            self::assertTrue($data->getProductVariationConst()->equals(self::$data->getProductVariationConst()));
        }


        self::assertTrue(true);
    }


    public function testModificationCase(): void
    {

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);
        $result = $AllProductsIdentifier
            ->forModificationConst(self::$data->getProductModificationConst())
            ->findAll();

        foreach($result as $data)
        {
            self::assertInstanceOf(ProductUid::class, $data->getProductId());
            self::assertTrue($data->getProductId()->equals(self::$data->getProductId()));


            self::assertInstanceOf(ProductEventUid::class, $data->getProductEvent());
            self::assertTrue($data->getProductEvent()->equals(self::$data->getProductEvent()));


            self::assertInstanceOf(ProductOfferUid::class, $data->getProductOfferId());
            self::assertTrue($data->getProductOfferId()->equals(self::$data->getProductOfferId()));

            self::assertInstanceOf(ProductOfferConst::class, $data->getProductOfferConst());
            self::assertTrue($data->getProductOfferConst()->equals(self::$data->getProductOfferConst()));


            self::assertInstanceOf(ProductVariationUid::class, $data->getProductVariationId());
            self::assertTrue($data->getProductVariationId()->equals(self::$data->getProductVariationId()));

            self::assertInstanceOf(ProductVariationConst::class, $data->getProductVariationConst());
            self::assertTrue($data->getProductVariationConst()->equals(self::$data->getProductVariationConst()));


            self::assertInstanceOf(ProductModificationUid::class, $data->getProductModificationId());
            self::assertTrue($data->getProductModificationId()->equals(self::$data->getProductModificationId()));

            self::assertInstanceOf(ProductModificationConst::class, $data->getProductModificationConst());
            self::assertTrue($data->getProductModificationConst()->equals(self::$data->getProductModificationConst()));
        }


        self::assertTrue(true);
    }

}
