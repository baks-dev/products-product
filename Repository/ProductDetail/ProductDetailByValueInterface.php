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

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;

interface ProductDetailByValueInterface
{
    /**
     * Метод возвращает детальную информацию о продукте и его заполненному значению ТП, вариантов и модификаций.
     *
     * @param ?string $offer - значение торгового предложения
     * @param ?string $variation - значение множественного варианта ТП
     * @param ?string $modification - значение модификации множественного варианта ТП
     */
    public function fetchProductAssociative(
        ProductUid $product,
        ?string $offer = null,
        ?string $variation = null,
        ?string $modification = null,
        ?string $postfix = null,
    ): array|bool;


    /**
     * Метод возвращает детальную информацию о продукте и его заполненному значению ТП, вариантов и модификаций.
     *
     * @param ?string $offer - значение торгового предложения
     * @param ?string $variation - значение множественного варианта ТП
     * @param ?string $modification - значение модификации множественного варианта ТП
     */
    public function fetchProductEventAssociative(
        ProductEventUid $event,
        ?string $offer = null,
        ?string $variation = null,
        ?string $modification = null,
    ): array|bool;

}