<?php
/*
<<<<<<< HEAD
 * Copyright 2025.  Baks.dev <admin@baks.dev>
 *
=======
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
 *  
>>>>>>> refs/remotes/origin/master
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
<<<<<<< HEAD
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
=======
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
>>>>>>> refs/remotes/origin/master
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\ExistProductBarcode;

<<<<<<< HEAD
=======
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
>>>>>>> refs/remotes/origin/master
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[Group('products-product-repository')]
#[When(env: 'test')]
final class ExistProductBarcodeRepositoryTest extends KernelTestCase
{
    public function testExists(): void
    {
        $ExistProductBarcodeRepository = self::getContainer()->get(ExistProductBarcodeInterface::class);

        /** @var ExistProductBarcodeRepository $ExistProductBarcodeRepository */
        $result = $ExistProductBarcodeRepository
<<<<<<< HEAD
            ->forBarcode('2743144747169')
            ->forProduct(new ProductUid('0193d94c-30a5-7be3-82af-12255bb8d5a3'))
            ->forOffer(new ProductOfferConst('0193d94c-2f8f-782c-b2d0-227a451cee01'))
            ->forVariation(new ProductVariationConst('0193d94c-3006-76b2-a4da-5bc7595519e8'))
            ->forModification(new ProductModificationConst('0193d94c-2ff9-7296-8348-fce652bc2bda'))
            ->exist();

//        dd($result);

=======
            ->forBarcode(new ProductBarcode())
            ->forProduct(new ProductUid())
            ->forOffer(new ProductOfferConst())
            ->forVariation(new ProductVariationConst())
            ->forModification(new ProductModificationConst())
            ->exist();

>>>>>>> refs/remotes/origin/master
        self::assertTrue(true);
    }
}