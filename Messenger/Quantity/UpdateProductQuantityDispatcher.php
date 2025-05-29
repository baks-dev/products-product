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

namespace BaksDev\Products\Product\Messenger\Quantity;

use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
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
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;

#[AsMessageHandler(priority: 0)]
final readonly class UpdateProductQuantityDispatcher
{
    public function __construct(
        private UpdateModificationQuantityHandler $UpdateModificationQuantityHandler,
        private UpdateVariationQuantityHandler $UpdateVariationQuantityHandler,
        private UpdateOfferQuantityHandler $UpdateOfferQuantityHandler,
        private UpdateProductQuantityHandler $UpdateProductQuantityHandler,
        #[Target('productsProductLogger')] private LoggerInterface $logger,
    ) {}

    /**
     * Метод обновляет количесто продукта
     */
    public function __invoke(UpdateProductQuantityMessage $message): void
    {
        if($message->getModification() instanceof ProductModificationUid)
        {
            $dto = new UpdateModificationQuantityDTO()
                ->setModification($message->getModification())
                ->setQuantity($message->getQuantity());

            $product = $this->UpdateModificationQuantityHandler->handle($dto);

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
                    'products-product: Ошибка обновления количества модификации товара : ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Модификация товара не была найдена : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }

        if(empty($message->getModification()) && $message->getVariation() instanceof ProductVariationUid)
        {
            $dto = new UpdateVariationQuantityDTO()
                ->setVariation($message->getVariation())
                ->setQuantity($message->getQuantity());

            $product = $this->UpdateVariationQuantityHandler->handle($dto);

            if($product instanceof ProductVariationQuantity)
            {
                $this->logger->info(
                    'products-product: Количество множественного варианта товара успешно изменено :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($product))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления количества множественного варианта товара : ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Множественный вариант товара не был найден : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }

        if(empty($message->getVariation()) && $message->getOffer() instanceof ProductOfferUid)
        {
            $dto = new UpdateOfferQuantityDTO()
                ->setOffer($message->getOffer())
                ->setQuantity($message->getQuantity());

            $product = $this->UpdateOfferQuantityHandler->handle($dto);

            if($product instanceof ProductOfferQuantity)
            {
                $this->logger->info(
                    'products-product: Количество торгового предложения товара успешно изменено :',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            if(is_string($product))
            {
                $this->logger->critical(
                    'products-product: Ошибка обновления количества торгового предложения товара : ',
                    [self::class.':'.__LINE__, var_export($message, true)],
                );

                return;
            }

            $this->logger->critical(
                'products-product: Торговое предложение товара не было найдено : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }

        if(empty($message->getOffer()) && $message->getEvent() instanceof ProductEventUid)
        {
            $dto = new UpdateProductQuantityDTO()
                ->setEvent($message->getEvent())
                ->setQuantity($message->getQuantity());

            $product = $this->UpdateProductQuantityHandler->handle($dto);

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
                'products-product: Товар не был найден : ',
                [self::class.':'.__LINE__, var_export($message, true)],
            );
        }
    }
}