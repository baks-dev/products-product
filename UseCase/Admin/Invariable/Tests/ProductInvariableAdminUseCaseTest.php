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

namespace BaksDev\Products\Product\UseCase\Admin\Invariable\Tests;

use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\UseCase\Admin\Invariable\ProductInvariableDTO;
use BaksDev\Products\Product\UseCase\Admin\Invariable\ProductInvariableHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group products-product
 * @group products-product-repository
 */
#[When(env: 'test')]
class ProductInvariableAdminUseCaseTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $main = $em->getRepository(ProductInvariable::class)
            ->findOneBy(['id' => ProductInvariableUid::TEST]);

        if($main)
        {
            $em->remove($main);
        }


        $em->flush();
        $em->clear();
    }


    public function testUseCase(): void
    {
        /** @see ProductInvariableDTO */
        $ProductInvariableDTO = new ProductInvariableDTO();

        $ProductInvariableDTO
            ->setProduct(new ProductUid(ProductUid::TEST))
            ->setOffer(new ProductOfferConst(ProductOfferConst::TEST))
            ->setVariation(new ProductVariationConst(ProductVariationConst::TEST))
            ->setModification(new ProductModificationConst(ProductModificationConst::TEST));


        /** @var ProductInvariableHandler $ProductInvariableHandler */
        $ProductInvariableHandler = self::getContainer()->get(ProductInvariableHandler::class);
        $handle = $ProductInvariableHandler->handle($ProductInvariableDTO);

        self::assertTrue(($handle instanceof ProductInvariable), $handle.': Ошибка ProductInvariable');

    }
}