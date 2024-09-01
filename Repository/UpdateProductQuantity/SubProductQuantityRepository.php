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

namespace BaksDev\Products\Product\Repository\UpdateProductQuantity;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Doctrine\DBAL\ParameterType;
use InvalidArgumentException;

final class SubProductQuantityRepository implements SubProductQuantityInterface
{
    private int|false $quantity = false;

    private int|false $reserve = false;

    private ProductEventUid|false $event = false;

    private ProductOfferUid|false $offer = false;

    private ProductVariationUid|false $variation = false;

    private ProductModificationUid|false $modification = false;


    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /** Указываем количество добавленного резерва */
    public function subReserve(int|false $reserve): self
    {
        $this->reserve = $reserve;
        return $this;
    }

    /** Указываем количество добавленного остатка */
    public function subQuantity(int|false $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

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

    public function forOffer(ProductOffer|ProductOfferUid|string|null $offer): self
    {
        if(is_null($offer))
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

    public function forVariation(ProductVariation|ProductVariationUid|string|null $variation): self
    {
        if(is_null($variation))
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

    public function forModification(ProductModification|ProductModificationUid|string|null $modification): self
    {
        if(is_null($modification))
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

    public function update(): int
    {
        if(!$this->event instanceof ProductEventUid)
        {
            throw new InvalidArgumentException('Необходимо вызвать метод forEvent и передать параметр $event');
        }

        if($this->quantity === false && $this->reserve === false)
        {
            throw new InvalidArgumentException('Необходимо вызвать метод subQuantity || subReserve передав количество');
        }

        /**
         * Определяем активное событие продукции
         */

        $current = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $current
            ->from(ProductEvent::class, 'event')
            ->where('event.id = :event')
            ->setParameter(
                'event',
                $this->event,
                ProductEventUid::TYPE
            );

        $current
            ->addSelect('product.event')
            ->join(
                'event',
                Product::class,
                'product',
                'product.id = event.main'
            );

        if($this->offer)
        {
            $current->leftJoin(
                'product',
                ProductOffer::class,
                'offer',
                'offer.id = :offer AND offer.event = product.event'
            )
                ->setParameter(
                    'offer',
                    $this->offer,
                    ProductOfferUid::TYPE
                );


            $current
                ->addSelect('current_offer.id AS offer')
                ->leftJoin(
                    'offer',
                    ProductOffer::class,
                    'current_offer',
                    'current_offer.const = offer.const AND current_offer.event = :event'
                );

            if($this->variation)
            {

                $current->leftJoin(
                    'offer',
                    ProductVariation::class,
                    'variation',
                    'variation.id = :variation AND variation.offer = offer.id'
                )
                    ->setParameter(
                        'variation',
                        $this->variation,
                        ProductVariationUid::TYPE
                    );

                $current
                    ->addSelect('current_variation.id AS variation')
                    ->leftJoin(
                        'variation',
                        ProductVariation::class,
                        'current_variation',
                        'current_variation.const = variation.const AND current_variation.offer = current_offer.id'
                    );


                if($this->modification)
                {
                    $current
                        ->leftJoin(
                            'variation',
                            ProductModification::class,
                            'modification',
                            'modification.id = :modification AND modification.variation = variation.id'
                        )
                        ->setParameter(
                            'modification',
                            $this->modification,
                            ProductModificationUid::TYPE
                        );

                    $current
                        ->addSelect('current_modification.id AS modification')
                        ->leftJoin(
                            'modification',
                            ProductModification::class,
                            'current_modification',
                            'current_modification.const = modification.const AND current_modification.variation = current_variation.id'
                        );
                }
            }
        }

        $result = $current->fetchAssociative();

        /** Если идентификатор события не определен - не выполняем обновление (продукт не найден) */
        if(!isset($result['event']))
        {
            return 0;
        }


        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->update(ProductPrice::class)
            ->where('event = :event')
            ->setParameter(
                'event',
                $result['event'],
                ProductEventUid::TYPE
            );


        if($this->offer && isset($result['offer']))
        {
            $dbal
                ->update(ProductOfferQuantity::class)
                ->where('offer = :offer')
                ->setParameter(
                    'offer',
                    $result['offer'],
                    ProductOfferUid::TYPE
                );
        }

        if($this->variation && isset($result['variation']))
        {
            $dbal
                ->update(ProductVariationQuantity::class)
                ->where('variation = :variation')
                ->setParameter(
                    'variation',
                    $result['variation'],
                    ProductVariationUid::TYPE
                );
        }


        if($this->variation && isset($result['modification']))
        {
            $dbal
                ->update(ProductModificationQuantity::class)
                ->where('modification = :modification')
                ->setParameter(
                    'modification',
                    $result['modification'],
                    ProductModificationUid::TYPE
                );
        }


        /** Если указан остаток - добавляем */
        if($this->quantity)
        {
            $dbal
                ->set('quantity', 'quantity - :quantity')
                ->setParameter('quantity', $this->quantity, ParameterType::INTEGER);

            /** @note !!! Снять остатки можно только если резерв положительный */
            $dbal->andWhere('quantity != 0');
        }

        /** Если указан резерв - добавляем */
        if($this->reserve)
        {
            $dbal
                ->set('reserve', 'reserve - :reserve')
                ->setParameter('reserve', $this->reserve, ParameterType::INTEGER);

            /** @note !!! Снять резерв можно только если резерв положительный */
            $dbal->andWhere('reserve != 0');
        }

        return (int) $dbal->executeStatement();
    }
}
