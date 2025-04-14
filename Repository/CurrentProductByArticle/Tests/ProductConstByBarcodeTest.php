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

namespace BaksDev\Products\Product\Repository\CurrentProductByArticle\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\CurrentProductDTO;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\ProductConstByBarcodeInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @group products-product
 */
#[When(env: 'test')]
class ProductConstByBarcodeTest extends KernelTestCase
{

    private static string|false|null $offer;
    private static string|false|null $variation;
    private static string|false|null $modification;

    public static function setUpBeforeClass(): void
    {

        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        $DBALQueryBuilder = self::getContainer()->get(DBALQueryBuilder::class);

        /** @var DBALQueryBuilder $dbal */
        $dbal = $DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('offer.barcode')
            ->from(ProductOffer::class, 'offer')
            ->where('offer.barcode IS NOT NULL')
            ->orderBy('offer.id', 'DESC')
            ->setMaxResults(1);

        self::$offer = $dbal->fetchOne();


        $dbal = $DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('variation.barcode')
            ->from(ProductVariation::class, 'variation')
            ->where('variation.barcode IS NOT NULL')
            ->orderBy('variation.id', 'DESC')
            ->setMaxResults(1);

        self::$variation = $dbal->fetchOne();


        $dbal = $DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('modification.barcode')
            ->from(ProductModification::class, 'modification')
            ->where('modification.barcode IS NOT NULL')
            ->orderBy('modification.id', 'DESC')
            ->setMaxResults(1);

        self::$modification = $dbal->fetchOne();


    }


    public function testUseCase(): void
    {

        /** @var ProductConstByBarcodeInterface $ProductConstByBarcode */
        $ProductConstByBarcode = self::getContainer()->get(ProductConstByBarcodeInterface::class);

        foreach([self::$offer, self::$variation, self::$modification] as $barcode)
        {
            if(false === $barcode)
            {
                continue;
            }

            $CurrentProductDTO = $ProductConstByBarcode->find($barcode);

            if(false === $CurrentProductDTO)
            {
                continue;
            }

            self::assertNotFalse($CurrentProductDTO);
            self::assertInstanceOf(CurrentProductDTO::class, $CurrentProductDTO);


            self::assertInstanceOf(ProductUid::class, $CurrentProductDTO->getProduct()); // : ProductUid
            self::assertInstanceOf(ProductEventUid::class, $CurrentProductDTO->getEvent()); // : ProductEventUid
            self::assertInstanceOf(ProductInvariableUid::class, $CurrentProductDTO->getInvariable()); // : ProductInvariableUid


            $CurrentProductDTO->getOffer() ?
                self::assertInstanceOf(ProductOfferUid::class, $CurrentProductDTO->getOffer()) :
                self::assertNull($CurrentProductDTO->getOffer()); // : ProductOfferUid|null

            $CurrentProductDTO->getOfferConst() ?
                self::assertInstanceOf(ProductOfferConst::class, $CurrentProductDTO->getOfferConst()) :
                self::assertNull($CurrentProductDTO->getOfferConst()); // : ProductOfferConst|null

            $CurrentProductDTO->getVariation() ?
                self::assertInstanceOf(ProductVariationUid::class, $CurrentProductDTO->getVariation()) :
                self::assertNull($CurrentProductDTO->getVariation()); // : ProductVariationUid|null


            $CurrentProductDTO->getVariationConst() ?
                self::assertInstanceOf(ProductVariationConst::class, $CurrentProductDTO->getVariationConst()) :
                self::assertNull($CurrentProductDTO->getVariationConst()); // : ProductVariationConst|null

            $CurrentProductDTO->getModification() ?
                self::assertInstanceOf(ProductModificationUid::class, $CurrentProductDTO->getModification()) :
                self::assertNull($CurrentProductDTO->getModification()); // : ProductModificationUid|null


            $CurrentProductDTO->getModificationConst() ?
                self::assertInstanceOf(ProductModificationConst::class, $CurrentProductDTO->getModificationConst()) :
                self::assertNull($CurrentProductDTO->getModificationConst()); // : ProductModificationConst|null

        }

        self::assertFalse(false);
    }
}