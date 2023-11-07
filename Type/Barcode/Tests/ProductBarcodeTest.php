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

namespace BaksDev\Products\Product\Type\Barcode\Tests;

use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
final class ProductBarcodeTest extends TestCase
{

    public function testBarcodeGeneration()
    {
        $barcode = new ProductBarcode();
        $this->assertMatchesRegularExpression('/^\d{13}$/', $barcode->getValue());

        $customValue = '0123456789012';
        $barcode = new ProductBarcode($customValue);
        $this->assertEquals($customValue, $barcode->getValue());
    }

    public function testBarcodeEncoding()
    {
        $article = 'TestArticle';
        $expected = '4601895645651';

        $barcode = new ProductBarcode();
        $result = $barcode->barcode($article);

        $this->assertEquals($expected, $result);
    }

    public function testRandomGeneration()
    {
        $barcode1 = ProductBarcode::generate();
        $barcode2 = ProductBarcode::generate();

        $this->assertNotEquals($barcode1, $barcode2);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{13}$/', $barcode1);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{13}$/', $barcode2);
    }
}