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

namespace BaksDev\Products\Product\Repository\AllProductsIdentifier;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Symfony\Component\Validator\Constraints as Assert;


final readonly class ProductsIdentifierResult
{
    public function __construct(
        private string $product_id,
        private string $product_event,

        private ?string $offer_id,
        private ?string $offer_const,

        private ?string $variation_id,
        private ?string $variation_const,

        private ?string $modification_id,
        private ?string $modification_const,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->product_id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->product_event);
    }

    public function getProductOfferId(): ProductOfferUid|false
    {
        return $this->offer_id ? new ProductOfferUid($this->offer_id) : false;
    }

    public function getProductOfferConst(): ProductOfferConst|false
    {
        return $this->offer_const ? new ProductOfferConst($this->offer_const) : false;
    }

    public function getProductVariationId(): ProductVariationUid|false
    {
        return $this->variation_id ? new ProductVariationUid($this->variation_id) : false;
    }

    public function getProductVariationConst(): ProductVariationConst|false
    {
        return $this->variation_const ? new ProductVariationConst($this->variation_const) : false;
    }

    public function getProductModificationId(): ProductModificationUid|false
    {
        return $this->modification_id ? new ProductModificationUid($this->modification_id) : false;
    }

    public function getProductModificationConst(): ProductModificationConst|false
    {
        return $this->modification_const ? new ProductModificationConst($this->modification_const) : false;
    }
}