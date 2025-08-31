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

namespace BaksDev\Products\Product\Repository\ProductsByValues\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableResult;
use BaksDev\Products\Product\Repository\ProductsByValues\ProductsByValuesInterface;
use BaksDev\Products\Product\Repository\ProductsByValues\ProductsByValuesResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewAdminUseCaseTest;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
final class ProductsByValuesRepositoryTest extends KernelTestCase
{
    #[DependsOnClass(ProductsProductNewAdminUseCaseTest::class)]
    public static function testFindAll(): void
    {
        /** @var ProductDetailByInvariableInterface $productDetailByInvariable */
        $productDetailByInvariable = self::getContainer()->get(ProductDetailByInvariableInterface::class);

        /** @var ProductDetailByInvariableResult $result */
        $result = $productDetailByInvariable->invariable(ProductInvariableUid::TEST)->find();

        $offerValue = $result->getProductOfferValue();

        $variationValue = $result->getProductVariationValue();

        $modificationValue = $result->getProductModificationValue();

        /** @var ProductsByValuesInterface $repositoryByValue */
        $repositoryByValue = self::getContainer()->get(ProductsByValuesInterface::class);
        $result = $repositoryByValue
            ->forCategory(CategoryProductUid::TEST)
            ->forOfferValue($offerValue)
            ->forVariationValue($variationValue)
            ->forModificationValue($modificationValue)
            ->findAll();

        self::assertNotFalse($result);

        $result = $result->current();

        self::assertInstanceOf(ProductsByValuesResult::class, $result);

        self::assertInstanceOf(ProductUid::class, $result->getProduct());
        self::assertInstanceOf(ProductEventUid::class, $result->getEvent());
        self::assertIsString($result->getProductUrl());

        self::assertIsInt($result->getProductQuantity());
        self::assertIsInt($result->getProductReserve());

        self::assertTrue(
            $result->getProductOfferUid() === null ||
            $result->getProductOfferUid() instanceof ProductOfferUid
        );
        self::assertTrue(
            $result->getProductOfferValue() === null ||
            is_string($result->getProductOfferValue())
        );
        self::assertTrue(
            $result->getProductOfferPostfix() === null ||
            is_string($result->getProductOfferPostfix())
        );

        self::assertTrue(
            $result->getProductVariationUid() === null ||
            $result->getProductVariationUid() instanceof ProductVariationUid
        );
        self::assertTrue(
            $result->getProductVariationValue() === null ||
            is_string($result->getProductVariationValue())
        );
        self::assertTrue(
            $result->getProductVariationPostfix() === null ||
            is_string($result->getProductVariationPostfix())
        );

        self::assertTrue(
            $result->getProductModificationUid() === null ||
            $result->getProductModificationUid() instanceof ProductModificationUid
        );
        self::assertTrue(
            $result->getProductModificationValue() === null ||
            is_string($result->getProductModificationValue())
        );
        self::assertTrue(
            $result->getProductModificationPostfix() === null ||
            is_string($result->getProductModificationPostfix())
        );
    }
}