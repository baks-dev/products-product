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

namespace BaksDev\Products\Product\Entity\Photo;

use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Product\Type\Photo\ProductPhotoUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'product_photo')]
class ProductPhoto extends EntityEvent implements UploadEntityInterface
{
	
	public const TABLE = "product_photo";
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: ProductPhotoUid::TYPE)]
	private ProductPhotoUid $id;
	
	/** ID события */
	#[ORM\ManyToOne(targetEntity: ProductEvent::class, inversedBy: 'photo')]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private ProductEvent $event;
	
	/** Название директории */
	#[ORM\Column(type: ProductEventUid::TYPE)]
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
	
	/** Заглавное фото */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
	private bool $root = false;
	
	
	/**
	 * @param ProductEvent $event
	 */
	public function __construct(ProductEvent $event)
	{
		$this->id = new ProductPhotoUid();
		$this->event = $event;
	}
	
	
	public function __clone()
	{
		$this->id = new ProductPhotoUid();
	}
	
	
	public function getId() : ProductPhotoUid
	{
		return $this->id;
	}
	
	
	public function getUploadDir() : object
	{
		return $this->event->getId();
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductPhotoInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		/* Если размер файла нулевой - не заполняем сущность */
		if(empty($dto->file) && empty($dto->getName()))
		{
			return false;
		}
		
		if(!empty($dto->file))
		{
			$dto->setEntityUpload($this);
		}
		
		if($dto instanceof ProductPhotoInterface)
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
	
	
	public function updCdn(string $ext) : void
	{
		$this->ext = $ext;
		$this->cdn = true;
	}
	
	
	public function root() : void
	{
		$this->root = true;
	}
	
}