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

namespace BaksDev\Products\Product\Repository\AllProductsIdentifier;

use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Generator;

interface AllProductsIdentifierInterface
{
    /** Применяет фильтр по идентификатору продукта */
    public function forProduct(Product|ProductUid|string $product): self;

    /** Применяет фильтр по константе торгового предложения */
    public function forOfferConst(ProductOfferConst|string $offerConst): self;

    /** Применяет фильтр по константе множественного варианта торгового предложения */
    public function forVariationConst(ProductVariationConst|string $offerVariation): self;

    /** Применяет фильтр по константе модификатора множественного варианта торгового предложения */
    public function forModificationConst(ProductModificationConst|string $offerModification): self;



    /**
     * Метод возвращает все идентификаторы продукции с её торговыми предложениями
     */

    /** @return Generator<int, ProductsIdentifierResult>|false */
    public function findAll(): Generator|false;

    /**  @return array<int, ProductsIdentifierResult>|false */
    public function toArray(): array|false;


    /**
     * Метод возвращает все идентификаторы продукции с её торговыми предложениями
     */
    public function findAllArray(): Generator|false;
}
