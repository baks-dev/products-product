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

namespace BaksDev\Products\Product\UseCase\Admin\Quantity\Offer\Variation;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Messenger\Price\UpdateMarketplacePriceMessage;

final class UpdateVariationQuantityHandler extends AbstractHandler
{
    public function handle(UpdateVariationQuantityDTO $command): ProductVariationQuantity|string|false
    {
        $this->setCommand($command);

        $ProductVariationQuantity = $this
            ->getRepository(ProductVariationQuantity::class)
            ->findOneBy(['variation' => (string) $command->getVariation()]);

        if(false === ($ProductVariationQuantity instanceof ProductVariationQuantity))
        {
            return false;
        }

        $ProductVariationQuantity->setQuantity($command->getQuantity());

        if($command->getReserve() !== false)
        {
            $ProductVariationQuantity->setReserve($command->getReserve());
        }

        /** Валидация всех объектов */
        $this->validatorCollection->add($ProductVariationQuantity);

        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->flush();

        $message = new UpdateMarketplacePriceMessage($command->getProduct());

        $this->messageDispatch
            ->addClearCacheOther('products-product')
            ->addClearCacheOther('avito-board')
            ->addClearCacheOther('drom-board')
            ->addClearCacheOther('drom-products')
            ->dispatch($message, [new MessageDelay('5 minutes')], transport: 'products-product');

        return $ProductVariationQuantity;
    }
}