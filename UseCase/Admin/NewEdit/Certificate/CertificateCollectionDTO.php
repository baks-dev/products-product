<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate;

use BaksDev\Products\Product\Entity\Event\Certificate\ProductCertificateInterface;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentInterface;
use BaksDev\Products\Product\Type\Certificate\Document\Types\Collection\CertDocCertificateConformity;
use BaksDev\Products\Product\Type\Certificate\Match\Types\CertificateMatchInterface;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Date\ProductCertificateDateDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Match\ProductCertificateMatchDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Number\ProductCertificateNumberDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Title\ProductCertificateTitleDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Type\ProductCertificateTypeDTO;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProductCertificate */
final class CertificateCollectionDTO implements ProductCertificateInterface
{
    public ?File $file = null;

    private ?string $name = null;

    private ?string $ext = null;

    private bool $cdn = false;

    private ?int $size = null;


    /** Регистрационный номер */
    private ProductCertificateNumberDTO $number;

    /** Название сертификата */
    private ProductCertificateTitleDTO $title;

    /** Тип сертификата */
    private ProductCertificateTypeDTO $type;

    /** Тип соответствия требованиям */
    private ProductCertificateMatchDTO $match;

    /** Дата начала действия сертификата. */
    private ProductCertificateDateDTO $date;

    public function __construct()
    {
        /** Регистрационный номер */
        $this->number = new ProductCertificateNumberDTO();

        /** Название сертификата */
        $this->title = new ProductCertificateTitleDTO();

        /** Тип сертификата */
        $this->type = new ProductCertificateTypeDTO();

        /** Тип соответствия требованиям */
        $this->match = new ProductCertificateMatchDTO();

        /** Дата начала действия сертификата. */
        $this->date = new ProductCertificateDateDTO();
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function getCdn(): bool
    {
        return $this->cdn;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getNumber(): ProductCertificateNumberDTO
    {
        return $this->number;
    }

    public function setNumber(ProductCertificateNumberDTO $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getTitle(): ProductCertificateTitleDTO
    {
        return $this->title;
    }

    public function setTitle(ProductCertificateTitleDTO $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getType(): ProductCertificateTypeDTO
    {
        return $this->type;
    }

    public function setType(ProductCertificateTypeDTO $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getMatch(): ProductCertificateMatchDTO
    {
        return $this->match;
    }

    public function setMatch(ProductCertificateMatchDTO $match): self
    {
        $this->match = $match;
        return $this;
    }

    public function getDate(): ProductCertificateDateDTO
    {
        return $this->date;
    }

    public function setDate(ProductCertificateDateDTO $date): self
    {
        $this->date = $date;
        return $this;
    }
}
