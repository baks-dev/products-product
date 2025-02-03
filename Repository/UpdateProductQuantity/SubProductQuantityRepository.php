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
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierInterface;
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


    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly CurrentProductIdentifierInterface $currentProductIdentifier
    ) {}

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

    public function forOffer(ProductOffer|ProductOfferUid|string|false|null $offer): self
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

    public function forVariation(ProductVariation|ProductVariationUid|string|false|null $variation): self
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

    public function forModification(ProductModification|ProductModificationUid|string|false|null $modification): self
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

    public function update(): int|false
    {
        if(false === ($this->event instanceof ProductEventUid))
        {
            throw new InvalidArgumentException('Необходимо вызвать метод forEvent и передать параметр $event');
        }

        if($this->quantity === false && $this->reserve === false)
        {
            throw new InvalidArgumentException('Необходимо вызвать метод subQuantity || subReserve передав количество');
        }

        //$result = $this->getCurrentProductQuantity();

        $CurrentProductIdentifier = $this->currentProductIdentifier
            ->forEvent($this->event)
            ->forOffer($this->offer)
            ->forVariation($this->variation)
            ->forModification($this->modification)
            ->find();

        /** Если идентификатор события не определен - не выполняем обновление (продукт не найден) */
        if(false === $CurrentProductIdentifier)
        {
            return false;
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->update(ProductPrice::class)
            ->where('event = :event')
            ->setParameter(
                'event',
                $CurrentProductIdentifier->getEvent(),
                ProductEventUid::TYPE
            );


        if($this->offer && $CurrentProductIdentifier->getOffer() instanceof ProductOfferUid)
        {
            $dbal
                ->update(ProductOfferQuantity::class)
                ->where('offer = :offer')
                ->setParameter(
                    'offer',
                    $CurrentProductIdentifier->getOffer(),
                    ProductOfferUid::TYPE
                );
        }

        if($this->variation && $CurrentProductIdentifier->getVariation() instanceof ProductVariationUid)
        {
            $dbal
                ->update(ProductVariationQuantity::class)
                ->where('variation = :variation')
                ->setParameter(
                    'variation',
                    $CurrentProductIdentifier->getVariation(),
                    ProductVariationUid::TYPE
                );
        }


        if($this->variation && $CurrentProductIdentifier->getModification() instanceof ProductModificationUid)
        {
            $dbal
                ->update(ProductModificationQuantity::class)
                ->where('modification = :modification')
                ->setParameter(
                    'modification',
                    $CurrentProductIdentifier->getModification(),
                    ProductModificationUid::TYPE
                );
        }


        /** Если указан остаток для списания - снимаем */
        if($this->quantity)
        {
            $dbal
                ->set('quantity', 'quantity - :quantity')
                ->setParameter('quantity', $this->quantity, ParameterType::INTEGER);

            /** @note !!! Снять остатки можно только если положительный */
            $dbal->andWhere('quantity >= :quantity');
        }

        /** Если указан резерв - добавляем */
        if($this->reserve)
        {
            $dbal
                ->set('reserve', 'reserve - :reserve')
                ->setParameter('reserve', $this->reserve, ParameterType::INTEGER);

            /** @note !!! Снять резерв можно только если положительный */
            $dbal->andWhere('reserve > 0');
        }

        return (int) $dbal->executeStatement();
    }
}
