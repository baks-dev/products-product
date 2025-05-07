<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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
 * @depends BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewTest::class
 * @depends BaksDev\Products\Product\UseCase\Admin\Invariable\Tests\ProductInvariableTest::class
 */
final class ProductDetailByInvariableRepositoryTest extends KernelTestCase
{
    public function testFind(): void
    {
        /** @var ProductDetailByInvariableInterface $productDetailByInvariable */
        $productDetailByInvariable = self::getContainer()->get(ProductDetailByInvariableInterface::class);

        $result = $productDetailByInvariable->invariable(ProductInvariableUid::TEST)->find();

        /** @var ProductDetailByInvariableResult $item */
        foreach($result as $item)
        {
            self::assertTrue(
                $item->getProductOfferValue() === null ||
                is_string($item->getProductOfferValue())
            );
            self::assertTrue(
                $item->getProductOfferPostfix() === null ||
                is_string($item->getProductOfferPostfix())
            );
            self::assertTrue(
                $item->getProductOfferReference() === null ||
                $item->getProductOfferReference() instanceof InputField
            );

            self::assertTrue(
                $item->getProductVariationValue() === null ||
                is_string($item->getProductVariationValue())
            );
            self::assertTrue(
                $item->getProductVariationPostfix() === null ||
                is_string($item->getProductVariationPostfix())
            );
            self::assertTrue(
                $item->getProductVariationReference() === null ||
                $item->getProductVariationReference() instanceof InputField
            );

            self::assertTrue(
                $item->getProductModificationValue() === null ||
                is_string($item->getProductModificationValue())
            );
            self::assertTrue(
                $item->getProductModificationPostfix() === null ||
                is_string($item->getProductModificationPostfix())
            );
            self::assertTrue(
                $item->getProductModificationReference() === null ||
                $item->getProductModificationReference() instanceof InputField
            );

            self::assertTrue($item->getProductArticle() === null || is_string($item->getProductArticle()));
            self::assertTrue($item->getProductImage() === null || is_string($item->getProductImage()));
            self::assertTrue($item->getProductImageExt() === null || is_string($item->getProductImageExt()));
            self::assertTrue($item->getProductImageCdn() === null || is_bool($item->getProductImageCdn()));
            self::assertTrue(
                $item->getCategorySectionField() === null ||
                is_string($item->getCategorySectionField())
            );

            break;
        }
    }
}