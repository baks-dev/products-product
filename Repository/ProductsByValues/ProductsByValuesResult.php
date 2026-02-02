<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\ProductsByValues;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

final readonly class ProductsByValuesResult
{
    public function __construct(
        private string $product,
        private string $event,
        private string $product_url,
        private int $product_quantity,
        private int $product_reserve,
        private ?string $product_offer_uid,
        private ?string $product_offer_value,
        private ?string $product_offer_postfix,
        private ?string $product_offer_const,
        private ?string $product_variation_uid,
        private ?string $product_variation_value,
        private ?string $product_variation_postfix,
        private ?string $product_variation_const,
        private ?string $product_modification_uid,
        private ?string $product_modification_value,
        private ?string $product_modification_postfix,
        private ?string $product_modification_const,
    ) {}

    public function getProduct(): ProductUid
    {
        return new ProductUid($this->product);
    }

    public function getEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function getProductUrl(): string
    {
        return $this->product_url;
    }

    public function getProductOfferUid(): ?ProductOfferUid
    {
        return $this->product_offer_uid === null ? null : new ProductOfferUid($this->product_offer_uid);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getProductOfferConst(): ?ProductOfferConst
    {
        return false === empty($this->product_offer_const) ? new ProductOfferConst($this->product_offer_const) : null;
    }

    public function getProductVariationUid(): ?ProductVariationUid
    {
        return $this->product_variation_uid === null ? null : new ProductVariationUid($this->product_variation_uid);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationConst(): ?ProductVariationConst
    {
        return false === empty($this->product_variation_const) ? new ProductVariationConst($this->product_variation_const) : null;
    }

    public function getProductModificationUid(): ?ProductModificationUid
    {
        return $this->product_modification_uid === null ? null : new ProductModificationUid($this->product_modification_uid);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationConst(): ?ProductModificationConst
    {
        return false === empty($this->product_modification_const) ? new ProductModificationConst($this->product_modification_const) : null;
    }

    public function getProductQuantity(): int
    {
        return $this->product_quantity;
    }

    public function getProductReserve(): int
    {
        return $this->product_reserve;
    }
}