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
    private static array $result;

    public static function setUpBeforeClass(): void
    {
        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $result = $repository
            ->maxResult(1)
            ->find();

        self::assertNotFalse($result, 'Не найдено ни одного продукта для тестирования');
        self::$result = current($result);
    }

    /** Набор ключей для сравнения алиасов в основном запросе */
    public static function getAllQueryKeys(): array
    {
        return [
            "product_name",
            "url",
            "sort",
            "product_offer_uid",
            "product_offer_value",
            "product_offer_postfix",
            "product_variation_uid",
            "product_variation_value",
            "product_variation_postfix",
            "product_modification_uid",
            "product_modification_value",
            "product_modification_postfix",
            "category_id",
            "category_url",
            "product_price",
            "product_old_price",
            "product_currency",
            "product_image",
            "product_invariable_id",
        ];
    }

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

    /** Тестирование алиасов в основном запросе */
    public function testFindAll(): void
    {
        $queryKeys = self::getAllQueryKeys();

        $current = self::$result;

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Найдено несоответствие с ключами из массива getAllQueryKeys: %s', $key));
        }

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
        }
    }

    /**
     * @depends testFindAll
     * Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT
     */
    public function testOrderProducts(): void
    {
        $queryKeys = self::getProductImagesKeys();

        $current = current(json_decode(self::$result['product_image'], true));

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Найдено несоответствие с ключами из массива getProductImagesKeys: %s', $key));
        }

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
        }
    }

    /** Тестирование метода maxResult */
    public function testMaxResult(): void
    {
        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $result = $repository
            ->maxResult(5)
            ->find();

        self::assertNotFalse($result, 'Не найдено ни одного продукта для тестирования');
        self::assertTrue(count($result) === 5);
    }

    /** Тестирование метода forCategory */
    public function testForCategory(): void
    {
        /** @var BestSellerProductsInterface $repository */
        $repository = self::getContainer()->get(BestSellerProductsInterface::class);

        $category = new CategoryProductUid();

        $result = $repository
            ->forCategory($category)
            ->find();

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
            ->find();

        foreach($result as $product)
        {
            self::assertTrue($product['product_invariable_id'] !== $invariable, 'Ошибка фильтрации по Product Invariable');
        }

    }
}
