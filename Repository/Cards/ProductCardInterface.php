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

namespace BaksDev\Products\Product\Repository\Cards;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;

interface ProductCardInterface
{
    public function getProductId(): ProductUid;

    public function getProductEvent(): ProductEventUid;

    public function getProductName(): string;

    public function getProductUrl(): ?string;

    public function getProductOfferUid(): ?ProductOfferUid;

    public function getProductOfferValue(): ?string;

    public function getProductOfferPostfix(): ?string;

    public function getProductOfferReference(): ?string;

    public function getProductVariationUid(): ?string;

    public function getProductVariationValue(): ?string;

    public function getProductVariationPostfix(): ?string;

    public function getProductVariationReference(): ?string;

    public function getProductModificationUid(): ?string;

    public function getProductModificationValue(): ?string;

    public function getProductModificationPostfix(): ?string;

    public function getProductModificationReference(): ?string;

    public function getProductArticle(): string|false|null;

    public function getProductImages(): ?array;

    public function getProductPrice(): ?int;

    public function getProductOldPrice(): ?int;

    public function getProductCurrency(): ?string;

    public function getProductQuantity(): bool|int|null;

    public function getCategoryUrl(): string;

    public function getCategoryName(): string;

    public function getCategorySectionField(): string;

    public function getProductInvariableId(): ?string;
}
