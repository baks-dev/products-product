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

namespace BaksDev\Products\Product\Repository\ProductCatalog\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Repository\ProductCatalog\ProductCatalogInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
class ProductCatalogRepositoryTest extends KernelTestCase
{
    private static array $result;

    public static function setUpBeforeClass(): void
    {
        /** @var ProductCatalogInterface $repository */
        $repository = self::getContainer()->get(ProductCatalogInterface::class);

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
            "id",
            "event",
            "product_name",
            "url",
            "active_from",
            "active_to",
            "product_offer_uid",
            "product_offer_value",
            "product_offer_postfix",
            "product_offer_reference",
            "product_variation_uid",
            "product_variation_value",
            "product_variation_postfix",
            "product_variation_reference",
            "product_modification_uid",
            "product_modification_value",
            "product_modification_postfix",
            "product_modification_reference",
            "product_article",
            "product_image",
            "product_image_ext",
            "product_image_cdn",
            "product_price",
            "product_old_price",
            "product_currency",
            "category_url",
            "category_name",
            "category_section_field",
            "product_invariable_id",
        ];
    }

    /** Набор ключей для сравнения алиасов в результате применения функции JSONB_BUILD_OBJECT */
    public static function getCategorySectionFieldKeys(): array
    {
        return [
            "field_card",
            "field_name",
            "field_sort",
            "field_type",
            "field_photo",
            "field_trans",
            "field_value",
        ];
    }

    /** Тестирование алиасов в основном запросе */
    public function testFindAll(): void
    {
        $queryKeys = self::getAllQueryKeys();

        $current = self::$result;

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Ключ "%s" из набора данных getAllQueryKeys отсутствует в результате метода findAll', $key));
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
    public function testCategorySectionFieldKeys(): void
    {
        $queryKeys = self::getCategorySectionFieldKeys();

        $current = current(json_decode(self::$result['category_section_field'], true));

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Ключ "%s" из набора данных getCategorySectionFieldKeys отсутствует в результате метода findAll', $key));
        }

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
        }
    }

    /** Тестирование метода maxResult */
    public function testMaxResult(): void
    {
        $actual = 2;
        $expected = 2;

        /** @var ProductCatalogInterface $repository */
        $repository = self::getContainer()->get(ProductCatalogInterface::class);

        $result = $repository
            ->maxResult($actual)
            ->find();

        self::assertNotFalse($result, 'Не найдено ни одного продукта для тестирования');
        self::assertTrue(count($result) === $expected, sprintf('Ошибка maxResult. Количество элементов: %s. Ожидаемое количество: %s', $actual, $expected));
    }

    /**
     * @depends testMaxResult
     * Тестирование метода forCategory
     */
    public function testForCategory(): void
    {
        /** @var ProductCatalogInterface $repository */
        $repository = self::getContainer()->get(ProductCatalogInterface::class);

        $category = new CategoryProductUid();

        $result = $repository
            ->forCategory($category)
            ->find();

        self::assertFalse($result);
    }
}
