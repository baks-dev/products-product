<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

use BaksDev\Core\Messenger\MessageDispatch;
use BaksDev\Products\Product\Repository\ProductsByValues\ProductsByValuesInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class FindProductsForQuantityUpdateDispatcher
{
    public function __construct(
        private ProductsByValuesInterface $ProductsByValuesRepository,
        private MessageDispatch $messageDispatch,
    ) {}

    public function __invoke(FindProductsForQuantityUpdateMessage $message): void
    {
        $this->ProductsByValuesRepository
            ->forProfile($message->getProfile())
            ->forCategory($message->getCategory())
            ->forOfferValue($message->getOfferValue())
            ->forVariationValue($message->getVariationValue())
            ->forModificationValue($message->getModificationValue());

        $products = $this->ProductsByValuesRepository->findAll();

        if(false === $products || false === $products->valid())
        {
            return;
        }

        foreach($products as $ProductsByValuesResult)
        {
            $UpdateProductQuantityMessage = new UpdateProductQuantityMessage(
                event: $ProductsByValuesResult->getEvent(),
                offer: $ProductsByValuesResult->getProductOfferUid(),
                variation: $ProductsByValuesResult->getProductVariationUid(),
                modification: $ProductsByValuesResult->getProductModificationUid(),
                quantity: $message->getQuantity(),
                reserve: false,
            );

            /** Отправляем сообщение в шину */
            $this->messageDispatch->dispatch(
                message: $UpdateProductQuantityMessage,
                transport: 'products-product',
            );
        }
    }
}