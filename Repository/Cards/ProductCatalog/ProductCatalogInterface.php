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

namespace BaksDev\Products\Product\Repository\Cards\ProductCatalog;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Generator;

interface ProductCatalogInterface
{
    public function search(SearchDTO $search): self;

    /** Максимальное количество записей в результате */
    public function maxResult(int $max): self;

    /** Фильтр по категории */
    public function forCategory(CategoryProduct|CategoryProductUid|string $category): self;

    /** Получаем только при условии активности карточки */
    public function onlyActive(): self;

    /** @return Generator<int, ProductCatalogResult>|false */
    public function findAll(string $expr): Generator|false;

    /**
     * Метод возвращает ограниченный по количеству элементов список продуктов из разных категорий
     *
     * /** @return array<int, ProductCatalogResult>|false
     */
    public function toArray(string $expr): array|false;
}