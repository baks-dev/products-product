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

namespace BaksDev\Products\Product\Type\Certificate\Match\Tests;

use BaksDev\Products\Product\Type\Certificate\Match\CertificateMatch;
use BaksDev\Products\Product\Type\Certificate\Match\CertificateMatchType;
use BaksDev\Products\Product\Type\Certificate\Match\Types\CertificateMatchCollection;
use BaksDev\Products\Product\Type\Certificate\Match\Types\CertificateMatchInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Group('products-product')]
final class CertificateMatchTypeTest extends KernelTestCase
{
    public function testUseCase(): void
    {

        self::bootKernel();

        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');


        /** @var CertificateMatchCollection $CertificateMatchTypesCollection */
        $CertificateMatchTypesCollection = self::getContainer()->get(CertificateMatchCollection::class);

        /** @var CertificateMatchInterface $case */
        foreach($CertificateMatchTypesCollection->cases() as $case)
        {
            $certificateMatch = new CertificateMatch($case->getValue());

            self::assertEquals($case->getValue(), $certificateMatch->getMatchTypeValue());

            $certificateMatchType = new CertificateMatchType();

            $platform = $this
                ->getMockBuilder(AbstractPlatform::class)
                ->getMock();

            $convertToDatabaseValue = $certificateMatchType->convertToDatabaseValue($certificateMatch, $platform);
            self::assertEquals($certificateMatch->getMatchTypeValue(), $convertToDatabaseValue);

            $convertToPHPValue = $certificateMatchType->convertToPHPValue($convertToDatabaseValue, $platform);
            self::assertInstanceOf(CertificateMatch::class, $convertToPHPValue);
            self::assertEquals($case, $convertToPHPValue->getMatchType());
        }

        self::assertTrue(true);
    }
}
