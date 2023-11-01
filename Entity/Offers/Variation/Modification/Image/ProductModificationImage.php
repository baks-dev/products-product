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

namespace BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Image\ProductOfferVariationModificationImageUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'product_modification_images')]
#[ORM\Index(columns: ['root'])]
class ProductModificationImage extends EntityEvent implements UploadEntityInterface
{
	public const TABLE = 'product_modification_images';
	
	/** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
	#[ORM\Id]
	#[ORM\Column(type: ProductOfferVariationModificationImageUid::TYPE)]
	private ProductOfferVariationModificationImageUid $id;
	
	/** ID торгового предложения */
    #[Assert\NotBlank]
	#[ORM\ManyToOne(targetEntity: ProductModification::class, inversedBy: 'image')]
	#[ORM\JoinColumn(name: 'modification', referencedColumnName: 'id')]
	private ProductModification $modification;
	
//	/** Название директории */
//    #[Assert\NotBlank]
//    #[Assert\Uuid]
//	#[ORM\Column(type: ProductModificationUid::TYPE)]
//	private ProductModificationUid $dir;
	
	/** Название файла */
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING)]
	private string $name;

    /** Расширение файла */
    #[Assert\NotBlank]
    #[Assert\Choice(['png', 'gif', 'jpg', 'jpeg', 'webp'])]
    #[ORM\Column(type: Types::STRING)]
	private string $ext;

    /** Размер файла */
    #[Assert\NotBlank]
    #[Assert\Range(max: 1048576)] // 1024 * 1024
    #[ORM\Column(type: Types::INTEGER)]
	private int $size = 0;
	
	/** Файл загружен на CDN */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
	private bool $cdn = false;
	
	/** Заглавное фото */
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
	private bool $root = false;
	
	
	public function __construct(ProductModification $modification)
	{
		$this->id = new ProductOfferVariationModificationImageUid();
		$this->modification = $modification;
	}
	
	
	public function __clone()
	{
        $this->id = clone $this->id;
	}
	
	
	public function getId() : ProductOfferVariationModificationImageUid
	{
		return $this->id;
	}

    public function __toString(): string
    {
        return (string) $this->id;
    }
	
	
	public function getDto($dto): mixed
	{
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

		if($dto instanceof ProductModificationImageInterface)
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
		
		if(!empty($dto->file))
		{
			$dto->setEntityUpload($this);
		}
		
		if($dto instanceof ProductModificationImageInterface || $dto instanceof self)
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
		//$this->dir = $this->modification->getId();
		$this->cdn = false;
	}
	
	
	public function updCdn(?string $ext = null) : void
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


	
	
//	public function getUploadDir() : object
//	{
//		return $this->modification->getId();
//	}
//
//
//	public function getFileName(): string
//	{
//		return $this->name.'.'.$this->ext;
//	}
//
//
//    public static function getDirName(): string
//    {
//        return  ProductModificationUid::class;
//    }
//
//    public function getDir(): ProductModificationUid
//    {
//        return $this->dir;
//    }

	public function root() : void
	{
		$this->root = true;
	}
	
}