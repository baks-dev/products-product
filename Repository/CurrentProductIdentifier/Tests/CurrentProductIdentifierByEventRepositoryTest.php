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

namespace BaksDev\Products\Product\Repository\CurrentProductIdentifier\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByEventInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierResult;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductHandler;
use Doctrine\ORM\EntityManagerInterface;
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
class CurrentProductIdentifierByEventRepositoryTest extends KernelTestCase
{
    private static array|false $result;
    private static array|false $new;

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
            ->select('product.id AS id')
            ->addSelect('product.event AS event')
            ->from(Product::class, 'product');

        $dbal
            ->addSelect('offer.id AS offer')
            ->addSelect('offer.const AS offer_const')
            ->join('product', ProductOffer::class, 'offer', 'offer.event = product.event');

        $dbal
            ->addSelect('variation.id AS variation')
            ->addSelect('variation.const AS variation_const')
            ->join('offer', ProductVariation::class, 'variation', 'variation.offer = offer.id');


        $dbal
            ->addSelect('modification.id AS modification')
            ->addSelect('modification.const AS modification_const')
            ->join('variation', ProductModification::class, 'modification', 'modification.variation = variation.id');


        $dbal->setMaxResults(1);

        self::$result = $dbal->fetchAssociative();
        sleep(1);


        /**
         * Обновляем событие
         *
         * @var EntityManagerInterface $EntityManagerInterface
         */
        $EntityManagerInterface = self::getContainer()->get(EntityManagerInterface::class);
        $EntityManagerInterface->clear();
        $ProductEvent = $EntityManagerInterface->getRepository(ProductEvent::class)->find(self::$result['event']);


        $ProductDTO = new ProductDTO();
        $ProductEvent->getDto($ProductDTO);


        /** @var ProductHandler $ProductHandler */
        $ProductHandler = self::getContainer()->get(ProductHandler::class);
        $handle = $ProductHandler->handle($ProductDTO, false);


        /** Получаем новые идентификаторы */

        $dbal->where('product.id = :product')
            ->setParameter('product', self::$result['id']);

        self::$new = $dbal->fetchAssociative();

        self::assertNotNull($ProductEvent);
        self::assertTrue(($handle instanceof Product));

    }

    /*
    "event" => "0191140d-eaa3-7f16-9218-f8b05c20266f"
    "quantity" => 0
    "reserve" => 0
    "offer" => "0191140d-eaa4-77aa-97c5-873d13abeb06"
    "offer_quantity" => 0
    "offer_reserve" => 0
    "variation" => "0191140d-eaa4-77aa-97c5-873d14871960"
    "variation_quantity" => 0
    "variation_reserve" => 0
    "modification" => "0191140d-eaa4-77aa-97c5-873d148f68f8"
    "modification_quantity" => 10
    "modification_reserve" => 4*/

    public static function testEvent(): void
    {
        /** @var CurrentProductIdentifierByEventInterface $CurrentProductIdentifier */
        $CurrentProductIdentifier = self::getContainer()->get(CurrentProductIdentifierByEventInterface::class);

        $CurrentProductIdentifierResult = $CurrentProductIdentifier
            ->forEvent(self::$result['event'])
            ->find();


        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(CurrentProductIdentifierResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($CurrentProductIdentifierResult);
                //dump($data);
            }
        }


        self::assertNotFalse($CurrentProductIdentifierResult);
        self::assertTrue($CurrentProductIdentifierResult->getEvent()->equals(self::$new['event']));

    }

    /** Offer */

    public static function testOffer(): void
    {
        /** @var CurrentProductIdentifierByEventInterface $CurrentProductIdentifier */
        $CurrentProductIdentifier = self::getContainer()->get(CurrentProductIdentifierByEventInterface::class);

        $CurrentProductIdentifierResult = $CurrentProductIdentifier
            ->forEvent(self::$result['event'])
            ->forOffer(self::$result['offer'])
            ->find();

        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(CurrentProductIdentifierResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($CurrentProductIdentifierResult);
                //dump($data);
            }
        }

        self::assertNotFalse($CurrentProductIdentifierResult);

        self::assertTrue($CurrentProductIdentifierResult->getEvent()->equals(self::$new['event']));
        self::assertTrue($CurrentProductIdentifierResult->getOffer()->equals(self::$new['offer']));

    }


    /** Variation */


    public static function testVariation(): void
    {
        /** @var CurrentProductIdentifierByEventInterface $CurrentProductIdentifier */
        $CurrentProductIdentifier = self::getContainer()->get(CurrentProductIdentifierByEventInterface::class);

        $CurrentProductIdentifierResult = $CurrentProductIdentifier
            ->forEvent(self::$result['event'])
            ->forOffer(self::$result['offer'])
            ->forVariation(self::$result['variation'])
            ->find();

        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(CurrentProductIdentifierResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($CurrentProductIdentifierResult);
                //dump($data);
            }
        }

        self::assertNotFalse($CurrentProductIdentifierResult);

        self::assertTrue($CurrentProductIdentifierResult->getEvent()->equals(self::$new['event']));
        self::assertTrue($CurrentProductIdentifierResult->getOffer()->equals(self::$new['offer']));
        self::assertTrue($CurrentProductIdentifierResult->getVariation()->equals(self::$new['variation']));
    }


    /** Modification */


    public static function testModification(): void
    {
        /** @var CurrentProductIdentifierByEventInterface $CurrentProductIdentifier */
        $CurrentProductIdentifier = self::getContainer()->get(CurrentProductIdentifierByEventInterface::class);

        $CurrentProductIdentifierResult = $CurrentProductIdentifier
            ->forEvent(self::$result['event'])
            ->forOffer(self::$result['offer'])
            ->forVariation(self::$result['variation'])
            ->forModification(self::$result['modification'])
            ->find();

        // Вызываем все геттеры
        $reflectionClass = new ReflectionClass(CurrentProductIdentifierResult::class);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach($methods as $method)
        {
            // Методы без аргументов
            if($method->getNumberOfParameters() === 0)
            {
                // Вызываем метод
                $data = $method->invoke($CurrentProductIdentifierResult);
                //dump($data);
            }
        }

        self::assertNotFalse($CurrentProductIdentifierResult);

        self::assertTrue($CurrentProductIdentifierResult->getEvent()->equals(self::$new['event']));
        self::assertTrue($CurrentProductIdentifierResult->getOffer()->equals(self::$new['offer']));
        self::assertTrue($CurrentProductIdentifierResult->getVariation()->equals(self::$new['variation']));
        self::assertTrue($CurrentProductIdentifierResult->getModification()->equals(self::$new['modification']));

    }
}
