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

namespace BaksDev\Products\Product\Repository\CurrentProductByArticle\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Offers\Barcode\ProductOfferBarcode;
use BaksDev\Products\Product\Entity\Offers\Variation\Barcode\ProductVariationBarcode;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Barcode\ProductModificationBarcode;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\CurrentProductByBarcodeResult;
use BaksDev\Products\Product\Repository\CurrentProductByArticle\ProductConstByBarcodeInterface;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Group('products-product')]
#[When(env: 'test')]
class ProductConstByBarcodeTest extends KernelTestCase
{

    private static string|false|null $offer;
    private static string|false|null $variation;
    private static string|false|null $modification;

    public static function setUpBeforeClass(): void
    {

        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        $DBALQueryBuilder = self::getContainer()->get(DBALQueryBuilder::class);

        /** @var DBALQueryBuilder $dbal */
        $dbal = $DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('offer.value')
            ->from(ProductOfferBarcode::class, 'offer')
            ->where('offer.value IS NOT NULL')
            ->orderBy('offer.offer', 'DESC')
            ->setMaxResults(1);

        self::$offer = $dbal->fetchOne();


        $dbal = $DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('variation.value')
            ->from(ProductVariationBarcode::class, 'variation')
            ->where('variation.value IS NOT NULL')
            ->orderBy('variation.variation', 'DESC')
            ->setMaxResults(1);

        self::$variation = $dbal->fetchOne();


        $dbal = $DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('modification.value')
            ->from(ProductModificationBarcode::class, 'modification')
            ->where('modification.value IS NOT NULL')
            ->orderBy('modification.modification', 'DESC')
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

            $CurrentProductByBarcodeResult = $ProductConstByBarcode->find($barcode);

            if(false === $CurrentProductByBarcodeResult)
            {
                continue;
            }

            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(CurrentProductByBarcodeResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($CurrentProductByBarcodeResult);
                    // dump($data);
                }
            }
        }

        self::assertFalse(false);
    }
}