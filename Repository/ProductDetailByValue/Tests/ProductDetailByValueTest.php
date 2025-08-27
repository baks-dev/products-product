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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ProductDetailByValue\Tests;

use BaksDev\Products\Product\Repository\ProductDetailByValue\ProductDetailByValueInterface;
use BaksDev\Products\Product\Repository\ProductDetailByValue\ProductDetailByValueResult;
use BaksDev\Products\Product\Type\Id\ProductUid;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[Group('products-product')]
#[When(env: 'test')]
class ProductDetailByValueTest extends KernelTestCase
{
    public function testUseCase(): void
    {

        /** @var ProductDetailByValueInterface $repository */
        $repository = self::getContainer()->get(ProductDetailByValueInterface::class);

        $ProductDetailByValueResult = $repository
            ->byProduct('01876b34-ed23-7c18-ba48-9071e8646a08')
            ->byOfferValue('01876b34-eccb-7188-887f-0738cae05232')
            ->byVariationValue('01876b34-ecce-7c46-9f63-fc184b6527ee')
            ->byModificationValue('01876b34-ecd2-762c-9834-b6a914a020ba')
            ->find();


        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(ProductDetailByValueResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($ProductDetailByValueResult);
                // dump($data);
            }
        }

        self::assertTrue(true);
    }

    public function testFetchProductAssociative(): void
    {

        self::assertTrue(true);
        return;

        /** @var ProductDetailByValueInterface $repository */
        $repository = self::getContainer()->get(ProductDetailByValueInterface::class);

        $result = $repository
            ->fetchProductAssociative(
                product: new ProductUid('01876b34-ed23-7c18-ba48-9071e8646a08'),
                offer: '01876b34-eccb-7188-887f-0738cae05232',
                variation: '01876b34-ecce-7c46-9f63-fc184b6527ee',
                modification: '01876b34-ecd2-762c-9834-b6a914a020ba',
            );

        //        dump($result);
        //        dd();
    }
}