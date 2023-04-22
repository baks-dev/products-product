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

namespace BaksDev\Products\Product\Entity\Offers\Image;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Image\ProductOfferImageUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

// Файлы изображений торгового предложения

#[ORM\Entity]
#[ORM\Table(name: 'product_offer_images')]
#[ORM\Index(columns: ['dir', 'cdn'])]
#[ORM\Index(columns: ['root'])]
class ProductOfferImage extends EntityEvent implements UploadEntityInterface
{
    public const TABLE = 'product_offer_images';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProductOfferImageUid::TYPE)]
    private ProductOfferImageUid $id;

    /** ID торгового предложения */
    #[Assert\NotBlank]
    #[Assert\Type(ProductOffer::class)]
    #[ORM\ManyToOne(targetEntity: ProductOffer::class, inversedBy: 'image')]
    #[ORM\JoinColumn(name: 'offer', referencedColumnName: 'id')]
    private ProductOffer $offer;

    /** Название директории */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProductOfferUid::TYPE, nullable: false)]
    private ProductOfferUid $dir;

    /** Название файла */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, nullable: false)]
    private string $name = 'img';

    /** Расширение файла */
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'])]
    #[Assert\Length(max: 5)]
    #[ORM\Column(type: Types::STRING, length: 64, nullable: false)]
    private string $ext = 'svg';

    /** Размер файла */
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $size = 0;

    /** Файл загружен на CDN */
    #[Assert\Type('bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $cdn = false;

    /** Заглавное фото */
    #[Assert\Type('bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $root = false;

    public function __construct(ProductOffer $offer)
    {
        $this->id = new ProductOfferImageUid();
        $this->offer = $offer;
    }

    public function __clone()
    {
        $this->id = new ProductOfferImageUid();
    }

    public function getId(): ProductOfferImageUid
    {
        return $this->id;
    }

    public function getDto($dto): mixed
    {
        if ($dto instanceof ProductOfferImageInterface) {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        // Если размер файла нулевой - не заполняем сущность
        if (empty($dto->file) && empty($dto->getName())) {
            return false;
        }

        if (!empty($dto->file)) {
            $dto->setEntityUpload($this);
        }

        if ($dto instanceof ProductOfferImageInterface) {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function updFile(string $name, string $ext, int $size): void
    {
        $this->name = $name;
        $this->ext = $ext;
        $this->size = $size;
        $this->dir = $this->offer->getId();
        $this->cdn = false;
    }

    public function updCdn(string $ext): void
    {
        $this->ext = $ext;
        $this->cdn = true;
    }

    public function getUploadDir(): object
    {
        return $this->offer->getId();
    }

    public function getFileName(): string
    {
        return $this->name.'.'.$this->ext;
    }

    public function getDirName(): ProductOfferUid
    {
        return $this->dir;
    }

    public function root(): void
    {
        $this->root = true;
    }
}
