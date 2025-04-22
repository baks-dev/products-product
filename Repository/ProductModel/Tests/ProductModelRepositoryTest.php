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

namespace BaksDev\Products\Product\Repository\ProductModel\Tests;

use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Repository\ProductModel\ProductModelInterface;
use BaksDev\Products\Product\Repository\ProductModel\ProductModelResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
class ProductModelRepositoryTest extends KernelTestCase
{
    private static ProductModelResult $result;

    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $products = $em->getRepository(Product::class)
            ->findAll();

        /** @var ProductModelInterface $repository */
        $repository = self::getContainer()->get(ProductModelInterface::class);
        $result = false;

        /** @var Product $product */
        foreach($products as $key => $product)
        {
            if($key >= 100)
            {
                break;
            }

            $result = $repository
                ->byProduct($product->getId())
                ->find();
        }

        self::assertNotFalse($result, 'Не найдено ни одного продукта для тестирования');
        self::$result = $result;
    }

    public static function getProductOffersKeys(): array
    {

        return [
            0,
            "price",
            "article",
            "currency",
            "quantity",
            "offer_uid",
            "old_price",
            "offer_name",
            "offer_value",
            "offer_postfix",
            "variation_uid",
            "variation_name",
            "offer_reference",
            "variation_value",
            "modification_uid",
            "modification_name",
            "variation_postfix",
            "modification_value",
            "variation_reference",
            "modification_postfix",
            "product_invariable_id",
            "modification_reference"
        ];
    }

    public static function getProductPhotoKeys(): array
    {
        return [
            "product_img",
            "product_img_cdn",
            "product_img_ext",
            "product_img_root",
        ];
    }

    public static function getSectionFieldKeys(): array
    {
        return [
            0,
            "field_card",
            "field_name",
            "field_type",
            "field_trans",
            "field_value",
            "field_public",
        ];
    }

    /** Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT */
    public function testProductOffersKeys(): void
    {
        $result = self::$result->getProductOffers();

        if(is_null($result))
        {
            self::assertTrue(true);
            return;
        }

        $queryKeys = self::getProductOffersKeys();

        $current = current($result);

        if(false === is_null($current))
        {
            $keys = implode(', ', array_keys($current));

            foreach($queryKeys as $key)
            {
                self::assertArrayHasKey($key, $current, sprintf('Ключ %s не найден. Доступные ключи: %s', $key, $keys));
            }

            foreach($current as $key => $value)
            {
                self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
            }
        }

        self::assertTrue(true);
    }

    /**
     * @depends testProductOffersKeys
     * Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT
     */
    public function testProductPhotoKeys(): void
    {
        $result = self::$result->getProductImages();

        if(is_null($result))
        {
            self::assertTrue(true);
            return;
        }

        $queryKeys = self::getProductPhotoKeys();

        $current = current($result);

        if(false === is_null($current))
        {
            $keys = implode(', ', array_keys($current));

            foreach($queryKeys as $key)
            {
                self::assertArrayHasKey($key, $current, sprintf('Ключ %s не найден. Доступные ключи: %s', $key, $keys));
            }

            foreach($current as $key => $value)
            {
                self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
            }
        }

        self::assertTrue(true);
    }

    /**
     * @depends testProductPhotoKeys
     * Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT
     */
    public function testSectionFieldKeys(): void
    {
        $result = self::$result->getCategorySectionField();

        if(is_null($result))
        {
            self::assertTrue(true);
            return;
        }

        $queryKeys = self::getSectionFieldKeys();

        $current = current($result);

        if(false === is_null($current))
        {
            $keys = implode(', ', array_keys($current));

            foreach($queryKeys as $key)
            {
                self::assertArrayHasKey($key, $current, sprintf('Ключ %s не найден. Доступные ключи: %s', $key, $keys));
            }

            foreach($current as $key => $value)
            {
                self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
            }
        }

        self::assertTrue(true);
    }
}
