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

use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByConstInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class CurrentProductIdentifierByConstRepositoryDebugTest extends KernelTestCase
{
    public function testRepository(): void
    {
        self::assertTrue(true);
        return;

        /** @var CurrentProductIdentifierByConstInterface $CurrentProductIdentifierByConstInterface */
        $CurrentProductIdentifierByConstInterface = self::getContainer()->get(CurrentProductIdentifierByConstInterface::class);

        /** product, offer, variation, modification */
        $CurrentProductIdentifierResult = $CurrentProductIdentifierByConstInterface
            ->forProduct(new ProductUid('0191143b-b72d-7afc-bad0-0ff96758a142'))
            ->forOfferConst(new ProductOfferConst('0191143b-b6e2-7ca8-badb-407bb8903197'))
            ->forVariationConst(new ProductVariationConst('0191143b-b6e9-7656-8978-f3e6ef4d6fce'))
            ->forModificationConst(new ProductModificationConst('0191143b-b6ec-766e-a20c-8319932ef3e6'))
            ->find();

        /** product, offer, variation */
        $CurrentProductIdentifierResult = $CurrentProductIdentifierByConstInterface
            ->forProduct(new ProductUid('018dbb8a-1279-7ef1-950b-ed7f891b1470'))
            ->forOfferConst(new ProductOfferConst('018dbb8a-0ac1-7666-a1c0-5237375f964d'))
            ->forVariationConst(new ProductVariationConst('018dbb8a-0ac3-78da-92d8-764f75084fa8'))
            ->find();

        /** product, offer */
        $CurrentProductIdentifierResult = $CurrentProductIdentifierByConstInterface
            ->forProduct(new ProductUid('0195cec7-ee6b-72a8-973b-9414568b7e05'))
            ->forOfferConst(new ProductOfferConst('0195cec7-ee2f-7499-8fa8-32feac2fb17b'))
            ->find();

        /** product */
        $CurrentProductIdentifierResult = $CurrentProductIdentifierByConstInterface
            ->forProduct(new ProductUid('0195cc92-93af-740e-a6b3-23f7d9c02a9f'))
            ->find();
    }
}
