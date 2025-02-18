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

namespace BaksDev\Products\Product\Repository\ExistProductArticle;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;


final class ExistProductArticleRepository implements ExistProductArticleInterface
{
    private bool $offer = false;

    private bool $variation = false;

    private bool $modification = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function onlyOffer(): self
    {
        $this->offer = true;
        $this->variation = false;
        $this->modification = false;
        return $this;
    }

    public function onlyVariation(): self
    {
        $this->offer = false;
        $this->variation = true;
        $this->modification = false;
        return $this;
    }

    public function onlyModification(): self
    {
        $this->offer = false;
        $this->variation = false;
        $this->modification = true;
        return $this;
    }

    public function onlyCard(): self
    {
        $this->offer = false;
        $this->variation = false;
        $this->modification = false;
        return $this;
    }

    public function isOffer(): bool
    {
        return $this->offer;
    }

    public function isVariation(): bool
    {
        return $this->variation;
    }

    public function isModification(): bool
    {
        return $this->modification;
    }

    public function isCard(): bool
    {
        return $this->offer === false && $this->variation === false && $this->modification === false;
    }

    /**
     * Метод проверяем в указанной таблице наличие артикула
     */
    public function exist(string $article): bool
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        if($this->modification)
        {
            $dbal->from(ProductModification::class, 'exist');

            $dbal->join(
                'exist',
                ProductVariation::class,
                'variation',
                'variation.id = exist.variation'
            );

            $dbal->join(
                'variation',
                ProductOffer::class,
                'offer',
                'offer.id = variation.offer'
            );

            $dbal->join(
                'offer',
                Product::class,
                'product',
                'product.event = offer.event'
            );


        }

        if($this->variation)
        {
            $dbal->from(ProductVariation::class, 'exist');

            $dbal->join(
                'exist',
                ProductOffer::class,
                'offer',
                'offer.id = exist.offer'
            );

            $dbal->join(
                'offer',
                Product::class,
                'product',
                'product.event = offer.event'
            );

        }

        if($this->offer)
        {
            $dbal->from(ProductOffer::class, 'exist');

            $dbal->join(
                'exist',
                Product::class,
                'product',
                'product.event = exist.event'
            );
        }

        if(false === $this->modification && false === $this->variation && false === $this->offer)
        {
            $dbal->from(ProductInfo::class, 'exist');

            $dbal->join(
                'exist',
                Product::class,
                'product',
                'product.event = exist.event'
            );
        }

        $dbal
            ->where('exist.article = :article')
            ->setParameter('article', $article);

        return $dbal->fetchExist();
    }
}
