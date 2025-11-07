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

namespace BaksDev\Products\Product\Messenger\Quantity;

use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\UpdateOfferQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\UpdateOfferQuantityHandler;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\Modification\UpdateModificationQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\Modification\UpdateModificationQuantityHandler;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\UpdateVariationQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation\UpdateVariationQuantityHandler;
use BaksDev\Products\Product\UseCase\Admin\Quantity\UpdateProductQuantityDTO;
use BaksDev\Products\Product\UseCase\Admin\Quantity\UpdateProductQuantityHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляет количество продукта Quantity в зависимости от вложенности торгового предложения
 */
#[AsMessageHandler(priority: 0)]
final readonly class UpdateProductQuantityDispatcher
{
    public function __construct(
        #[Target('productsProductLogger')] private LoggerInterface $logger,
        private UpdateModificationQuantityHandler $UpdateModificationQuantityHandler,
        private UpdateVariationQuantityHandler $UpdateVariationQuantityHandler,
        private UpdateOfferQuantityHandler $UpdateOfferQuantityHandler,
        private UpdateProductQuantityHandler $UpdateProductQuantityHandler,
        private CurrentProductIdentifierInterface $CurrentProductIdentifierRepository,
    ) {}

    /**
     * Метод обновляет количество продукта
     */
    public function __invoke(UpdateProductQuantityMessage $message): void
    {
        /**
         * Получаем текущие идентификаторы на случай изменения карточки
         */
        $CurrentProductIdentifierResult = $this->CurrentProductIdentifierRepository
            ->forEvent($message->getEvent())
            ->forOffer($message->getOffer())
            ->forVariation($message->getVariation())
            ->forModification($message->getModification())
            ->find();

        if(false === $CurrentProductIdentifierResult instanceof CurrentProductIdentifierResult)
        {
            $this->logger->critical(
                'products-product: Невозможно изменить остаток карточки товара: карточка не найдена',
                [$message, self::class.':'.__LINE__],
            );

            return;
        }


        /**
         * Если передан Modification - ищем количественный учет ModificationQuantity
         */

        if(true === ($message->getModification() instanceof ProductModificationUid))
        {
            $updateModificationQuantityDTO = new UpdateModificationQuantityDTO(
                $CurrentProductIdentifierResult->getModification(),
                $message->getQuantity(),
                $message->getReserve(),
            );

            $product = $this->UpdateModificationQuantityHandler->handle($updateModificationQuantityDTO);

            if($product instanceof ProductModificationQuantity)
            {
                $this->logger->info(
                    'products-product: Количество модификации товара успешно изменено :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($product))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления количества модификации товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления остатка: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        /**
         * Если Modification === FALSE, но передан Variation - ищем количественный учет VariationQuantity
         */

        if(
            false === ($message->getModification() instanceof ProductModificationUid) &&
            true === ($message->getVariation() instanceof ProductVariationUid)
        )
        {
            $updateVariationQuantityDTO = new UpdateVariationQuantityDTO(
                $CurrentProductIdentifierResult->getVariation(),
                $message->getQuantity(),
                $message->getReserve(),
            );

            $product = $this->UpdateVariationQuantityHandler->handle($updateVariationQuantityDTO);

            if($product instanceof ProductVariationQuantity)
            {
                $this->logger->info(
                    'products-product: Количество множественного варианта товара успешно изменено:',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($product))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления количества множественного варианта товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Множественный вариант товара не был найден для обновления остатка: ',
                [self::class.':'.__LINE__, var_export($message, true), var_export($product, true)],
            );

            return;
        }


        /**
         * Если Variation === FALSE, но передан Offer - ищем количественный учет OfferQuantity
         */

        if(
            false === ($message->getVariation() instanceof ProductVariationUid) &&
            true === ($message->getOffer() instanceof ProductOfferUid)
        )
        {
            $updateOfferQuantityDTO = new UpdateOfferQuantityDTO(
                $CurrentProductIdentifierResult->getOffer(),
                $message->getQuantity(),
                $message->getReserve(),
            );

            $product = $this->UpdateOfferQuantityHandler->handle($updateOfferQuantityDTO);

            if($product instanceof ProductOfferQuantity)
            {
                $this->logger->info(
                    'products-product: Количество торгового предложения товара успешно изменено:',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($product))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления количества торгового предложения товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Торговое предложение товара не было найдено для обновления остатка: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        /**
         * Если Offer === FALSE, но передан Event - ищем количественный учет ProductPrice
         */

        if(
            false === ($message->getOffer() instanceof ProductOfferUid) &&
            true === ($message->getEvent() instanceof ProductEventUid)
        )
        {
            $updateProductQuantityDTO = new UpdateProductQuantityDTO(
                $CurrentProductIdentifierResult->getEvent(),
                $message->getQuantity(),
                $message->getReserve(),
            );

            $product = $this->UpdateProductQuantityHandler->handle($updateProductQuantityDTO);

            if($product instanceof ProductPrice)
            {
                $this->logger->info(
                    'products-product: Количество товара успешно изменено :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($product))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления количества товара : ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Товар не был найден для обновления остатка : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }
    }
}