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

namespace BaksDev\Products\Product\Messenger\Price;

use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByEventInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\UpdateProductOfferPriceDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\UpdateProductOfferPriceHandler;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\Modification\UpdateProductModificationPriceDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\Modification\UpdateProductModificationPriceHandler;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\UpdateProductVariationPriceDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\UpdateProductVariationPriceHandler;
use BaksDev\Products\Product\UseCase\Admin\Price\UpdateProductPriceDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\UpdateProductPriceHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class UpdateProductPriceDispatcher
{
    public function __construct(
        #[Target('productsProductLogger')] private LoggerInterface $logger,
        private UpdateProductPriceHandler $UpdateProductPriceHandler,
        private UpdateProductOfferPriceHandler $UpdateProductOfferPriceHandler,
        private UpdateProductVariationPriceHandler $UpdateProductVariationPriceHandler,
        private UpdateProductModificationPriceHandler $UpdateProductModificationPriceHandler,
        private CurrentProductIdentifierByEventInterface $CurrentProductIdentifierRepository,
    ) {}

    /**
     * Обновляет цену продукта в зависимости от вложенности торгового предложения
     */
    public function __invoke(UpdateProductPriceMessage $message): void
    {
        /**
         * Получаем текущие идентификаторы на случай изменения карточки
         */
        $currentProductIdentifierResult = $this->CurrentProductIdentifierRepository
            ->forEvent($message->getEvent())
            ->forOffer($message->getOffer())
            ->forVariation($message->getVariation())
            ->forModification($message->getModification())
            ->find();

        if(false === ($currentProductIdentifierResult instanceof CurrentProductIdentifierResult))
        {
            $this->logger->critical(
                'products-product: Невозможно изменить цену товара: карточка не найдена',
                [$message, self::class.':'.__LINE__],
            );

            return;
        }


        /**
         * Если передан Modification - изменяем цену ModificationPrice
         */
        if(true === ($message->getModification() instanceof ProductModificationUid))
        {
            $updateProductModificationPriceDTO = new UpdateProductModificationPriceDTO()
                ->setModification($message->getModification())
                ->setPrice($message->getPrice());

            $price = $this->UpdateProductModificationPriceHandler->handle($updateProductModificationPriceDTO);

            if($price instanceof ProductModificationPrice)
            {
                $this->logger->info(
                    'products-product: Цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($price))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления цены модификации товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления цены: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /**
         * Если Modification === FALSE, но передан Variation - изменяем цену VariationPrice
         */
        if(
            false === ($message->getModification() instanceof ProductModificationUid) &&
            true === ($message->getVariation() instanceof ProductVariationUid)
        )
        {
            $updateProductVariationPriceDTO = new UpdateProductVariationPriceDTO()
                ->setVariation($message->getVariation())
                ->setPrice($message->getPrice());

            $price = $this->UpdateProductVariationPriceHandler->handle($updateProductVariationPriceDTO);

            if($price instanceof ProductVariationPrice)
            {
                $this->logger->info(
                    'products-product: Цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($price))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления цены варианта торгового предложения товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления цены: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /**
         * Если Variation === FALSE, но передан Offer - изменяем цену OfferPrice
         */
        if(
            false === ($message->getVariation() instanceof ProductVariationUid) &&
            true === ($message->getOffer() instanceof ProductOfferUid)
        )
        {
            $updateProductOfferPriceDTO = new UpdateProductOfferPriceDTO()
                ->setOffer($message->getOffer())
                ->setPrice($message->getPrice());

            $price = $this->UpdateProductOfferPriceHandler->handle($updateProductOfferPriceDTO);

            if($price instanceof ProductOfferPrice)
            {
                $this->logger->info(
                    'products-product: Цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($price))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления цены торгового предложения товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления цены: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /**
         * Если Offer === FALSE, но передан Event - изменяем цену ProductPrice
         */
        if(
            false === ($message->getOffer() instanceof ProductOfferUid) &&
            true === ($message->getEvent() instanceof ProductEventUid)
        )
        {
            $updateProductPriceDTO = new UpdateProductPriceDTO()
                ->setEvent($message->getEvent())
                ->setPrice($message->getPrice());

            $price = $this->UpdateProductPriceHandler->handle($updateProductPriceDTO);

            if($price instanceof ProductPrice)
            {
                $this->logger->info(
                    'products-product: Цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($price))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления цены товара : ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Товар не был найден для обновления цены : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }
    }
}