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

namespace BaksDev\Products\Product\Type\SearchTags;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Switcher\Switcher;
use BaksDev\Search\Repository\DataToIndex\DataToIndexInterface;
use BaksDev\Search\Repository\SearchRepository\SearchRepositoryInterface;
use BaksDev\Search\EntityDocument\EntityDocumentInterface;
use Generator;

abstract class AbstractProductSearchTag
{
    public function __construct(
        protected readonly DataToIndexInterface $allProductsToIndexRepository,
        protected readonly SearchRepositoryInterface $searchAllProducts,
        protected readonly Switcher $switcher,
        protected readonly EntityDocumentInterface $entityDocument
    ) {}

    /**
     * Возвращает сущности для индексации
     */
    public function getRepositoryData(): false|Generator
    {
        return $this->allProductsToIndexRepository->findAll();
    }

    /**
     * Возвращает сущности для поиска
     */
    public function getRepositorySearchData(SearchDTO $search, int|bool $max_results = false): false|Generator
    {
        $repository = $this->searchAllProducts
            ->search($search);

        if($max_results !== false)
        {
            $repository->maxResult($max_results);
        }

        return $repository->findAll();
    }

}