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

use BaksDev\Products\Product\Messenger\Price\UpdateProductOptDispatcher;
use BaksDev\Products\Product\Messenger\Price\UpdateProductOptMessage;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Money\Type\Money;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[Group('products-product-dispatcher')]
#[When(env: 'test')]
final class UpdateProductOptDispatcherTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        $UpdateProductOptDispatcher = self::getContainer()->get(UpdateProductOptDispatcher::class);

        /** @var UpdateProductOptDispatcher $UpdateProductOptDispatcher */
        $UpdateProductOptDispatcher(new UpdateProductOptMessage()
            ->setOpt(new Money(10000))
            ->setEvent(new ProductEventUid('019a964c-05d0-71af-a7f7-24ad75b6e7a7'))
            ->setOffer(new ProductOfferUid('019a9651-fe78-7fd4-ab5c-7795a6e654da'))
            ->setVariation(new ProductVariationUid('019a9651-fe78-7fd4-ab5c-7795a7690788'))
            ->setModification(new ProductModificationUid('019a9651-fe7a-740f-a4ae-c752008f3715'))
        );

        self::assertTrue(true);
    }
}