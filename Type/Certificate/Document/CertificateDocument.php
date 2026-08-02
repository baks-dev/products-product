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

namespace BaksDev\Products\Product\Type\Certificate\Document;

use BaksDev\Core\Type\UidType\Uid;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentInterface;
use BaksDev\Products\Product\Type\Certificate\Document\Types\Collection\CertDocCertificateConformity;
use InvalidArgumentException;
use Symfony\Component\Uid\AbstractUid;

final class CertificateDocument
{
    public const string TYPE = 'certificate_document';

    public const string TEST = CertDocCertificateConformity::class;

    private CertificateDocumentInterface $type;

    public function __construct(self|string|CertificateDocumentInterface $type)
    {
        if(is_string($type) && class_exists($type))
        {
            $instance = new $type();

            if($instance instanceof CertificateDocumentInterface)
            {
                $this->type = $instance;
                return;
            }
        }

        if($type instanceof CertificateDocumentInterface)
        {
            $this->type = $type;
            return;
        }

        if($type instanceof self)
        {
            $this->type = $type->getDocumentType();
            return;
        }

        /** @var CertificateDocumentInterface $declare */
        foreach(self::getDeclared() as $declare)
        {

            $instance = new self($declare);

            if($instance->getDocumentTypeValue() === $type)
            {
                $this->type = new $declare();
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Not found Certificate Type %s', $type));

    }

    public function getDocumentType(): CertificateDocumentInterface
    {
        return $this->type;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(CertificateDocumentInterface::class, class_implements($className), true);
            },
        );
    }

    public function getDocumentTypeValue(): string
    {
        return $this->type->getValue();
    }

    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $key => $declared)
        {
            /** @var CertificateDocumentInterface $declared */
            $class = new $declared();

            $case[$class::priority().$key] = new self($class);
        }

        ksort($case);

        return $case;
    }

    public function __toString(): string
    {
        return $this->type->getValue();
    }

    public function equals(mixed $type): bool
    {
        $type = new self($type);
        return $this->getDocumentTypeValue() === $type->getDocumentTypeValue();
    }
}