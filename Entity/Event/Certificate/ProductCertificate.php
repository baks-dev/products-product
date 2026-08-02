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

namespace BaksDev\Products\Product\Entity\Event\Certificate;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Event\Certificate\Date\ProductCertificateDate;
use BaksDev\Products\Product\Entity\Event\Certificate\Match\ProductCertificateMatch;
use BaksDev\Products\Product\Entity\Event\Certificate\Number\ProductCertificateNumber;
use BaksDev\Products\Product\Entity\Event\Certificate\Title\ProductCertificateTitle;
use BaksDev\Products\Product\Entity\Event\Certificate\Type\ProductCertificateType;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Type\Certificate\Document\CertificateDocument;
use BaksDev\Products\Product\Type\Certificate\Document\Types\Collection\CertDocCertificateConformity;
use BaksDev\Products\Product\Type\Certificate\Match\CertificateMatch;
use BaksDev\Products\Product\Type\Certificate\ProductCertificateUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'product_certificate')]
#[ORM\Index(columns: ['name'])]
class ProductCertificate extends EntityEvent implements UploadEntityInterface
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductCertificateUid::TYPE)]
    private ProductCertificateUid $id;

    /** ID события */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'file')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private ProductEvent $event;

    /** Название файла (md5) */
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING)]
    private string $name;

    /** Расширение файла */
    #[Assert\NotBlank]
    #[Assert\Choice(['pdf', 'png', 'jpg', 'jpeg'])]
    #[ORM\Column(type: Types::STRING)]
    private string $ext;

    /** Размер файла */
    #[Assert\NotBlank]
    #[Assert\Range(max: 10485760)] // 1024 * 1024 * 10
    #[ORM\Column(type: Types::INTEGER)]
    private int $size = 0;

    /** Файл загружен на CDN */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $cdn = false;


    /** Регистрационный номер */
    #[ORM\OneToOne(targetEntity: ProductCertificateNumber::class, mappedBy: 'certificate', cascade: ['all'])]
    private ?ProductCertificateNumber $number = null;

    /** Название сертификата */
    #[ORM\OneToOne(targetEntity: ProductCertificateTitle::class, mappedBy: 'certificate', cascade: ['all'])]
    private ?ProductCertificateTitle $title = null;

    /** Тип сертификата */
    #[ORM\OneToOne(targetEntity: ProductCertificateType::class, mappedBy: 'certificate', cascade: ['all'])]
    private ?ProductCertificateType $type = null;

    /** Тип соответствия требованиям */
    #[ORM\OneToOne(targetEntity: ProductCertificateMatch::class, mappedBy: 'certificate', cascade: ['all'])]
    private ?ProductCertificateMatch $match = null;

    /** Дата начала действия сертификата. */
    #[ORM\OneToOne(targetEntity: ProductCertificateDate::class, mappedBy: 'certificate', cascade: ['all'])]
    private ?ProductCertificateDate $date = null;


    public function __construct(ProductEvent $event)
    {
        $this->id = new ProductCertificateUid();
        $this->event = $event;
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }


    public function getId(): ProductCertificateUid
    {
        return $this->id;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProductCertificateInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {

        /* Если размер файла нулевой - не заполняем сущность */
        if(empty($dto->file) && empty($dto->getName()))
        {
            return false;
        }

        //        if(!empty($dto->file))
        //        {
        //            $dto->setEntityUpload($this);
        //        }

        if($dto instanceof ProductCertificateInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function updFile(string $name, string $ext, int $size): void
    {

        $this->name = $name;
        $this->ext = $ext;
        $this->size = $size;
        $this->cdn = false;
    }

    public function updCdn(?string $ext = null): void
    {
        if($ext)
        {
            $this->ext = $ext;
        }

        $this->cdn = true;
    }

    /**
     * Ext
     */
    public function getExt(): string
    {
        return $this->ext;
    }

    public function isCdn(): bool
    {
        return $this->cdn === true;
    }

    public function getNumber(): ?string
    {
        return $this->number?->getValue();
    }

    public function getTitle(): ?string
    {
        return $this->title?->getValue();
    }

    public function getType(): ?CertificateDocument
    {
        return $this->type?->getValue();
    }

    public function getMatch(): ?CertificateMatch
    {
        return $this->match?->getValue();
    }

    public function getDateIssue(): ?DateTimeImmutable
    {
        return $this->date?->getIssue();
    }

    public function getDateExpire(): ?DateTimeImmutable
    {
        return $this->date?->getExpire();
    }
}
