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

namespace BaksDev\Products\Product\Type\RedisTags;

use BaksDev\Products\Product\Repository\Search\AllProductsToIndex\AllProductsToIndexResult;
use BaksDev\Search\RedisSearchDocuments\EntityDocument;
use BaksDev\Search\Repository\RedisToIndexResult\RedisToIndexResultInterface;
use BaksDev\Search\Type\RedisTags\Collection\RedisSearchIndexTagInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.redis-tags')]
class ProductRedisSearchTag extends AbstractProductRedisSearchTag implements RedisSearchIndexTagInterface
{

    public const string TAG = 'products-product';

    public const string INDEX_ID = 'id';

    public function getValue(): string
    {
        return self::TAG;
    }

    public function getIndexId(): string
    {
        return self::INDEX_ID;
    }

    public static function sort(): int
    {
        return 1;
    }

    /**
     * Подготовка сущности по продукту
     */
    public function prepareDocument(RedisToIndexResultInterface $item): EntityDocument
    {
        /** @var AllProductsToIndexResult $item */
        $documentId = $item->getProductId();
        $entityDocument = new EntityDocument($documentId);

        $transformed_value = $item->getTransformedValue($this->switcher);

        $entityDocument
            ->setEntityIndex($transformed_value)
            ->setSearchTag($this->getValue());

        return $entityDocument;
    }

}