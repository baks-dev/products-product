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

namespace BaksDev\Products\Product\Repository\ProductAlternative\Tests;

use BaksDev\Products\Product\Repository\ProductAlternative\ProductAlternativeInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group product-alternative-interface-test
 */
#[When(env: 'test')]
class ProductAlternativeRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var ProductAlternativeInterface $ProductAlternative */
        $ProductAlternative = self::getContainer()->get(ProductAlternativeInterface::class);

        $result = $ProductAlternative
            ->setMaxResult(1)
            ->fetchAllAlternativeAssociative(
                '19',
                null,
                null,
                null
            );

        $array_keys = [
            "id",
            "event",
            "product_offer_value",
            "product_offer_postfix",
            "product_offer_uid",
            "product_offer_reference",
            "product_offer_name",

            "product_variation_value",
            "product_variation_postfix",
            "product_variation_uid",
            "product_variation_reference",
            "product_variation_name",

            "product_modification_value",
            "product_modification_postfix",
            "product_modification_uid",
            "product_modification_reference",
            "product_modification_name",

            "active_from",
            "product_name",
            "product_url",
            "article",


            "price",
            "product_old_price",
            "currency",
            "quantity",
            "category_name",
            "category_url",
            "category_section_field",
            "product_invariable_id",

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
    }
}