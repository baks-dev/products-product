<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace App\Module\Products\Product\Entity\Offers\Offer\Image;


use App\Module\Files\Res\Upload\UploadEntityInterface;
use App\Module\Products\Category\Type\Offers\Id\OffersUid;
use App\Module\Products\Product\Entity\Offers\Offer\Offer;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\Module\Products\Product\Type\Offers\Image\ImageUid;
use App\System\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'product_offer_images')]
#[ORM\Index(columns: ['dir', 'cdn'])]
#[ORM\Index(columns: ['root'])]
class Image extends EntityEvent implements UploadEntityInterface
{
    public const TABLE = 'product_offer_images';
    
    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: ImageUid::TYPE)]
    protected ImageUid $id;

    /** ID торгового предложения */
    #[ORM\ManyToOne(targetEntity: Offer::class, cascade: ['persist'], inversedBy: 'images')]
    protected Offer $offer;
    
    /** Название директории */
    #[ORM\Column(type: ProductOfferUid::TYPE, nullable: false)]
    protected ProductOfferUid $dir;

    /** Название файла */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: false)]
    protected string $name = 'img';
    
    /** Расширение файла */
    #[ORM\Column( type: Types::STRING, length: 64, nullable: false)]
    protected string $ext = 'svg';
    
    /** Размер файла */
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    protected int $size = 0;
    
    /** Файл загружен на CDN */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    protected bool $cdn = false;
    
    /** Заглавное фото */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $root = false;
    
    public function __construct(Offer $offer) {
        $this->id = new ImageUid();
        $this->offer = $offer;
        //$this->dir = $offer->getId();
    }
    public function __clone()
    {
        $this->id = new ImageUid();
    }
    
    /**
     * @return ImageUid
     */
    public function getId() : ImageUid
    {
        return $this->id;
    }
    
//    /**
//     * @return ProductOfferUid
//     */
//    public function getId() : ProductOfferUid
//    {
//        return $this->offer->getId();
//    }
    
    public function getDto($dto) : mixed
    {
        if($dto instanceof ImageInterface)
        {
            return parent::getDto($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    

    public function setEntity($dto) : mixed
    {

        /* Если размер файла нулевой - не заполняем сущность */
        if(
          (empty($dto->file) && empty($dto->getName())) ||
          (!empty($dto->file) && empty($dto->getName()))
        )
        {
            if(!empty($dto->file) && empty($dto->getName()))
            {
                /* Присваиваем торговое предложение для последующей загрузки */
                $dto->setImgOffer($this->offer);
            }
           
            
            return false;
        }
        
        if($dto instanceof ImageInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    
    public function updFile(string $name, string $ext, int $size) : void
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
    
    public function getUploadDir() : object
    {
        return $this->offer->getId();
    }
    
    /**
     * @return string
     */
    public function getFileName() : string
    {
        return $this->name.'.'.$this->ext;
    }
    
    /**
     * @return ProductOfferUid
     */
    public function getDirName() : ProductOfferUid
    {
        return $this->dir;
    }
    
    

    public function root() : void
    {
        $this->root = true;
    }
    
    
    
    //    /**
//     * @param ProductOfferImageUid $id
//     */
//   // public function __construct() { $this->id = new ProductOfferImageUid(); }
//
//    /**
//     * @return ProductOfferImageUid
//     */
//    public function getId() : ProductOfferImageUid
//    {
//        return $this->id;
//    }
    
//    /**
//     * @return Offer|null
//     */
//    public function getOffer() : ?Offer
//    {
//        return $this->offer;
//    }
//
//    /**
//     * @param Offer|null $offer
//     */
//    public function setOffer(?Offer $offer) : void
//    {
//        $this->offer = $offer;
//    }
//
//    /**
//     * @return string
//     */
//    public function getName() : string
//    {
//        return $this->name;
//    }
//
//    /**
//     * @param string $name
//     */
//    public function setName(string $name) : void
//    {
//        $this->name = $name;
//    }
//
//    /**
//     * @return string
//     */
//    public function getExt() : string
//    {
//        return $this->ext;
//    }
//
//    /**
//     * @param string $ext
//     */
//    public function setExt(string $ext) : void
//    {
//        $this->ext = $ext;
//    }
//
//
//    /**
//     * @return int
//     */
//    public function getSize() : int
//    {
//        return $this->size;
//    }
//
//    /**
//     * @param int $size
//     */
//    public function setSize(int $size) : void
//    {
//        $this->size = $size;
//    }
//
//    /**
//     * @return bool
//     */
//    public function isCdn() : bool
//    {
//        return $this->cdn;
//    }
//
//    /**
//     * @param bool $cdn
//     */
//    public function setCdn(bool $cdn) : void
//    {
//        $this->cdn = $cdn;
//    }
//

}