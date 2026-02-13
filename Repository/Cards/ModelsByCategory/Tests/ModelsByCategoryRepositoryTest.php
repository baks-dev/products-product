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

namespace BaksDev\Products\Product\Repository\Cards\ModelsByCategory\Tests;

use BaksDev\Products\Category\Repository\AllCategory\AllCategoryInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Repository\Cards\ModelsByCategory\ModelsByCategoryInterface;
use BaksDev\Products\Product\Repository\Cards\ModelsByCategory\ModelsByCategoryResult;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class ModelsByCategoryRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {

        /** @var AllCategoryInterface $categoriesRepo */
        $categoriesRepo = self::getContainer()->get(AllCategoryInterface::class);
        $categories = $categoriesRepo->getOnlyChildren();

        if(empty($categories))
        {
            $this->addWarning('Не найдено ни одной категории товаров');
            return;
        }

        $categoryUids = null;

        foreach($categories as $category)
        {
            $categoryUids[] = new CategoryProductUid($category['id']);
        }

        /** @var ModelsByCategoryInterface $repository */
        $repository = self::getContainer()->get(ModelsByCategoryInterface::class);

        $results = $repository
            ->inCategories($categoryUids)
            ->maxResult(100)
            ->findAll();

        if(false === $results)
        {
            $this->addWarning(sprintf('Не найдено ни одного продукта по категории %s | %s',
                current($categories)['id'],
                current($categories)['category_name'],
            ));

            return;
        }

        /** @var ModelsByCategoryResult $ModelsByCategoryResult */
        foreach($results as $ModelsByCategoryResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(ModelsByCategoryResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($ModelsByCategoryResult);
                    // dump($data);
                }
            }
        }
    }

}