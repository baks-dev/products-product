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

use BaksDev\Products\Product\Repository\Search\AllProductsToIndex\AllProductsToIndexResult;
use BaksDev\Search\EntityDocument\EntityDocumentInterface;
use BaksDev\Search\Repository\DataToIndexResult\DataToIndexResultInterface;
use BaksDev\Search\SearchDocuments\PrepareDocumentInterface;
use BaksDev\Search\SearchIndex\SearchIndexTagInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.search-tags')]
class ProductVariationSearchTag extends AbstractProductSearchTag implements SearchIndexTagInterface, PrepareDocumentInterface
{

    public const string TAG = 'products-product';

    public static function sort(): int
    {
        return 3;
    }

    public function getModuleName(): string
    {
        return self::TAG;
    }

    /**
     * Подготовка сущности по множественным вариантам торгового предложения
     */
    public function prepareDocument(DataToIndexResultInterface $item): EntityDocumentInterface
    {
        /** @var AllProductsToIndexResult $item */
        $documentId = $item->getProductVariationId();

        /** @see AbstractProductSearchTag */
        $this->entityDocument->setEntityId($documentId);

        $textSearch = $item->setTextSearch($this->switcher);

        $this->entityDocument
            ->setEntityIndex($textSearch)
            ->setSearchTag($this->getModuleName());

        return $this->entityDocument;

    }

}