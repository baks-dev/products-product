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

namespace BaksDev\Products\Product\UseCase\Admin\Price\Offer\Tests;

use BaksDev\Products\Product\Entity\Offers\Opt\ProductOfferOpt;
use BaksDev\Products\Product\Repository\CurrentProductEvent\CurrentProductEventInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\UseCase\Admin\Invariable\Tests\ProductInvariableAdminUseCaseTest;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewAdminUseCaseTest;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\UpdateProductOfferOptDTO;
use BaksDev\Products\Product\UseCase\Admin\Price\Offer\UpdateProductOfferOptHandler;
use BaksDev\Reference\Money\Type\Money;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[Group('products-product-usecase')]
#[When(env: 'test')]
final class UpdateProductOfferOptTest extends KernelTestCase
{
    #[DependsOnClass(ProductsProductNewAdminUseCaseTest::class)]
    #[DependsOnClass(ProductInvariableAdminUseCaseTest::class)]
    public function testUseCase(): void
    {
        /** @var CurrentProductEventInterface $currentProductEvent */
        $currentProductEvent = self::getContainer()->get(CurrentProductEventInterface::class);
        $productEvent = $currentProductEvent->findByProduct(ProductUid::TEST);
        self::assertNotNull($productEvent);

        $offerId = $productEvent
            ->getOffer()
            ->current()
            ->getId();

        $updateProductOfferOptDTO = new UpdateProductOfferOptDTO()
            ->setOffer(new ProductOfferUid($offerId))
            ->setPrice(new Money(10000));

        /** @var UpdateProductOfferOptHandler $UpdateProductOfferOptHandler */
        $UpdateProductOfferOptHandler = self::getContainer()->get(UpdateProductOfferOptHandler::class);
        $handle = $UpdateProductOfferOptHandler->handle($updateProductOfferOptDTO);

        self::assertInstanceOf(ProductOfferOpt::class, $handle);
    }
}