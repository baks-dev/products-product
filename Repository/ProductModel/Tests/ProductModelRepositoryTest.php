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
use BaksDev\Products\Product\Type\Id\ProductUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 */
#[When(env: 'test')]
class ProductModelRepositoryTest extends KernelTestCase
{
    private static array $result;

    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $products = $em->getRepository(Product::class)
            ->findAll();

        /** @var Product $product */
        $product = current($products);

        /** @var ProductModelInterface $repository */
        $repository = self::getContainer()->get(ProductModelInterface::class);

        $result = $repository
            ->byProduct($product->getId())
            ->find();

        self::assertNotFalse($result, 'Не найдено ни одного продукта для тестирования');
        self::$result = $result;
    }

    /** Набор ключей для сравнения алиасов в основном запросе */
    public static function getAllQueryKeys(): array
    {
        return [
            "id",
            "event",
            "active",
            "active_from",
            "active_to",
            "seo_title",
            "seo_keywords",
            "seo_description",
            "product_name",
            "product_preview",
            "product_description",
            "url",
            "product_offer_reference",
            "product_offers",
            "product_modification_image",
            "product_variation_image",
            "product_offer_images",
            "product_photo",
            "category_id",
            "category_name",
            "category_url",
            "category_cover_ext",
            "category_cover_cdn",
            "category_cover_dir",
            "category_section_field"
        ];
    }

    /** Набор ключей для сравнения алиасов в результате применения функции JSONB_BUILD_OBJECT */
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

    /** Набор ключей для сравнения алиасов в результате применения функции JSONB_BUILD_OBJECT */
    public static function getProductPhotoKeys(): array
    {
        return [
            "product_img",
            "product_img_cdn",
            "product_img_ext",
            "product_img_root",
        ];
    }

    /** Набор ключей для сравнения алиасов в результате применения функции JSONB_BUILD_OBJECT */
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
    public function testProductOffersKeys(): void
    {
        $queryKeys = self::getProductOffersKeys();

        $current = current(json_decode(self::$result['product_offers'], true));

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Найдено несоответствие с ключами из массива getProductImagesKeys: %s', $key));
        }

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
        }
    }

    /**
     * @depends testProductOffersKeys
     * Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT
     */
    public function testProductPhotoKeys(): void
    {
        $queryKeys = self::getProductPhotoKeys();

        $current = current(json_decode(self::$result['product_photo'], true));

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Найдено несоответствие с ключами из массива getProductImagesKeys: %s', $key));
        }

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
        }
    }

    /**
     * @depends testProductPhotoKeys
     * Тестирование алиасов в результате применения функции JSONB_BUILD_OBJECT
     */
    public function testSectionFieldKeys(): void
    {
        $queryKeys = self::getSectionFieldKeys();

        $current = current(json_decode(self::$result['category_section_field'], true));

        foreach($queryKeys as $key)
        {
            self::assertArrayHasKey($key, $current, sprintf('Найдено несоответствие с ключами из массива getProductImagesKeys: %s', $key));
        }

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $queryKeys), sprintf('Новый ключ в массиве с результатом запроса: %s', $key));
        }
    }

    /** Тестирование метода byProduct */
    public function testByProduct(): void
    {
        /** @var ProductModelInterface $repository */
        $repository = self::getContainer()->get(ProductModelInterface::class);

        $product = new ProductUid();

        $result = $repository
            ->byProduct($product)
            ->find();

        self::assertFalse($result, 'Не сработало условие для метода byProduct');
    }
}
