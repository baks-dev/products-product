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

namespace BaksDev\Products\Product\Repository\ProductDetail\Tests;

use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableResult;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group products-product
 * @group products-product-repository
 *
 * @depends BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewTest::class
 * @depends BaksDev\Products\Product\UseCase\Admin\Invariable\Tests\ProductInvariableTest::class
 */
final class ProductDetailByInvariableRepositoryTest extends KernelTestCase
{
    public function testFind(): void
    {
        /** @var ProductDetailByInvariableInterface $productDetailByInvariable */
        $productDetailByInvariable = self::getContainer()->get(ProductDetailByInvariableInterface::class);

        /** @var ProductDetailByInvariableResult $result */
        $result = $productDetailByInvariable->invariable(ProductInvariableUid::TEST)->find();

        self::assertTrue(is_string($result->getProductArticle()));
        self::assertTrue(is_string($result->getProductName()));

        self::assertTrue(
            $result->getProductOfferValue() === null ||
            is_string($result->getProductOfferValue())
        );
        self::assertTrue(
            $result->getProductOfferPostfix() === null ||
            is_string($result->getProductOfferPostfix())
        );
        self::assertTrue(
            $result->getProductOfferReference() === null ||
            $result->getProductOfferReference() instanceof InputField
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
            $result->getProductVariationReference() === null ||
            $result->getProductVariationReference() instanceof InputField
        );

        self::assertTrue(
            $result->getProductModificationValue() === null ||
            is_string($result->getProductModificationValue())
        );
        self::assertTrue(
            $result->getProductModificationPostfix() === null ||
            is_string($result->getProductModificationPostfix())
        );
        self::assertTrue(
            $result->getProductModificationReference() === null ||
            $result->getProductModificationReference() instanceof InputField
        );

        self::assertTrue($result->getProductImage() === null || is_string($result->getProductImage()));
        self::assertTrue($result->getProductImageExt() === null || is_string($result->getProductImageExt()));
        self::assertTrue($result->getProductImageCdn() === null || is_bool($result->getProductImageCdn()));
        self::assertTrue(
            $result->getCategorySectionField() === null ||
            is_string($result->getCategorySectionField())
        );


    }
}