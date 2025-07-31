<?php

namespace BaksDev\Products\Product\Repository\ProductsDetailByUids;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;

class ProductsDetailByUidsResult
{
    public function __construct(
        private ?string $id,
        private ?string $main,
        private ?string $url,
        private ?string $product_name,

        private ?string $product_article,

        private ?string $product_image,
        private ?string $product_image_ext,

        private ?string $category_name,
        private ?string $category_url,
        private ?string $category_section_field,

        private ?string $product_offer_uid = null,
        private ?string $product_offer_value = null,
        private ?string $product_offer_reference = null,
        private ?string $product_offer_name = null,


        private ?string $product_variation_uid = null,
        private ?string $product_variation_value = null,
        private ?string $product_variation_reference = null,
        private ?string $product_variation_name = null,

        private ?string $product_modification_uid = null,
        private ?string $product_modification_value = null,
        private ?string $product_modification_reference = null,
        private ?string $product_modification_name = null,

        private ?string $product_offer_postfix = null,
        private ?string $product_variation_postfix = null,
        private ?string $product_modification_postfix = null,
        private ?bool $product_image_cdn = false,

        private int $product_total = 0,

    ) {}


    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getMain(): ?string
    {
        return $this->main;
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
        return $this->product_image_cdn;
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


}