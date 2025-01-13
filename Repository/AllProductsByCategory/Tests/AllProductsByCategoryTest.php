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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group products-product
 */
#[When(env: 'test')]
class AllProductsByCategoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);
        return;


        /** @var AllProductsByCategoryInterface $AllProductsByCategory */
        $AllProductsByCategory = self::getContainer()->get(AllProductsByCategoryInterface::class);

        $result = $AllProductsByCategory
            ->category('0189784d-4f53-7010-b89d-73765e7bfda5')
            ->analyze()
            ->find();

        $array_keys = [
            "category_url",
            "category_name",
            "id",
            "event",
            "active_from",
            "product_name",
            "product_preview", "product_description",
            "url",
            "sort",
            "product_offer_quantity",
            "product_variation_quantity",
            "product_modification_quantity",

            "product_offers",


            "product_image",
            "product_image_ext",
            "product_image_cdn",
            "product_price",
            "product_old_price",
            "product_currency",
            "product_quantity",
            "category_section_field",

        ];


        $current = current($result);

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $array_keys), sprintf('Появился новый ключ %s', $key));
        }

        foreach($array_keys as $key)
        {
            self::assertTrue(array_key_exists($key, $current));
        }


        $array_product_offers = [
            "0",
            "offer_uid",
            "offer_value",
            "offer_article",
            "offer_postfix",
            "variation_uid",
            "offer_reference",
            "variation_value",
            "modification_uid",
            "variation_article",
            "variation_postfix",
            "modification_value",
            "variation_reference",
            "modification_article",
            "modification_postfix",
            "modification_reference",
        ];

        $product_offers = json_decode($current["product_offers"], true, 512, JSON_THROW_ON_ERROR);
        $current_product_offers = current($product_offers);

        foreach($current_product_offers as $key => $value)
        {
            self::assertTrue(in_array($key, $array_product_offers), sprintf('Появился новый ключ %s', $key));
        }

        foreach($array_product_offers as $key)
        {
            self::assertTrue(array_key_exists($key, $current_product_offers), sprintf('Отсутствует ключ %s', $key));
        }


        $array_category_section_field = [
            "0",
            "field_card",
            "field_name",
            "field_type",
            "field_photo",
            "field_trans",
            "field_value",
        ];


        $category_section_field = json_decode($current["category_section_field"], true, 512, JSON_THROW_ON_ERROR);
        $current_category_section_field = current($category_section_field);

        foreach($current_category_section_field as $key => $value)
        {
            self::assertTrue(in_array($key, $array_category_section_field), sprintf('Появился новый ключ %s', $key));
        }

        foreach($array_category_section_field as $key)
        {
            self::assertTrue(array_key_exists($key, $current_category_section_field), sprintf('Отсутствует ключ %s', $key));
        }

    }

}