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

namespace BaksDev\Products\Product\Type\Event\Tests;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Uid\Uuid;

/**
 * @group products-product
 */
#[When(env: 'test')]
final class ProductEventUidTests extends TestCase
{
    public function testConstructor()
    {
        $uid = new ProductEventUid();
        $this->assertInstanceOf(ProductEventUid::class, $uid);

        $value = '0188a99e-7e95-7385-a2d5-b6fb3d0ca89f';
        $uid = new ProductEventUid($value);
        $this->assertEquals($value, $uid->getValue());
    }

    public function testGetters()
    {
        $attr = 'someAttr';
        $option = 'someOption';
        $property = 'someProperty';
        $characteristic = 'someCharacteristic';

        $uid = new ProductEventUid(null, $attr, $option, $property, $characteristic);

        $this->assertEquals($attr, $uid->getAttr());
        $this->assertEquals($option, $uid->getOption());
        $this->assertEquals($property, $uid->getProperty());
        $this->assertEquals($characteristic, $uid->getCharacteristic());
    }

    public function testUidGeneration()
    {
        $uid1 = ProductEventUid::generate();
        $uid2 = ProductEventUid::generate();

        $this->assertInstanceOf(ProductEventUid::class, $uid1);
        $this->assertInstanceOf(ProductEventUid::class, $uid2);
        $this->assertNotEquals($uid1->getValue(), $uid2->getValue());
    }
}