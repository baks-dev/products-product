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

namespace BaksDev\Products\Product\Entity\Photo;

use App\Module\Files\Res\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Photo\PhotoUid;
use App\System\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'product_photo')]
//#[ORM\Index(columns: ['event_id'])]
class Photo extends EntityEvent implements UploadEntityInterface
{
    
    public const TABLE = "product_photo";
    
    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: PhotoUid::TYPE)]
    private PhotoUid $id;
    
    /** ID события */
    #[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    protected ProductEvent $event;
    
    /** Название директории */
    #[ORM\Column(type: ProductEventUid::TYPE, nullable: false)]
    protected ProductEventUid $dir;
    
    /** Название файла */
    #[ORM\Column(name: 'name', type: Types::STRING, length: 100, nullable: false)]
    protected string $name;
    
    /** Расширение файла */
    #[ORM\Column(name: 'ext', type: Types::STRING, length: 64, nullable: false)]
    protected string $ext;
    
    /** Размер файла */
    #[ORM\Column(name: 'size', type: Types::INTEGER, nullable: false)]
    private int $size = 0;
    
    /** Файл загружен на CDN */
    #[ORM\Column(name: 'cdn', type: Types::BOOLEAN, nullable: false)]
    protected bool $cdn = false;
    
    /** Заглавное фото */
    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $root = false;
    
    /**
     * @param ProductEvent $event
     */
    public function __construct(ProductEvent $event) {
        $this->id = new PhotoUid();
        $this->event = $event;
    }
    
    public function __clone()
    {
        $this->id = new PhotoUid();
    }
    
    /**
     * @return PhotoUid
     */
    public function getId() : PhotoUid
    {
        return $this->id;
    }
    
    
    
//    /**
//     * @return PhotoUid
//     */
//    public function getId() : PhotoUid
//    {
//        return $this->id;
//    }
    
    public function getUploadDir() : object
    {
        return $this->event->getId();
    }

    public function getDto($dto) : mixed
    {
        if($dto instanceof PhotoInterface)
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
            return false;
        }
        
        if($dto instanceof PhotoInterface)
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
        $this->dir = $this->event->getId();
        $this->cdn = false;
    }
    
    public function updCdn(string $ext): void
    {
        $this->ext = $ext;
        $this->cdn = true;
    }
    

    public function root() : void
    {
        $this->root = true;
    }
    

    
    
    
    
    //    /**
//     * @param PhotoUid $id
//     */
//    //public function __construct() { $this->id = new PhotoUid(); }
//
//    /**
//     * @return PhotoUid
//     */
//    public function getId() : PhotoUid
//    {
//        return $this->id;
//    }
//
//    /**
//     * @param PhotoUid $id
//     */
//    public function setId(PhotoUid|string|null $id) : void
//    {
//        if(is_string($id) || null) { $id = new PhotoUid($id); }
//
//        $this->id = $id;
//    }
    
//    /**
//     * @return Event
//     */
//    public function getEvent() : Event
//    {
//        return $this->event;
//    }
//
//    /**
//     * @param Event $event
//     */
//    public function setEvent(Event $event) : void
//    {
//        $this->event = $event;
//    }
//
//    /**
//     * @return string|null
//     */
//    public function getName() : ?string
//    {
//        return $this->name;
//    }
//
//    /**
//     * @param string|null $name
//     */
//    public function setName(?string $name) : void
//    {
//        $this->name = $name;
//    }
//
//
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
    
    
}