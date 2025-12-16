<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\ExistProductBarcode;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use InvalidArgumentException;

final class ExistProductBarcodeRepository implements ExistProductBarcodeInterface
{
    public ProductBarcode $barcode;

    private ProductUid $product;

    private ?ProductOfferConst $offer = null;

    private ?ProductVariationConst $variation = null;

    private ?ProductModificationConst $modification = null;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forBarcode(ProductBarcode $barcode): self
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function forProduct(ProductUid $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function forOffer(?ProductOfferConst $offer): self
    {
        $this->offer = $offer;
        return $this;
    }

    public function forVariation(?ProductVariationConst $variation): self
    {
        $this->variation = $variation;
        return $this;
    }

    public function forModification(?ProductModificationConst $modification): self
    {
        $this->modification = $modification;
        return $this;
    }

    public function exist(): bool
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        if(empty($this->barcode))
        {
            throw new InvalidArgumentException('Invalid argument Barcode');
        }

        if(empty($this->product))
        {
            throw new InvalidArgumentException('Invalid argument Product');
        }

        $dbal
            ->from(ProductEvent::class, 'product_event')
            ->where('product_event.main = :product')
            ->setParameter('product', $this->product, ProductUid::TYPE);

        if(false === ($this->offer instanceof ProductOfferConst))
        {
            $dbal
                ->join(
                    'product_event',
                    ProductInfo::class,
                    'product_info',
                    'product_info.event = product_event.id AND product_info.barcode = :barcode'
                )
                ->setParameter('barcode', $this->barcode, ProductBarcode::TYPE);
        }

        if(($this->offer instanceof ProductOfferConst) && false === ($this->variation instanceof ProductVariationConst))
        {
            $dbal
                ->join(
                    'product_event',
                    ProductOffer::class,
                    'product_offer',
                    'product_offer.event = product_event.id AND
                    product_offer.const = :offer AND
                    product_offer.barcode = :barcode'
                )
                ->setParameter('offer', $this->offer, ProductOfferConst::TYPE)
                ->setParameter('barcode', $this->barcode, ProductBarcode::TYPE);
        }
        else
        {
            $dbal
                ->leftJoin(
                    'product_event',
                    ProductOffer::class,
                    'product_offer',
                    'product_offer.event = product_event.id AND
                    product_offer.const = :offer'
                )
                ->setParameter('offer', $this->offer, ProductOfferConst::TYPE);
        }

        if((
            $this->variation instanceof ProductVariationConst) &&
            false === ($this->modification instanceof ProductModificationConst)
        )
        {
            $dbal
                ->join(
                    'product_offer',
                    ProductVariation::class,
                    'product_variation',
                    'product_variation.offer = product_offer.id AND
                    product_variation.const = :variation AND
                    product_variation.barcode = :barcode'
                )
                ->setParameter('variation', $this->variation, ProductVariationConst::TYPE)
                ->setParameter('barcode', $this->barcode, ProductBarcode::TYPE);
        }
        else
        {
            $dbal
                ->leftJoin(
                    'product_offer',
                    ProductVariation::class,
                    'product_variation',
                    'product_variation.offer = product_offer.id AND
                    product_variation.const = :variation'
                )
                ->setParameter('variation', $this->variation, ProductVariationConst::TYPE);
        }

        if($this->modification instanceof ProductModificationConst)
        {
            $dbal
                ->join(
                    'product_variation',
                    ProductModification::class,
                    'product_modification',
                    'product_modification.variation = product_variation.id AND
                    product_modification.const = :modification AND
                    product_modification.barcode = :barcode'
                )
                ->setParameter('modification', $this->modification, ProductModificationConst::TYPE)
                ->setParameter('barcode', $this->barcode, ProductBarcode::TYPE);
        }

        return $dbal->fetchExist();
    }
}