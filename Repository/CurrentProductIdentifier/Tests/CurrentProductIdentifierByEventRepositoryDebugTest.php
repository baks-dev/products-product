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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\CurrentProductIdentifier\Tests;

use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByEventInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class CurrentProductIdentifierByEventRepositoryDebugTest extends KernelTestCase
{
    public function testRepository(): void
    {
        self::assertTrue(true);
        return;

        /** @var CurrentProductIdentifierByEventInterface $CurrentProductIdentifierByEventInterface */
        $CurrentProductIdentifierByEventInterface = self::getContainer()->get(CurrentProductIdentifierByEventInterface::class);

        /** product, offer, variation, modification */
        $CurrentProductIdentifierResult = $CurrentProductIdentifierByEventInterface
            ->forEvent(new ProductEventUid('019c9514-e070-7656-a51c-4335edc395d4'))
            ->forOffer(new ProductOfferUid('019c9514-e072-7f5c-8515-e6027b3c46ea'))
            ->forVariation(new ProductVariationUid('019c9514-e073-720a-9647-9e43e2f9644c'))
            ->forModification(new ProductModificationUid('019c9514-e073-746e-9647-9e43e38694d9'))
            ->find();

        /** product, offer, variation */
        //        $CurrentProductIdentifierResult = $CurrentProductIdentifierByEventInterface
        //            ->forEvent(new ProductEventUid('018dbb8a-1279-7ef1-950b-ed7f89c8a685'))
        //            ->forOffer(new ProductOfferUid('018dbb8a-127a-7f30-a39d-d7adc1244447'))
        //            ->forVariation(new ProductVariationUid('018dbb8a-127a-7f30-a39d-d7adc3dde723'))
        //            ->find();

        /** product, offer */
        //        $CurrentProductIdentifierResult = $CurrentProductIdentifierByEventInterface
        //            ->forEvent(new ProductEventUid('0195d190-9d73-72a3-8516-ea7b3dcf8c20'))
        //            ->forOffer(new ProductOfferUid('0195d190-9d79-7d98-8c01-e395f73ea481'))
        //            ->find();

        /** product */
        //        $CurrentProductIdentifierResult = $CurrentProductIdentifierByEventInterface
        //            ->forEvent(new ProductEventUid('0195d179-dc12-7d4f-95cd-76bab0fb28a5'))
        //            ->find();

    }
}
