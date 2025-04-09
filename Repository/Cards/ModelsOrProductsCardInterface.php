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
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;

interface ModelsOrProductsCardInterface
{
    public function getProductId(): ProductUid;

    public function getProductEvent(): ProductEventUid;

    public function getProductName(): string|null|bool;

    public function getProductUrl(): string|null|bool;

    public function getProductSort(): int|null|bool;

    public function getProductActiveFrom(): string|null|bool;

    public function getCategoryOfferCard(): bool|null;

    public function getProductOfferReference(): string|null|bool;

    public function getProductOfferValue(): string|null|bool;

    public function getOfferAgg(): string;

    public function getCategoryVariationCard(): bool|null;

    public function getProductVariationReference(): string|null|bool;

    public function getProductVariationValue(): string|null|bool;

    public function getVariationAgg(): string;

    public function getCategoryModificationCard(): bool|null;

    public function getProductModificationReference(): string|null|bool;

    public function getProductModificationValue(): string|null|bool;

    public function getModificationAgg(): string;

    public function getInvariable(): array|null|bool;

    public function getProductRootImages(): array|null;

    public function getCategoryUrl(): string|null|bool;

    public function getCategoryName(): string|null|bool;

    public function getProductPrice(): Money|bool;

    public function getProductCurrency(): Currency|bool;

    public function getCategorySectionField(): array|null|bool;

    public function getProductQuantity(): int|null|bool;
}
