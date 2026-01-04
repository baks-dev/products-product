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

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDetailByEventResult
{
    public function __construct(
        private ?string $id, // идентификатор события
        private ?string $main, // идентификатор корня

        private ?string $url,
        private ?string $product_name,
        private ?string $product_offer_uid,
        private ?string $product_offer_value,
        private ?string $product_offer_reference,
        private ?string $product_offer_name,
        private ?string $product_offer_name_postfix,

        private ?string $product_variation_uid,
        private ?string $product_variation_value,
        private ?string $product_variation_reference,
        private ?string $product_variation_name,
        private ?string $product_variation_name_postfix,

        private ?string $product_modification_uid,
        private ?string $product_modification_value,
        private ?string $product_modification_reference,
        private ?string $product_modification_name,
        private ?string $product_modification_name_postfix,


        private ?string $product_article,
        private ?string $product_card_article,

        private ?string $product_image,
        private ?string $product_image_ext,
        private ?bool $product_image_cdn,

        private ?string $category_name,
        private ?string $category_url,
        private ?string $category_section_field,

        private bool $active,
        private ?string $active_from,
        private ?string $active_to,

        private ?string $product_preview,
        private ?string $product_description,
        private ?string $product_barcode,

        private ?int $product_price,
        private ?int $product_old_price,
        private ?string $product_currency,

        private ?int $product_quantity,
        private ?int $product_reserve,

        private ?string $product_offer_postfix = null,
        private ?string $product_variation_postfix = null,
        private ?string $product_modification_postfix = null,




        private int $product_total = 0,

        private string|null $profile_discount = null,

    ) {}


    public function getProductId(): ProductEventUid
    {
        return new ProductEventUid($this->id);
    }

    public function getProductMain(): ProductUid
    {
        return new ProductUid($this->main);
    }


    public function getProductName(): ?string
    {
        return $this->product_name;
    }


    public function getProductUrl(): ?string
    {
        return $this->url;
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getCategoryUrl(): ?string
    {
        return $this->category_url;
    }

    public function getCategorySectionField(): ?string
    {
        return $this->category_section_field;
    }

    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(is_null($this->product_offer_uid))
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

    public function getProductVariationUid(): ProductVariationUid|null
    {
        if(is_null($this->product_variation_uid))
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

    public function getProductModificationUid(): ProductModificationUid|null
    {
        if(is_null($this->product_modification_uid))
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

    public function getProductModificationName(): ?string
    {
        return $this->product_modification_name;
    }

    public function getProductVariationName(): ?string
    {
        return $this->product_variation_name;
    }

    public function getProductOfferName(): ?string
    {
        return $this->product_offer_name;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductImage(): ?string
    {
        return $this->product_image;
    }

    public function getProductImageExt(): ?string
    {
        return $this->product_image_ext;
    }

    public function isProductImageCdn(): bool
    {
        return $this->product_image_cdn === true;
    }

    public function getProductTotal(): int
    {
        return $this->product_total;
    }

    public function setProductTotal(int $product_total): self
    {
        $this->product_total = $product_total;
        return $this;
    }

    public function getProductOfferNamePostfix(): ?string
    {
        return $this->product_offer_name_postfix;
    }

    public function getProductVariationNamePostfix(): ?string
    {
        return $this->product_variation_name_postfix;
    }

    public function getProductModificationNamePostfix(): ?string
    {
        return $this->product_modification_name_postfix;
    }

    public function isActive(): bool
    {
        return $this->active === true;
    }

    public function getActiveFrom(): ?DateTimeImmutable
    {
        return $this->active_from ? new DateTimeImmutable($this->active_from) : null;
    }

    public function getActiveTo(): ?DateTimeImmutable
    {
        return $this->active_to ? new DateTimeImmutable($this->active_to) : null;
    }

    public function getProductPreview(): ?string
    {
        return $this->product_preview;
    }

    public function getProductDescription(): ?string
    {
        return $this->product_description;
    }

    public function getProductBarcode(): ?string
    {
        return $this->product_barcode;
    }

    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        // применяем скидку пользователя из профиля
        if(false === empty($this->profile_discount))
        {
            $price->applyString($this->profile_discount);
        }

        return $price;
    }

    public function getProductOldPrice(): Money|false
    {
        if(empty($this->product_old_price))
        {
            return false;
        }

        $price = new Money($this->product_old_price, true);

        // применяем скидку пользователя из профиля
        if(false === empty($this->profile_discount))
        {
            $price->applyString($this->profile_discount);
        }

        return $price;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(): int|null
    {
        return $this->product_quantity;
    }

    public function getProductReserve(): int|null
    {
        return $this->product_reserve;
    }

    public function getProductCardArticle(): ?string
    {
        return $this->product_card_article;
    }
}