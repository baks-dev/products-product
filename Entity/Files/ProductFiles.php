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

namespace BaksDev\Products\Product\Entity\Files;

use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Type\File\ProductFileUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use RuntimeException;

#[ORM\Entity]
#[ORM\Table(name: 'product_files')]
class ProductFiles extends EntityEvent implements UploadEntityInterface
{
	public const TABLE = 'product_files';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ProductFileUid::TYPE)]
	private ProductFileUid $id;
	
	/** ID события */
	#[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'file')]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private ProductEvent $event;
	
	/** Название директории */
	#[ORM\Column(type: ProductEventUid::TYPE, nullable: false)]
	private ProductEventUid $dir;
	
	/** Название файла */
	#[ORM\Column(type: Types::STRING, length: 100)]
	private string $name;
	
	/** Расширение файла */
	#[ORM\Column(type: Types::STRING, length: 64)]
	private string $ext;
	
	/** Размер файла */
	#[ORM\Column(type: Types::INTEGER)]
	private int $size = 0;
	
	/** Файл загружен на CDN */
	#[ORM\Column(type: Types::BOOLEAN)]
	private bool $cdn = false;
	
	
	public function __construct(ProductEvent $event)
	{
		$this->id = new ProductFileUid();
		$this->event = $event;
	}
	
	
	public function __clone()
	{
		$this->id = new ProductFileUid();
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductFilesInterface)
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
		if($dto instanceof ProductFilesInterface)
		{
			return parent::setEntity($dto);
		}
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	//    /**
	//     * @return Event
	//     */
	//    public function getEvent() : Event
	//    {
	//        return $this->event;
	//    }
	/**
	 * @return ProductEventUid
	 */
	public function getUploadDir() : object
	{
		return $this->event->getId();
	}
	
	
	public function updFile(string $name, string $ext, int $size) : void
	{
		
		$this->name = $name;
		$this->ext = $ext;
		$this->size = $size;
		$this->dir = $this->event->getId();
		$this->cdn = false;
	}
	
	
	public function updCdn(string $ext) : void
	{
		$this->ext = $ext;
		$this->cdn = true;
	}
	
	
	
	
	
	
	//    /**
	//     * @return FileUid
	//     */
	//    public function getId() : FileUid
	//    {
	//        return $this->id;
	//    }
	//
	//    /**
	//     * @param FileUid $id
	//     */
	//    public function setId(FileUid $id) : void
	//    {
	//        $this->id = $id;
	//    }
	//
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
	//
	//
	//    /**
	//     * @return mixed
	//     */
	//    public function getName()
	//    {
	//        return $this->name;
	//    }
	//
	//    /**
	//     * @param mixed $name
	//     */
	//    public function setName($name) : void
	//    {
	//        $this->name = $name;
	//    }
	//
	//    /**
	//     * @return mixed
	//     */
	//    public function getMime()
	//    {
	//        return $this->mime;
	//    }
	//
	//    /**
	//     * @param mixed $mime
	//     */
	//    public function setMime($mime) : void
	//    {
	//        $this->mime = $mime;
	//    }
	//
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
}