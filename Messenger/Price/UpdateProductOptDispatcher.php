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

use BaksDev\Products\Product\Entity\Offers\Opt\ProductOfferOpt;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Opt\ProductModificationOpt;
use BaksDev\Products\Product\Entity\Offers\Variation\Opt\ProductVariationOpt;
use BaksDev\Products\Product\Entity\Price\Opt\ProductPriceOpt;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\UpdateProductOfferOptDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\UpdateProductOfferOptHandler;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\Modification\UpdateProductModificationOptDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\Modification\UpdateProductModificationOptHandler;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\UpdateProductVariationOptDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\Variation\UpdateProductVariationOptHandler;
use BaksDev\Products\Product\UseCase\Admin\Price\UpdateProductOptDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\UpdateProductOptHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class UpdateProductOptDispatcher
{
    public function __construct(
        #[Target('productsProductLogger')] private LoggerInterface $logger,
        private UpdateProductOptHandler $UpdateProductOptHandler,
        private UpdateProductOfferOptHandler $UpdateProductOfferOptHandler,
        private UpdateProductVariationOptHandler $UpdateProductVariationOptHandler,
        private UpdateProductModificationOptHandler $UpdateProductModificationOptHandler,
        private CurrentProductIdentifierInterface $CurrentProductIdentifierRepository,
    ) {}

    /**
     * Обновляет оптовую цену продукта в зависимости от вложенности торгового предложения
     */
    public function __invoke(UpdateProductOptMessage $message): void
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
                'products-product: Невозможно изменить оптовую цену товара: карточка не найдена',
                [$message, self::class.':'.__LINE__],
            );

            return;
        }


        /**
         * Если передан Modification - изменяем оптовую цену ModificationOpt
         */
        if(true === ($message->getModification() instanceof ProductModificationUid))
        {
            $updateProductModificationOptDTO = new UpdateProductModificationOptDTO()
                ->setModification($message->getModification())
                ->setOpt($message->getOpt());

            $opt = $this->UpdateProductModificationOptHandler->handle($updateProductModificationOptDTO);

            if($opt instanceof ProductModificationOpt)
            {
                $this->logger->info(
                    'products-product: Оптовая цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($opt))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления оптовой цены модификации товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления оптовой цены: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        
        /**
         * Если Modification === FALSE, но передан Variation - изменяем оптовую цену VariationOpt
         */
        if(
            false === ($message->getModification() instanceof ProductModificationUid) &&
            true === ($message->getVariation() instanceof ProductVariationUid)
        )
        {
            $updateProductVariationOptDTO = new UpdateProductVariationOptDTO()
                ->setVariation($message->getVariation())
                ->setOpt($message->getOpt());

            $opt = $this->UpdateProductVariationOptHandler->handle($updateProductVariationOptDTO);

            if($opt instanceof ProductVariationOpt)
            {
                $this->logger->info(
                    'products-product: Оптовая цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($opt))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления оптовой цены варианта торгового предложения товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления оптовой цены: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /**
         * Если Variation === FALSE, но передан Offer - изменяем оптовую цену OfferOpt
         */
        if(
            false === ($message->getVariation() instanceof ProductVariationUid) &&
            true === ($message->getOffer() instanceof ProductOfferUid)
        )
        {
            $updateProductOfferOptDTO = new UpdateProductOfferOptDTO()
                ->setOffer($message->getOffer())
                ->setOpt($message->getOpt());

            $opt = $this->UpdateProductOfferOptHandler->handle($updateProductOfferOptDTO);

            if($opt instanceof ProductOfferOpt)
            {
                $this->logger->info(
                    'products-product: Оптовая цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($opt))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления оптовой цены торгового предложения товара: ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена для обновления оптовой цены: ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /**
         * Если Offer === FALSE, но передан Event - изменяем оптовую стсть ProductPriceOpt
         */
        if(
            false === ($message->getOffer() instanceof ProductOfferUid) &&
            true === ($message->getEvent() instanceof ProductEventUid)
        )
        {
            $updateProductOptDTO = new UpdateProductOptDTO()
                ->setEvent($message->getEvent())
                ->setOpt($message->getOpt());

            $opt = $this->UpdateProductOptHandler->handle($updateProductOptDTO);

            if($opt instanceof ProductPriceOpt)
            {
                $this->logger->info(
                    'products-product: Оптовая цена товара успешно изменена :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($opt))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления оптовой цены товара : ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Товар не был найден для обновления оптовой цены : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }
    }
}