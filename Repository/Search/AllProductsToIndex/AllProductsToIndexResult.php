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

namespace BaksDev\Products\Product\Repository\Search\AllProductsToIndex;

use BaksDev\Core\Services\Switcher\Switcher;
use BaksDev\Core\Type\UidType\Uid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Search\Repository\DataToIndexResult\DataToIndexResultInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AllProductsToIndexResult implements DataToIndexResultInterface
{
    public function __construct(
        private string|null $id,
        private string|null $product_name,
        private string|null $product_article,

        private string|null $product_offer_id = null,
        private string|null $product_offer_value = null,

        private string|null $product_variation_id = null,
        private string|null $product_variation_value = null,

        private string|null $product_modification_id = null,
        private string|null $product_modification_value = null,

        private string|null $property = null,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }

    public function getProductOfferId(): ProductOfferUid|null
    {
        if(is_null($this->product_offer_id))
        {
            return null;
        }

        return new ProductOfferUid($this->product_offer_id);
    }


    public function getProductVariationId(): ProductVariationUid|null
    {
        if(is_null($this->product_variation_id))
        {
            return null;
        }

        return new ProductVariationUid($this->product_variation_id);
    }

    public function getProductModificationId(): ProductModificationUid|null
    {
        if(is_null($this->product_modification_id))
        {
            return null;
        }

        return new ProductModificationUid($this->product_modification_id);
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }


    public function setTextSearch(Switcher $switcher, ?TranslatorInterface $translator = null): string
    {
        $product_article = str_replace('-', ' ', $this->product_article);

        // Добавить "ошибочный" вариант Switcher
        $transl_article = $switcher->toRus($product_article);
        $transl_name = $switcher->toRus($this->product_name);
        $transl_offer = $switcher->toRus($this->product_offer_value);
        $transl_variation = $switcher->toRus($this->product_variation_value);
        $transl_modification = $switcher->toRus($this->product_modification_value);

        $search = explode(' ',
            $product_article
            .' '.$transl_article

            .' '.$this->product_name
            .' '.$transl_name

            .' '.$this->product_offer_value
            .' '.$transl_offer

            .' '.$this->product_variation_value
            .' '.$transl_variation

            .' '.$this->product_modification_value
            .' '.$transl_modification,
        );

        if(json_validate($this->property))
        {
            $decode = json_decode($this->property, true, 512, JSON_THROW_ON_ERROR);

            $property = array_filter($decode, static function($element) {
                return false === empty($element) && false === Uid::isUid($element);
            });

            $search = array_merge($search, $property);
        }

        return implode(' ', array_unique($search));
    }
}