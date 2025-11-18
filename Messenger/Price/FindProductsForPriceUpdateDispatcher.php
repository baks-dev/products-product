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

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\AllProductsForPriceUpdate\AllProductsForPriceUpdateInterface;
use BaksDev\Products\Product\Repository\AllProductsForPriceUpdate\AllProductsForPriceUpdateResult;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Api\CbrCurrencyRequest;
use BaksDev\Reference\Currency\Type\Currencies\RUR;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class FindProductsForPriceUpdateDispatcher
{
    public function __construct(
        private AllProductsForPriceUpdateInterface $AllProductsForPriceUpdateRepository,
        private MessageDispatchInterface $messageDispatch,
        private CbrCurrencyRequest $CbrCurrencyRequest,
    ) {}

    /**
     * Автоматически обновляем оптовую и розничную цену продуктов в соответствии с настройками их категории
     */
    public function __invoke(FindProductsForPriceUpdateMessage $message): void
    {
        /** Находим все продукты, для категорий которых присутсвует настройка автоматического рассчета оптовой и/или
         * розничной цены
         */
        $products = $this->AllProductsForPriceUpdateRepository->findAll();

        /** @var AllProductsForPriceUpdateResult $product */
        foreach ($products as $product)
        {
            /**
             * Находим наибольшую вложенность и для нее себестоимость продукта, а также валюту себестоимости и валюту
             * розничной и оптовой цен
             */
            $cost = null;
            $costCurrency = '';
            $priceCurrency = '';


            /** Если есть модификация торгового предложения */
            if($product->getModification() instanceof ProductModificationUid)
            {
                $cost = $product->getProductModificationCost();
                $costCurrency = $product
                    ->getProductModificationCostCurrency()
                    ->getCurrencyValueUpper();

                $priceCurrency = $product
                    ->getProductModificationPriceCurrency()
                    ->getCurrencyValueUpper();
            }


            /** Если нет модификации, но есть вариант ТП */
            if(false === $product->getModification() && $product->getVariation() instanceof ProductVariationUid)
            {
                $cost = $product->getProductVariationCost();
                $costCurrency = $product
                    ->getProductVariationCostCurrency()
                    ->getCurrencyValueUpper();

                $priceCurrency = $product
                    ->getProductVariationPriceCurrency()
                    ->getCurrencyValueUpper();
            }


            /** Если нет варианта, но есть ТП */
            if(false === $product->getVariation() && $product->getOffer() instanceof ProductOfferUid)
            {
                $cost = $product->getProductOfferCost();
                $costCurrency = $product
                    ->getProductOfferCostCurrency()
                    ->getCurrencyValueUpper();

                $priceCurrency = $product
                    ->getProductOfferPriceCurrency()
                    ->getCurrencyValueUpper();
            }


            /** Если нет ТП для категории */
            if(false === $product->getOffer())
            {
                $cost = $product->getProductCost();
                $costCurrency = $product
                    ->getProductCostCurrency()
                    ->getCurrencyValueUpper();

                $priceCurrency = $product
                    ->getProductPriceCurrency()
                    ->getCurrencyValueUpper();
            }


            /** Если не назначена себестоимость или не указаны валюты - пропускаем товар */
            if(empty($cost) || empty($costCurrency) || empty($priceCurrency))
            {
                continue;
            }


            /** Находим коэффициенты для рассчета цен */
            $costCurrencyPercent = RUR::equals($costCurrency) ? 1 : $this->CbrCurrencyRequest->getCurrency($costCurrency);
            $priceCurrencyPercent = RUR::equals($priceCurrency) ? 1 : $this->CbrCurrencyRequest->getCurrency($priceCurrency);


            /** Если по курсам валют из настроек не удалось получить данные - пропускаем товар */
            if(empty($costCurrencyPercent) || empty($priceCurrencyPercent))
            {
                continue;
            }


            /** Если есть настройка для оптовой цены */
            if(null !== $product->getOpt())
            {
                /**
                 * Цену рассчитываем по формуле:
                 * Опт = Себестоимость * единица валюты себестоимости в рублях / единица валюты цены в рублях * (1 + процент увеличения оптовой цены / 100)
                 */
                $opt = $cost->getValue() * $costCurrencyPercent / $priceCurrencyPercent * (1 + $product->getOpt() / 100);

                $updateProductOptMessage = new UpdateProductOptMessage()
                    ->setOpt(new Money($opt))
                    ->setEvent($product->getEvent())
                    ->setOffer($product->getOffer())
                    ->setVariation($product->getVariation())
                    ->setModification($product->getModification());

                $this->messageDispatch->dispatch($updateProductOptMessage, [], 'products-product');
            }


            /** Если есть настройка для розничной цены */
            if(null !== $product->getPrice())
            {
                /**
                 * Цену рассчитываем по формуле:
                 * Опт = Себестоимость * единица валюты себестоимости в рублях / единица валюты цены в рублях * (1 + процент увеличения розничной цены / 100)
                 */
                $price = $cost->getValue() * $costCurrencyPercent / $priceCurrencyPercent * (1 + $product->getPrice() / 100);

                $updateProductPriceMessage = new UpdateProductPriceMessage()
                    ->setPrice(new Money($price))
                    ->setEvent($product->getEvent())
                    ->setOffer($product->getOffer())
                    ->setVariation($product->getVariation())
                    ->setModification($product->getModification());

                $this->messageDispatch->dispatch($updateProductPriceMessage, [], 'products-product');
            }
        }
    }
}