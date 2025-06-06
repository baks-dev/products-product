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

namespace BaksDev\Products\Product\Repository\Cards\ProductAlternative;

use Generator;

interface ProductAlternativeInterface
{
    public function setMaxResult(int $limit): self;

    public function forOfferValue(string $offer): self;

    public function forVariationValue(string|null $variation): self;

    public function forModificationValue(string|null $modification): self;

    public function byProperty(array|null $property): self;

    /** @return array<int, ProductAlternativeResult>|false */
    public function toArray(): array|false;

    /** @return Generator<int, ProductAlternativeResult>|false */
    public function findAll(): Generator|false;

    /**
     * Метод возвращает альтернативные варианты продукции по значению value торговых предложений
     * @deprecated Используйте метод findAll
     */
    public function fetchAllAlternativeAssociative(
        string $offer,
        ?string $variation,
        ?string $modification,
        ?array $property = null
    ): array|false;
}
