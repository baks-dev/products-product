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

namespace BaksDev\Products\Product\Repository\ProductModel;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Reference\Money\Type\Money;

/** @see ProductModelRepository */
final readonly class ProductModelResult
{
    public function __construct(
        private string $id,
        private string $event,
        private bool $active,
        private string|null $active_from,
        private string|null $active_to,
        private string|null $seo_title,
        private string|null $seo_keywords,
        private string|null $seo_description,
        private string|null $product_name,
        private string|null $product_preview,
        private string|null $product_description,
        private string|null $url,
        private string|null $product_offer_reference,
        private string|null $product_offers,
        private string|null $product_images,
        private string|null $category_id,
        private string|null $category_name,
        private string|null $category_url,
        private string|null $category_cover_ext,
        private bool|null $category_cover_cdn,
        private string|null $category_cover_dir,
        private string|null $category_section_field
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function isActiveProduct(): bool
    {
        return $this->active;
    }

    public function getProductActiveFrom(): ?string
    {
        return $this->active_from;
    }

    public function getProductActiveTo(): ?string
    {
        return $this->active_to;
    }

    public function getProductSeoTitle(): ?string
    {
        return $this->seo_title;
    }

    public function getProductSeoKeywords(): ?string
    {
        return $this->seo_keywords;
    }

    public function getProductSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductPreview(): ?string
    {
        return $this->product_preview;
    }

    public function getProductDescription(): ?string
    {
        return $this->product_description;
    }

    public function getProductUrl(): ?string
    {
        return $this->url;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductOffers(): ?array
    {
        if(is_null($this->product_offers))
        {
            return null;
        }

        $offers = json_decode($this->product_offers, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($offers))
        {
            return null;
        }

        return $offers;
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

    public function getCategoryId(): ?CategoryProductUid
    {
        if(is_null($this->category_id))
        {
            return null;
        }

        return new CategoryProductUid($this->category_id);
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getCategoryUrl(): ?string
    {
        return $this->category_url;
    }

    public function getCategoryCoverExt(): ?string
    {
        return $this->category_cover_ext;
    }

    public function isCategoryCoverCdn(): bool|null
    {
        return $this->category_cover_cdn;
    }

    public function getCategoryCoverDir(): ?string
    {
        return $this->category_cover_dir;
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

    /** Helpers */

    /** Минимальная цена из торговых предложений */
    public function getMinPrice(): ?Money
    {
        if(is_null($this->product_offers))
        {
            return null;
        }

        $offers = json_decode($this->product_offers, null, 512, JSON_THROW_ON_ERROR);

        if(null === current($offers))
        {
            return null;
        }

        // Сортировка по возрастанию цены
        usort($offers, function($a, $b) {
            return $a->price <=> $b->price;
        });

        $minPrice = current($offers)->price;

        return new Money($minPrice, true);
    }

    /** Валюта из торговых предложений */
    public function getOfferCurrency(): ?string
    {
        if(is_null($this->product_offers))
        {
            return null;
        }

        $offers = json_decode($this->product_offers, null, 512, JSON_THROW_ON_ERROR);

        if(null === current($offers))
        {
            return null;
        }

        // Сортировка по возрастанию цены
        usort($offers, function($a, $b) {
            return $a->price <=> $b->price;
        });

        return current($offers)->currency;
    }

    /** Торговые предложения только в наличии */
    public function getInStockOffers(): ?array
    {
        if(is_null($this->product_offers))
        {
            return [];
        }

        $offers = json_decode($this->product_offers, true, 512, JSON_THROW_ON_ERROR);

        if(is_null(current($offers)))
        {
            return null;
        }

        $inStock = array_filter($offers, function(array $offer) {
            return $offer['quantity'] !== 0;
        });

        return $inStock;
    }

    /** Торговые предложения не в наличии */
    public function getOutOfStockOffers(): ?array
    {
        if(is_null($this->product_offers))
        {
            return [];
        }

        $offers = json_decode($this->product_offers, true, 512, JSON_THROW_ON_ERROR);

        if(is_null(current($offers)))
        {
            return null;
        }

        $outOfStock = array_filter($offers, function(array $offer) {
            return $offer['quantity'] === 0;
        });

        return $outOfStock;
    }

    /** Изображения, отсортированные по флагу root */
    public function getProductImagesSortByRoot(): array|null
    {
        $images = json_decode($this->product_images, null, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        // Сортировка массива элементов с изображениями по root = true
        usort($images, function($f) {
            return $f->product_img_root === true ? -1 : 1;
        });

        return $images;
    }


    /** Для модели нет Invariable */
    public function getProductInvariableId(): null
    {
        return null;
    }
}
