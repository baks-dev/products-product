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

namespace BaksDev\Products\Product\Repository\ProductDetail\Tests;

use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByConstInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class ProductDetailByConstRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var ProductDetailByConstInterface $OneProductDetailByConst */
        $OneProductDetailByConst = self::getContainer()->get(ProductDetailByConstInterface::class);

        $current = $OneProductDetailByConst
            ->product(new ProductUid('01876b34-ed23-7c18-ba48-9071e8646a08'))
            ->offerConst(new ProductOfferConst('01876b34-eccb-7188-887f-0738cae05232'))
            ->variationConst(new ProductVariationConst('01876b34-ecce-7c46-9f63-fc184b6527ee'))
            ->modificationConst(new ProductModificationConst('01876b34-ecd2-762c-9834-b6a914a020ba'))
            ->find();


        $array_keys = [
            "id",
            "event",
            "active",
            'active_from',
            'active_to',
            "product_name",
            'product_preview',
            'product_description',
            "product_url",
            "product_offer_uid",
            "product_offer_const",
            "product_offer_value",
            "product_offer_postfix",
            "product_offer_reference",
            "product_offer_name",
            "product_offer_name_postfix",
            "product_variation_uid",
            "product_variation_const",
            "product_variation_value",
            "product_variation_postfix",
            "product_variation_reference",
            "product_variation_name",
            "product_variation_name_postfix",
            "product_modification_uid",
            "product_modification_const",
            "product_modification_value",
            "product_modification_postfix",
            "product_modification_reference",
            "product_modification_name",
            "product_modification_name_postfix",
            "product_article",
            "product_image",
            "product_image_ext",
            "product_image_cdn",
            "category_name",
            "category_url",
            "product_quantity",
            "product_price",
            "product_old_price",
            "product_currency",
            "category_section_field",
        ];


        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $array_keys), sprintf('Появился новый ключ %s', $key));
        }

        foreach($array_keys as $key)
        {
            self::assertTrue(array_key_exists($key, $current), sprintf('Неизвестный новый ключ %s', $key));
        }


        /**
         * category_section_field
         */


        self::assertTrue(json_validate($current['category_section_field']));
        $current = json_decode($current['category_section_field'], true);
        $current = current($current);

        $array_keys = [
            '0',
            "field_uid",
            "field_card",
            "field_name",
            "field_type",
            "field_const",
            "field_trans",
            "field_value",
            "field_public",
            "field_alternative",
        ];

        foreach($current as $key => $value)
        {
            self::assertTrue(in_array($key, $array_keys), sprintf('Появился новый ключ %s', $key));
        }

        foreach($array_keys as $key)
        {
            self::assertTrue(array_key_exists($key, $current), sprintf('Неизвестный новый ключ %s', $key));
        }
    }
}