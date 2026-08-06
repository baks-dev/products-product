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

namespace BaksDev\Products\Product\Repository\CertificatesByProduct;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

/** @see CertificatesByProductResult */
final class CertificatesByProductResult
{

    public function __construct(
        private string $name, //" => "/upload/product_certificate/2573a8192582b5cc10efa0e041ba4f5c"
        private string $ext, // " => "pdf"
        private bool $cdn, //" => false
        private string $number, //" => "567890"
        private string $title, //" => "Название декларации"
        private string $issue, //" => "2026-08-03 00:00:00"
        private ?string $expire, //" => "2026-08-26 00:00:00"
        private string $type, //" => "decconf"
        private string $match, //" => "certgost"
    ) {}

    public function getFilePath(): string
    {
        return $this->name.'/file'.$this->ext;
    }

    public function isCdn(): bool
    {
        return $this->cdn;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIssue(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->issue);
    }

    public function getExpire(): ?DateTimeImmutable
    {
        return $this->expire ? new DateTimeImmutable($this->expire) : null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMatch(): string
    {
        return $this->match;
    }
}