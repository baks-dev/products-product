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

namespace BaksDev\Products\Product\Type\Certificate\Document\Tests;

use BaksDev\Orders\Order\Type\Status\OrderStatus;
use BaksDev\Orders\Order\Type\Status\OrderStatus\OrderStatusCollection;
use BaksDev\Orders\Order\Type\Status\OrderStatusType;
use BaksDev\Products\Product\Type\Certificate\Document\CertificateDocument;
use BaksDev\Products\Product\Type\Certificate\Document\CertificateDocumentType;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentCollection;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Group('products-product')]
#[When(env: 'test')]
final class CertificateDocumentTypeTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var CertificateDocumentCollection $CertificateTypesCollection */
        $CertificateTypesCollection = self::getContainer()->get(CertificateDocumentCollection::class);

        /** @var CertificateDocumentInterface $case */
        foreach($CertificateTypesCollection->cases() as $case)
        {
            $CertificateDocument = new CertificateDocument($case->getValue());

            self::assertTrue($CertificateDocument->equals($case::class)); // немспейс интерфейса
            self::assertTrue($CertificateDocument->equals($case)); // объект интерфейса
            self::assertTrue($CertificateDocument->equals($case->getValue())); // срока
            self::assertTrue($CertificateDocument->equals($CertificateDocument)); // объект класса

            $CertificateDocumentType = new CertificateDocumentType();
            $platform = $this
                ->getMockBuilder(AbstractPlatform::class)
                ->getMock();

            $convertToDatabase = $CertificateDocumentType->convertToDatabaseValue($CertificateDocument, $platform);
            self::assertEquals($CertificateDocument->getDocumentTypeValue(), $convertToDatabase);

            $convertToPHP = $CertificateDocumentType->convertToPHPValue($convertToDatabase, $platform);
            self::assertInstanceOf(CertificateDocument::class, $convertToPHP);
            self::assertEquals($case, $convertToPHP->getDocumentType());
        }

        self::assertTrue(true);
    }
}
