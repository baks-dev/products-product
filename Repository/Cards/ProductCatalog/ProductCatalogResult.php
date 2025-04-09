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

namespace BaksDev\Products\Product\Repository\Cards\ProductCatalog;

use BaksDev\Products\Category\Type\Event\CategoryProductEventUid;
use BaksDev\Products\Product\Repository\Cards\ProductCardInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;

/** @see ProductCatalogRepository */
final readonly class ProductCatalogResult implements ProductCardInterface
{
    public function __construct(
        private string $product_id,
        private string $product_event,
        private string $product_name,
        private string|null $product_url,
        private string|null $product_offer_uid,
        private string|null $product_offer_value,
        private string|null $product_offer_postfix,
        private string|null $product_offer_reference,
        private string|null $product_variation_uid,
        private string|null $product_variation_value,
        private string|null $product_variation_postfix,
        private string|null $product_variation_reference,
        private string|null $product_modification_uid,
        private string|null $product_modification_value,
        private string|null $product_modification_postfix,
        private string|null $product_modification_reference,
        private string|null $product_article,
        private string|null $product_images,
        private int|null $product_price,
        private int|null $product_old_price,
        private string|null $product_currency,
        private string $category_url,
        private string $category_name,
        private string $category_section_field,
        private string|null $product_invariable_id,
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

    public function getProductUrl(): ?string
    {
        return $this->product_url;
    }

    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(null === $this->product_offer_uid)
        {
            return null;
        }

        return new ProductOfferUid($this->product_offer_uid);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductVariationUid(): ProductVariationUid|null
    {
        if(null === $this->product_variation_uid)
        {
            return null;
        }

        return new ProductVariationUid($this->product_variation_uid);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductModificationUid(): ProductModificationUid|null
    {
        if(null === $this->product_modification_uid)
        {
            return null;
        }

        return new ProductModificationUid($this->product_modification_uid);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductArticle(): string|null
    {
        return $this->product_article;
    }

    public function getProductImages(): array|null
    {
        $images = json_decode($this->product_images, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
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

    public function getCategoryUrl(): string
    {
        return $this->category_url;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getCategorySectionField(): array|null
    {
        $sectionFields = json_decode($this->category_section_field, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($sectionFields))
        {
            return null;
        }

        return $sectionFields;
    }

    public function getProductInvariableId(): ProductInvariableUid|null
    {
        if(null === $this->product_invariable_id)
        {
            return null;
        }

        return new ProductInvariableUid($this->product_invariable_id);
    }

    /** Методы - заглушки */

    public function getProductOfferConst(): ProductOfferConst|null|bool
    {
        return false;
    }

    public function getProductOfferName(): string|null|bool
    {
        return false;
    }

    public function getProductVariationConst(): ProductVariationConst|null|bool
    {
        return false;
    }

    public function getProductVariationName(): string|null|bool
    {
        return false;
    }

    public function getProductModificationConst(): ProductModificationConst|null|bool
    {
        return false;
    }

    public function getProductModificationName(): string|null|bool
    {
        return false;
    }

    public function getProductActiveFrom(): string|null|bool
    {
        return false;
    }

    public function getProductReserve(): int|null|bool
    {
        return false;
    }

    public function getProductQuantity(): int|null|bool
    {
        return true;
    }

    public function getProductInvariableOfferConst(): ProductOfferConst|null|bool
    {
        return false;
    }

    public function getProductCategory(): string|null|bool
    {
        return false;
    }

    public function getCategoryEvent(): CategoryProductEventUid|null|bool
    {
        return false;
    }
}
