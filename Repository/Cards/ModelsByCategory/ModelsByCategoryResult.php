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

namespace BaksDev\Products\Product\Repository\Cards\ModelsByCategory;

use BaksDev\Products\Product\Repository\Cards\ModelsOrProductsCardResultInterfaceProduct;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;

/** @see ModelsByCategoryRepository */
final readonly class ModelsByCategoryResult implements ModelsOrProductsCardResultInterfaceProduct
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

        private bool|null $category_variation_card,
        private string|null $product_variation_reference,

        private bool|null $category_modification_card,
        private string|null $product_modification_reference,

        private string $category_url,
        private string $category_name,

        private int|string|null $product_price,
        private string|null $product_currency,

        private ?string $product_image,
        private ?string $product_image_ext,
        private ?bool $product_image_cdn,

        private ?string $invariable = null,

        private string|int|null $promotion_price = null,
        private string|null $profile_discount = null,
        private string|null $project_discount = null,
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

    public function getProductOfferValue(): null
    {
        return null;
    }

    public function getOfferAgg(): null
    {
        return null;
    }

    public function getCategoryVariationCard(): ?bool
    {
        return $this->category_variation_card;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductVariationValue(): null
    {
        return null;
    }

    public function getVariationAgg(): null
    {
        return null;
    }

    public function getCategoryModificationCard(): ?bool
    {
        return $this->category_modification_card;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductModificationValue(): null
    {
        return null;
    }

    public function getModificationAgg(): null
    {
        return null;
    }

    public function getInvariable(): null
    {
        return null;
    }

    public function getProductRootImages(): array|null
    {
        return [
            'img_root' => true,
            'img' => $this->product_image,
            'img_ext' => $this->product_image_ext,
            'img_cdn' => $this->product_image_cdn,
        ];
    }

    public function getCategoryUrl(): string
    {
        return $this->category_url;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        /** Кастомная цена */
        $promotionPrice = $this->minPromotionPrice();

        if($promotionPrice instanceof Money)
        {
            $price = $promotionPrice;
        }

        /** Скидка магазина */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        /** Скидка пользователя */
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

        /** Кастомная цена */
        $promotionPrice = $this->minPromotionPrice();

        if($promotionPrice instanceof Money)
        {
            $price = $promotionPrice;
        }

        /** Скидка магазина */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        /** Скидка пользователя */
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

    public function getCategorySectionField(): null
    {
        return null;
    }

    public function getProductQuantity(): null
    {
        return null;
    }

    public function getProductOfferPostfix(): null
    {
        return null;
    }

    public function getProductVariationPostfix(): null
    {
        return null;
    }

    public function getProductModificationPostfix(): null
    {
        return null;
    }

    /** Helpers */

    /**
     * Метод возвращает минимальную стоимость с учетом применения кастомной скидки (надбавки)
     */
    private function minPromotionPrice(): ?Money
    {
        if(true === empty($this->promotion_price))
        {
            return null;
        }

        if(false === json_validate($this->promotion_price))
        {
            return null;
        }

        $promotionPrice = json_decode($this->promotion_price, true, 512, JSON_THROW_ON_ERROR);

        if(is_null($promotionPrice))
        {
            return null;
        }

        $promotionPrice = array_filter($promotionPrice, fn($value) => null !== $value);

        if(true === empty($promotionPrice))
        {
            return null;
        }

        /** Создаем массив с ценами и применяем кастомную скидку (надбавку) */
        $promotionPriceMoney = array_map(function(array $element) {
            $money = new Money($element['price'], true);
            $money->applyString($element['promo']);

            return $money;
        }, $promotionPrice);

        // сортировка по возрастанию цены - от меньшей к большей
        usort($promotionPriceMoney, static function(Money $a, Money $b) {
            return $a->getValue() <=> $b->getValue();
        });

        return current($promotionPriceMoney);
    }
}
