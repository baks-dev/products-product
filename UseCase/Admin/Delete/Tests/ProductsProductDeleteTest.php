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

namespace BaksDev\Products\Product\UseCase\Admin\Delete\Tests;

use BaksDev\Products\Category\UseCase\Admin\NewEdit\Tests\CategoryProductNewTest;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Repository\CurrentProductEvent\CurrentProductEventInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteDTO;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group products-product
 * @group products-product-usecase
 *
 * @depends BaksDev\Products\Product\Controller\Admin\Tests\DeleteControllerTest::class
 * @depends BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductEditTest::class
 *
 */
#[When(env: 'test')]
class ProductsProductDeleteTest extends KernelTestCase
{
    public static function tearDownAfterClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $main = $em->getRepository(Product::class)
            ->findOneBy(['id' => ProductUid::TEST]);

        if($main)
        {
            $em->remove($main);
        }


        $event = $em->getRepository(ProductEvent::class)
            ->findBy(['main' => ProductUid::TEST]);

        foreach($event as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
        $em->clear();

        /** Удаляем тестовую категорию */
        CategoryProductNewTest::setUpBeforeClass();
    }

    public function testUseCase(): void
    {
        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var CurrentProductEventInterface $ProductCurrentEvent */
        $ProductCurrentEvent = self::getContainer()->get(CurrentProductEventInterface::class);
        $ProductEvent = $ProductCurrentEvent->findByProduct(ProductUid::TEST);

        self::assertNotNull($ProductEvent);

        /** @see ProductDeleteDTO */
        $ProductDeleteDTO = new ProductDeleteDTO();
        $ProductEvent->getDto($ProductDeleteDTO);


        /** @var ProductDeleteHandler $ProductDeleteHandler */
        $ProductDeleteHandler = self::getContainer()->get(ProductDeleteHandler::class);
        $handle = $ProductDeleteHandler->handle($ProductDeleteDTO);

        self::assertTrue($handle instanceof Product);

    }
}
