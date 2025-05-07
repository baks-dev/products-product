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

namespace BaksDev\Products\Product\Repository\BestSellerProducts\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Repository\BestSellerProducts\BestSellerProductsInterface;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
class BestSellerProductsRepositoryTest extends KernelTestCase
{

    /** Набор ключей для сравнения алиасов в результате применения функции JSONB_BUILD_OBJECT */
    public static function getProductImagesKeys(): array
    {
        return [
            "img",
            "img_cdn",
            "img_ext",
            "img_root",
        ];
    }

    /**
     * Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT
     */
    public function testOrderProducts(): void
    {
        $queryKeys = self::getProductImagesKeys();

        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $products = $repository
            ->maxResult(1000)
            ->findAll();

        foreach($products as $product)
        {
            $images = $product->getProductImages();

            if(false === is_null($images))
            {
                $current = current($images);

                foreach($queryKeys as $key)
                {
                    self::assertArrayHasKey($key, $current, sprintf('Найдено несоответствие с ключами из массива getProductImagesKeys: %s', $key));
                }

                foreach($current as $key => $value)
                {
                    self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
                }

                break;
            }
        }

        self::assertTrue(true);
    }

    /** Тестирование метода maxResult */
    public function testMaxResult(): void
    {
        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $result = $repository
            ->maxResult(5)
            ->findAll();

        self::assertNotFalse($result, 'Не найдено ни одного продукта для тестирования');
        self::assertTrue(count(iterator_to_array($result)) === 5);
    }

    /** Тестирование метода forCategory */
    public function testForCategory(): void
    {
        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $category = new CategoryProductUid();

        $result = $repository
            ->forCategory($category)
            ->findAll();

        self::assertFalse($result);
    }

    /** Тестирование метода byInvariable */
    public function testByInvariable(): void
    {
        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $invariable = new ProductInvariableUid();

        $result = $repository
            ->byInvariable($invariable)
            ->maxResult(2)
            ->findAll();

        foreach($result as $product)
        {
            self::assertTrue($product->getProductInvariableId() !== $invariable, 'Ошибка фильтрации по Product Invariable');
        }

        self::assertTrue(true);
    }
}
