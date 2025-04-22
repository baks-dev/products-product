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

namespace BaksDev\Products\Product\Repository\Cards\ModelsOrProductsByCategory;

use BaksDev\Products\Product\Repository\Cards\ModelsOrProductsCardResultInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;

/** @see ModelsOrProductsByCategoryRepository */
final readonly class ModelOrProductByCategoryResult implements ModelsOrProductsCardResultInterface
{
    public function __construct(
        private string $product_id,
        private string $product_event,
        private string $product_name,
        private string $product_url,
        private int $product_sort,
        private string $product_active_from,
        private bool|null $category_offer_card,
        private string|null $product_offer_reference,
        private string|null $product_offer_value,
        private string $offer_agg,
        private bool|null $category_variation_card,
        private string|null $product_variation_reference,
        private string|null $product_variation_value,
        private string $variation_agg,
        private bool|null $category_modification_card,
        private string|null $product_modification_reference,
        private string|null $product_modification_value,
        private string $modification_agg,
        private string $invariable,
        private string $product_root_images,
        private string $category_url,
        private string $category_name,
        private int|null $product_price,
        private int|null $product_old_price,
        private string|null $product_currency,
        private string|null $category_section_field,
        private int|null $product_quantity,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->product_id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->product_event);
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductUrl(): string
    {
        return $this->product_url;
    }

    public function getProductSort(): int
    {
        return $this->product_sort;
    }

    public function getProductActiveFrom(): string
    {
        return $this->product_active_from;
    }

    public function getCategoryOfferCard(): ?bool
    {
        return $this->category_offer_card;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getOfferAgg(): string
    {
        return $this->offer_agg;
    }

    public function getCategoryVariationCard(): ?bool
    {
        return $this->category_variation_card;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getVariationAgg(): string
    {
        return $this->variation_agg;
    }

    public function getCategoryModificationCard(): ?bool
    {
        return $this->category_modification_card;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getModificationAgg(): string
    {
        return $this->modification_agg;
    }

    public function getInvariable(): array|null
    {
        $invariables = json_decode($this->invariable, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($invariables))
        {
            return null;
        }

        return $invariables;
    }

    public function getProductRootImages(): array|null
    {
        $images = json_decode($this->product_root_images, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getCategoryUrl(): string
    {
        return $this->category_url;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getProductPrice(): Money
    {
        return new Money($this->product_price, true);
    }

    public function getProductOldPrice(): Money
    {
        return new Money($this->product_old_price, true);
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getCategorySectionField(): array|null
    {
        $category_section_field = json_decode($this->category_section_field, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($category_section_field))
        {
            return null;
        }

        return $category_section_field;
    }

    public function getProductQuantity(): ?int
    {
        return $this->product_quantity;
    }
}
