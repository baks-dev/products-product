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

namespace BaksDev\Products\Product\Type\Certificate\Match;

use BaksDev\Products\Product\Type\Certificate\Match\Types\CertificateMatchInterface;
use BaksDev\Products\Product\Type\Certificate\Match\Types\Collection\CertMatchGOST;
use InvalidArgumentException;

/**
 * Класс для работы с типами сертификатов соответствия (ГОСТ, Техрегламент РФ, Техрегламент ТС)
 */
final class CertificateMatch
{
    public const string TYPE = 'certificate_match';

    public const string TEST = CertMatchGOST::class;

    private CertificateMatchInterface $type;

    public function __construct(self|string|CertificateMatchInterface $type)
    {

        if(is_string($type) && class_exists($type))
        {
            $instance = new $type();

            if($instance instanceof CertificateMatchInterface)
            {
                $this->type = $instance;
                return;
            }
        }

        if($type instanceof CertificateMatchInterface)
        {
            $this->type = $type;
            return;
        }

        if($type instanceof self)
        {
            $this->type = $type->getMatchType();
            return;
        }

        /** @var CertificateMatchInterface $declare */
        foreach(self::getDeclared() as $declare)
        {
            $instance = new self($declare);

            if($instance->getMatchTypeValue() === $type)
            {
                $this->type = new $declare();
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Not found Certificate Type %s', $type));

    }

    public function getMatchType(): CertificateMatchInterface
    {
        return $this->type;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(CertificateMatchInterface::class, class_implements($className), true);
            },
        );
    }

    public function getMatchTypeValue(): string
    {
        return $this->type->getValue();
    }

    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $key => $declared)
        {
            /** @var CertificateMatchInterface $declared */
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
        return $this->getMatchTypeValue() === $type->getMatchTypeValue();
    }
}