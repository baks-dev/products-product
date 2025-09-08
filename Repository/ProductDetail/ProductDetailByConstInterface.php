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

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;

interface ProductDetailByConstInterface
{
    public function product(Product|ProductUid|string $product): self;

    public function offerConst(ProductOffer|ProductOfferConst|string|null|false $offer): self;

    public function variationConst(ProductVariation|ProductVariationConst|string|null|false $variation): self;

    public function modificationConst(ProductModification|ProductModificationConst|string|null|false $modification
    ): self;

    /**
     * Метод возвращает детальную информацию о продукте по его неизменяемым идентификаторам по иерархии
     * 1. модификаций множественного варианта торгового предложения
     * 2. множественного варианта торгового предложения
     * 3. торгового предложения,
     * возвращая массив
     */
    public function find(): array|false;

    /**
     * Метод возвращает детальную информацию о продукте по его неизменяемым идентификаторам по иерархии
     * 1. модификаций множественного варианта торгового предложения
     * 2. множественного варианта торгового предложения
     * 3. торгового предложения,
     * гидрируя всё на объект резалта
     * @see ProductDetailByConstResult
     */
    public function findResult(): ProductDetailByConstResult|false;
}
