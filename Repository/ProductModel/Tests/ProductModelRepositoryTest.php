<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\ProductModel\Tests;

use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Repository\ProductModel\ProductModelInterface;
use BaksDev\Products\Product\Repository\ProductModel\ProductModelResult;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class ProductModelRepositoryTest extends KernelTestCase
{


    public function testSectionFieldKeys(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $products = $em->getRepository(Product::class)->findAll();

        /** @var ProductModelInterface $repository */
        $repository = self::getContainer()->get(ProductModelInterface::class);
        $ProductModelResult = false;

        /** @var Product $product */
        foreach($products as $key => $product)
        {
            if($key >= 100)
            {
                break;
            }

            $ProductModelResult = $repository
                ->byProduct($product->getId())
                ->find();

            if(true === ($ProductModelResult instanceof ProductModelResult))
            {
                break;
            }

        }

        self::assertNotFalse($ProductModelResult, 'Не найдено ни одного продукта для тестирования');

        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(ProductModelResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($ProductModelResult);
                // dump($data);
            }
        }
    }
}
