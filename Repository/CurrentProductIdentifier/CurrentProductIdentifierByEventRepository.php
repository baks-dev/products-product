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
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use InvalidArgumentException;

final class CurrentProductIdentifierByEventRepository implements CurrentProductIdentifierByEventInterface
{
    private ProductEventUid|false $event = false;

    private ProductOfferUid|false $offer = false;

    private ProductVariationUid|false $variation = false;

    private ProductModificationUid|false $modification = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forEvent(ProductEvent|ProductEventUid|string $event): self
    {
        if($event instanceof ProductEvent)
        {
            $event = $event->getId();
        }

        if(is_string($event))
        {
            $event = new ProductEventUid($event);
        }

        $this->event = $event;

        return $this;
    }

    public function forOffer(ProductOffer|ProductOfferUid|string|null|false $offer): self
    {
        if(empty($offer))
        {
            $this->offer = false;
            return $this;
        }

        if(is_string($offer))
        {
            $offer = new ProductOfferUid($offer);
        }

        if($offer instanceof ProductOffer)
        {
            $offer = $offer->getId();
        }

        $this->offer = $offer;

        return $this;
    }

    public function forVariation(ProductVariation|ProductVariationUid|string|null|false $variation): self
    {
        if(empty($variation))
        {
            $this->variation = false;
            return $this;
        }

        if(is_string($variation))
        {
            $variation = new ProductVariationUid($variation);
        }

        if($variation instanceof ProductVariation)
        {
            $variation = $variation->getId();
        }


        $this->variation = $variation;

        return $this;
    }

    public function forModification(ProductModification|ProductModificationUid|string|null|false $modification): self
    {
        if(empty($modification))
        {
            $this->modification = false;
            return $this;
        }

        if(is_string($modification))
        {
            $modification = new ProductModificationUid($modification);
        }

        if($modification instanceof ProductModification)
        {
            $modification = $modification->getId();
        }

        $this->modification = $modification;

        return $this;
    }


    /**
     * Метод возвращает активные идентификаторы продукта по событию и идентификаторов торгового предложения
     */
    public function find(): CurrentProductIdentifierResult|false
    {
        if(!$this->event instanceof ProductEventUid)
        {
            throw new InvalidArgumentException('Необходимо вызвать метод forEvent и передать параметр $event');
        }

        /**
         * Определяем активное событие продукции
         */

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(ProductEvent::class, 'event')
            ->where('event.id = :event')
            ->setParameter(
                'event',
                $this->event,
                ProductEventUid::TYPE,
            );

        $dbal
            ->addSelect('product.id')
            ->addSelect('product.event')
            ->join(
                'event',
                Product::class,
                'product',
                'product.id = event.main',
            );

        /** Объявляем предварительно переменные Invariable */
        $fromAlias = 'product';
        $conditionOffer = 'product_invariable.offer IS NULL';
        $conditionVariation = 'product_invariable.variation IS NULL';
        $conditionModification = 'product_invariable.modification IS NULL';

        if($this->offer instanceof ProductOfferUid)
        {
            /** получаем событие ProductOffer  */
            $dbal->join(
                'product',
                ProductOffer::class,
                'offer',
                'offer.id = :offer',
            )
                ->setParameter(
                    'offer',
                    $this->offer,
                    ProductOfferUid::TYPE,
                );


            /** Определяем активное состояние ProductOffer */
            $dbal
                ->addSelect('current_offer.id AS offer')
                ->addSelect('current_offer.const AS offer_const')
                ->addSelect('current_offer.value AS offer_value')
                ->join(
                    'offer',
                    ProductOffer::class,
                    'current_offer',
                    '
                        current_offer.const = offer.const 
                        AND current_offer.event = product.event
                    ',
                );

            $fromAlias = 'current_offer';
            $conditionOffer = 'product_invariable.offer = offer.const';


            /**
             *  ProductVariation
             */

            if($this->variation instanceof ProductVariationUid)
            {
                /** получаем событие ProductVariation  */
                $dbal->join(
                    'offer',
                    ProductVariation::class,
                    'variation',
                    '
                        variation.id = :variation 
                        AND variation.offer = offer.id
                    ',
                )
                    ->setParameter(
                        'variation',
                        $this->variation,
                        ProductVariationUid::TYPE,
                    );

                /** Определяем активное состояние ProductVariation */
                $dbal
                    ->addSelect('current_variation.id AS variation')
                    ->addSelect('current_variation.const AS variation_const')
                    ->addSelect('current_variation.value AS variation_value')
                    ->join(
                        'variation',
                        ProductVariation::class,
                        'current_variation',
                        '
                            current_variation.const = variation.const 
                            AND current_variation.offer = current_offer.id
                        ',
                    );

                $fromAlias = 'current_variation';
                $conditionVariation = 'product_invariable.variation = variation.const';


                /**
                 *  ProductModification
                 */

                if($this->modification instanceof ProductModificationUid)
                {
                    /** получаем событие ProductModification  */
                    $dbal
                        ->join(
                            'variation',
                            ProductModification::class,
                            'modification',
                            '
                                modification.id = :modification 
                                AND modification.variation = variation.id
                            ',
                        )
                        ->setParameter(
                            'modification',
                            $this->modification,
                            ProductModificationUid::TYPE,
                        );

                    $dbal
                        ->addSelect('current_modification.id AS modification')
                        ->addSelect('current_modification.const AS modification_const')
                        ->addSelect('current_modification.value AS modification_value')
                        ->join(
                            'modification',
                            ProductModification::class,
                            'current_modification',
                            '
                                current_modification.const = modification.const 
                                AND current_modification.variation = current_variation.id
                            ',
                        );


                    $fromAlias = 'current_modification';
                    $conditionModification = 'product_invariable.modification = modification.const';
                }
            }
        }

        /** Product Invariable */
        $dbal
            ->addSelect('product_invariable.id AS product_invariable')
            ->leftJoin(
                $fromAlias,
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id
                    AND '.$conditionOffer.'
                    AND '.$conditionVariation.'
                    AND '.$conditionModification.'
                ');


        return $dbal->fetchHydrate(CurrentProductIdentifierResult::class);

    }
}
