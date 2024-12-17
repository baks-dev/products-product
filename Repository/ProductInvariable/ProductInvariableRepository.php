<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\ProductInvariable;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use InvalidArgumentException;


final class ProductInvariableRepository implements ProductInvariableInterface
{

    /** ID продукта */
    private ProductUid|false $product = false;

    /** Константа ТП */
    private ProductOfferConst|false $offer = false;

    /** Константа множественного варианта */
    private ProductVariationConst|false $variation = false;

    /** Константа модификации множественного варианта */
    private ProductModificationConst|false $modification = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function product(ProductUid|string $product): self
    {
        if($product instanceof ProductUid)
        {
            $this->product = $product;
        }

        if(is_string($product))
        {
            $this->product = new ProductUid($product);
        }

        return $this;
    }

    public function offer(ProductOfferConst|string|false|null $offer): self
    {
        if(empty($offer))
        {
            $this->offer = false;
        }

        if($offer instanceof ProductOfferConst)
        {
            $this->offer = $offer;
        }

        if(is_string($offer))
        {
            $this->offer = new ProductOfferConst($offer);
        }

        return $this;
    }

    public function variation(ProductVariationConst|string|false|null $variation): self
    {
        if(empty($variation))
        {
            $this->variation = false;
        }

        if($variation instanceof ProductVariationConst)
        {
            $this->variation = $variation;
        }

        if(is_string($variation))
        {
            $this->variation = new ProductVariationConst($variation);
        }

        return $this;
    }

    public function modification(ProductModificationConst|string|false|null $modification): self
    {
        if(empty($modification))
        {
            $this->modification = false;
        }

        if($modification instanceof ProductModificationConst)
        {
            $this->modification = $modification;
        }

        if(is_string($modification))
        {
            $this->modification = new ProductModificationConst($modification);
        }

        return $this;
    }

    /**
     * Метод возвращает идентификатор Invariable продукта
     */
    public function find(): ProductInvariableUid|false
    {
        if($this->product === false)
        {
            throw new InvalidArgumentException('Product not found.');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->select('invariable.id');

        $dbal
            ->from(ProductInvariable::class, 'invariable')
            ->where('invariable.product = :product')
            ->setParameter('product', $this->product, ProductUid::TYPE);

        if(false === $this->offer)
        {
            $dbal->andWhere('invariable.offer IS NULL');
        }
        else
        {
            $dbal
                ->andWhere('invariable.offer = :offer')
                ->setParameter('offer', $this->offer, ProductOfferConst::TYPE);
        }

        if(false === $this->variation)
        {
            $dbal->andWhere('invariable.variation IS NULL');
        }
        else
        {
            $dbal
                ->andWhere('invariable.variation = :variation')
                ->setParameter('variation', $this->variation, ProductVariationConst::TYPE);
        }

        if(false === $this->modification)
        {
            $dbal->andWhere('invariable.modification IS NULL');
        }
        else
        {
            $dbal
                ->andWhere('invariable.modification = :modification')
                ->setParameter('modification', $this->modification, ProductModificationConst::TYPE);
        }

        $invariable = $dbal
            //->enableCache('products-product', 86400)
            ->fetchOne();

        return $invariable ? new ProductInvariableUid($invariable) : false;
    }
}