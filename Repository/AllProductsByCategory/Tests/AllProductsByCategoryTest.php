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

namespace BaksDev\Products\Product\Repository\AllProductsByCategory\Tests;

use BaksDev\Products\Product\Repository\AllProductsByCategory\AllProductsByCategoryInterface;
use BaksDev\Products\Product\Repository\AllProductsByCategory\AllProductsByCategoryResult;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class AllProductsByCategoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);

        /** @var AllProductsByCategoryInterface $AllProductsByCategory */
        $AllProductsByCategory = self::getContainer()->get(AllProductsByCategoryInterface::class);

        $result = $AllProductsByCategory
            ->forProfile(new UserProfileUid('0197f935-a3c8-701a-9dfb-5e6f951e4c6e'))
            ->fetchAllProductByCategory();

        foreach($result as $AllProductsByCategoryResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(AllProductsByCategoryResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($AllProductsByCategoryResult);
                    dump($data);
                }
            }

            break;
        }
    }

}