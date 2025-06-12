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

namespace BaksDev\Products\Product\Messenger\Invariable;


use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Messenger\ProductMessage;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Repository\ProductInvariable\ProductInvariableInterface;
use BaksDev\Products\Product\UseCase\Admin\Invariable\ProductInvariableDTO;
use BaksDev\Products\Product\UseCase\Admin\Invariable\ProductInvariableHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class UpdateProductInvariableHandler
{
    public function __construct(
        #[Target('productsProductLogger')] private LoggerInterface $logger,
        private AllProductsIdentifierInterface $allProductsIdentifier,
        private ProductInvariableInterface $productInvariable,
        private ProductInvariableHandler $productInvariableHandler,
    ) {}

    /**
     * Метод обновляет сущность Invariable при изменении продукта
     */
    public function __invoke(ProductMessage $message): void
    {
        $products = $this->allProductsIdentifier
            ->forProduct($message->getId())
            ->findAll();

        if(false === $products || false === $products->valid())
        {
            return;
        }

        foreach($products as $ProductsIdentifierResult)
        {
            $ProductInvariableUid = $this->productInvariable
                ->product($ProductsIdentifierResult->getProductId())
                ->offer($ProductsIdentifierResult->getProductOfferConst())
                ->variation($ProductsIdentifierResult->getProductVariationConst())
                ->modification($ProductsIdentifierResult->getProductModificationConst())
                ->find();

            if(false === $ProductInvariableUid)
            {
                $ProductInvariableDTO = new ProductInvariableDTO();

                $ProductInvariableDTO
                    ->setProduct($ProductsIdentifierResult->getProductId())
                    ->setOffer($ProductsIdentifierResult->getProductOfferConst())
                    ->setVariation($ProductsIdentifierResult->getProductVariationConst())
                    ->setModification($ProductsIdentifierResult->getProductModificationConst());

                $handle = $this->productInvariableHandler->handle($ProductInvariableDTO);

                if(false === ($handle instanceof ProductInvariable))
                {
                    $this->logger->critical(sprintf('%s: Ошибка при обновлении ProductInvariable', $handle));
                }
            }
        }
    }
}
