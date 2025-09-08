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

use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByConstInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByConstResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use DateTimeImmutable;
use BaksDev\Reference\Money\Type\Money;

#[Group('products-product')]
#[Group('products-product-repository')]
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

    public function testUseCaseResult()
    {
        /** @var ProductDetailByConstInterface $OneProductDetailByConst */
        $OneProductDetailByConst = self::getContainer()->get(ProductDetailByConstInterface::class);

        $result = $OneProductDetailByConst
            ->product(new ProductUid(ProductUid::TEST))
            ->offerConst(new ProductOfferConst(ProductOfferConst::TEST))
            ->variationConst(new ProductVariationConst(ProductVariationConst::TEST))
            ->modificationConst(new ProductModificationConst(ProductModificationConst::TEST))
            ->findResult();

        self::assertInstanceOf(ProductDetailByConstResult::class, $result);

        self::assertInstanceOf(ProductUid::class, $result->getId());
        self::assertInstanceOf(ProductEventUid::class, $result->getEvent());

        self::assertIsInt($result->getProductQuantity());
        self::assertTrue(
            $result->getProductOldPrice() instanceof Money
            || false === $result->getProductOldPrice()
        );

        self::assertIsBool($result->getActive());
        self::assertInstanceOf(DateTimeImmutable::class, $result->getActiveFrom());
        self::assertInstanceOf(DateTimeImmutable::class, $result->getActiveTo());

        self::assertTrue(is_string($result->getProductPreview()) || null === $result->getProductPreview());
        self::assertTrue(
            is_string($result->getProductDescription())
            || null === $result->getProductDescription()
        );
        self::assertTrue(is_string($result->getProductUrl()) || null === $result->getProductUrl());

        self::assertTrue(
            $result->getProductOfferUid() === null ||
            $result->getProductOfferUid() instanceof ProductOfferUid
        );
        self::assertTrue(
            $result->getProductOfferConst() instanceof ProductOfferConst
            || null === $result->getProductOfferConst()
        );
        self::assertTrue(
            $result->getProductOfferValue() === null ||
            is_string($result->getProductOfferValue())
        );
        self::assertTrue(
            $result->getProductOfferPostfix() === null ||
            is_string($result->getProductOfferPostfix())
        );
        self::assertTrue(
            $result->getProductOfferReference() === null ||
            $result->getProductOfferReference() instanceof InputField
        );
        self::assertTrue($result->getProductOfferName() === null || is_string($result->getProductOfferName()));
        self::assertTrue(
            $result->getProductOfferNamePostfix() === null
            || is_string($result->getProductOfferNamePostfix())
        );

        self::assertTrue(
            $result->getProductVariationUid() === null ||
            $result->getProductVariationUid() instanceof ProductVariationUid
        );
        self::assertTrue(
            $result->getProductVariationConst() === null ||
            $result->getProductVariationConst() instanceof ProductVariationConst
        );
        self::assertTrue(
            $result->getProductVariationValue() === null ||
            is_string($result->getProductVariationValue())
        );
        self::assertTrue(
            $result->getProductVariationPostfix() === null ||
            is_string($result->getProductVariationPostfix())
        );
        self::assertTrue(
            $result->getProductVariationReference() === null ||
            $result->getProductVariationReference() instanceof InputField
        );
        self::assertTrue(
            $result->getProductVariationName() === null
            || is_string($result->getProductVariationName())
        );
        self::assertTrue(
            $result->getProductVariationNamePostfix() === null
            || is_string($result->getProductVariationNamePostfix())
        );

        self::assertTrue(
            $result->getProductModificationUid() === null ||
            $result->getProductModificationUid() instanceof ProductModificationUid
        );
        self::assertTrue(
            $result->getProductModificationConst() === null ||
            $result->getProductModificationConst() instanceof ProductModificationConst
        );
        self::assertTrue(
            $result->getProductModificationValue() === null ||
            is_string($result->getProductModificationValue())
        );
        self::assertTrue(
            $result->getProductModificationPostfix() === null ||
            is_string($result->getProductModificationPostfix())
        );
        self::assertTrue(
            $result->getProductModificationReference() === null ||
            $result->getProductModificationReference() instanceof InputField
        );
        self::assertTrue(
            $result->getProductModificationName() === null
            || is_string($result->getProductModificationName())
        );
        self::assertTrue(
            $result->getProductModificationNamePostfix() === null
            || is_string($result->getProductModificationNamePostfix())
        );

        self::assertTrue($result->getProductArticle() === null || is_string($result->getProductArticle()));
        self::assertTrue($result->getProductImage() === null || is_string($result->getProductImage()));
        self::assertTrue($result->getProductImageExt() === null || is_string($result->getProductImageExt()));
        self::assertTrue($result->getProductImageCdn() === null || is_bool($result->getProductImageCdn()));

        self::assertTrue($result->getCategoryName() === null || is_string($result->getCategoryName()));
        self::assertTrue($result->getCategoryUrl() === null || is_string($result->getCategoryUrl()));
        self::assertTrue(
            $result->getCategorySectionField() === null ||
            is_array($result->getCategorySectionField())
        );

        self::assertTrue($result->getProductPrice() instanceof Money || false === $result->getProductPrice());
        self::assertTrue($result->getProductCurrency() === null || is_string($result->getProductCurrency()));
    }
}