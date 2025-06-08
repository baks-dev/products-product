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

namespace BaksDev\Products\Product\Repository\CurrentProductIdentifier;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use InvalidArgumentException;

final class CurrentProductIdentifierByConstRepository implements CurrentProductIdentifierByConstInterface
{
    private ProductUid|false $product = false;

    private ProductOfferConst|false $offer = false;

    private ProductVariationConst|false $variation = false;

    private ProductModificationConst|false $modification = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProduct(ProductUid|string $product): self
    {
        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        $this->product = $product;

        return $this;
    }

    public function forOfferConst(ProductOfferConst|string|null|false $offer): self
    {
        if(is_null($offer) || $offer === false)
        {
            $this->offer = false;
            return $this;
        }

        if(is_string($offer))
        {
            $offer = new ProductOfferConst($offer);
        }

        $this->offer = $offer;

        return $this;
    }

    public function forVariationConst(ProductVariationConst|string|null|false $variation): self
    {
        if(is_null($variation) || $variation === false)
        {
            $this->variation = false;
            return $this;
        }

        if(is_string($variation))
        {
            $variation = new ProductVariationConst($variation);
        }


        $this->variation = $variation;

        return $this;
    }

    public function forModificationConst(ProductModificationConst|string|null|false $modification): self
    {
        if(is_null($modification) || $modification === false)
        {
            $this->modification = false;
            return $this;
        }

        if(is_string($modification))
        {
            $modification = new ProductModificationConst($modification);
        }

        $this->modification = $modification;

        return $this;
    }


    /**
     * Метод возвращает активные идентификаторы продукта по событию и идентификаторов торгового предложения
     */
    public function find(): CurrentProductIdentifierResult|false
    {
        if(!$this->product instanceof ProductUid)
        {
            throw new InvalidArgumentException('Необходимо вызвать метод forProduct и передать параметр $product');
        }

        /**
         * Определяем активное событие продукции
         */

        $current = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $current
            ->addSelect('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter(
                'product',
                $this->product,
                ProductUid::TYPE,
            );


        /**
         * ProductOffer
         */


        $current
            ->addSelect('current_offer.id AS offer')
            ->addSelect('current_offer.const AS offer_const')
            ->addSelect('current_offer.value AS offer_value')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'current_offer',
                'current_offer.event = product.event'.
                (($this->offer instanceof ProductOfferConst) ? ' AND current_offer.const = :offer_const' : ''),
            );


        if($this->offer instanceof ProductOfferConst)
        {
            $current->setParameter(
                'offer_const',
                $this->offer,
                ProductOfferConst::TYPE,
            );
        }


        /**
         * ProductVariation
         */

        $current
            ->addSelect('current_variation.id AS variation')
            ->addSelect('current_variation.const AS variation_const')
            ->addSelect('current_variation.value AS variation_value')
            ->leftJoin(
                'current_offer',
                ProductVariation::class,
                'current_variation',
                'current_variation.offer = current_offer.id'.
                (($this->variation instanceof ProductVariationConst) ? ' AND current_variation.const = :variation_const' : ''),
            );

        if($this->variation instanceof ProductVariationConst)
        {
            $current->setParameter(
                'variation_const',
                $this->variation,
                ProductVariationConst::TYPE,
            );
        }


        /**
         * ProductModification
         */

        $current
            ->addSelect('current_modification.id AS modification')
            ->addSelect('current_modification.const AS modification_const')
            ->addSelect('current_modification.value AS modification_value')
            ->leftJoin(
                'current_variation',
                ProductModification::class,
                'current_modification',
                'current_modification.variation = current_variation.id'.
                (($this->modification instanceof ProductModificationConst) ? ' AND current_modification.const = :modification_const' : ''),
            );

        if($this->modification instanceof ProductModificationConst)
        {
            $current->setParameter(
                'modification_const',
                $this->modification,
                ProductModificationConst::TYPE,
            );
        }


        /** Product Invariable */
        $current
            ->addSelect('product_invariable.id AS product_invariable')
            ->leftJoin(
                'current_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id
                    AND
                        CASE 
                            WHEN current_offer.const IS NOT NULL 
                            THEN product_invariable.offer = current_offer.const
                            ELSE product_invariable.offer IS NULL
                        END
                    AND 
                        CASE
                            WHEN current_variation.const IS NOT NULL 
                            THEN product_invariable.variation = current_variation.const
                            ELSE product_invariable.variation IS NULL
                        END
                    AND
                        CASE
                            WHEN current_modification.const IS NOT NULL 
                            THEN product_invariable.modification = current_modification.const
                            ELSE product_invariable.modification IS NULL
                        END
                ');


        return $current
            ->enableCache('products-product')
            ->fetchHydrate(CurrentProductIdentifierResult::class);

    }
}
