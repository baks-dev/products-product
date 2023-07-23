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

namespace BaksDev\Products\Product\Entity\Offers\Variation\Image;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Image\ProductOfferVariationImageUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'product_variation_images')]
#[ORM\Index(columns: ['dir', 'cdn'])]
#[ORM\Index(columns: ['root'])]
class ProductVariationImage extends EntityEvent implements UploadEntityInterface
{
	public const TABLE = 'product_variation_images';
	
	/** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
	#[ORM\Id]
	#[ORM\Column(type: ProductOfferVariationImageUid::TYPE)]
	private ProductOfferVariationImageUid $id;
	
	/** ID торгового предложения */
    #[Assert\NotBlank]
	#[ORM\ManyToOne(targetEntity: ProductVariation::class, inversedBy: 'image')]
	#[ORM\JoinColumn(name: 'variation', referencedColumnName: 'id')]
	private ProductVariation $variation;
	
	/** Название директории */
    #[Assert\NotBlank]
    #[Assert\Uuid]
	#[ORM\Column(type: ProductVariationUid::TYPE, nullable: false)]
	private ProductVariationUid $dir;
	
	/** Название файла */
    #[Assert\NotBlank]
    #[Assert\Choice(['svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'])]
    #[Assert\Length(max: 5)]
	#[ORM\Column(type: Types::STRING, length: 100)]
	private string $name = 'img';
	
	/** Расширение файла */
    #[Assert\NotBlank]
	#[ORM\Column(type: Types::STRING, length: 64)]
	private string $ext = 'svg';
	
	/** Размер файла */
    #[Assert\NotBlank]
	#[ORM\Column(type: Types::INTEGER)]
	private int $size = 0;
	
	/** Файл загружен на CDN */
    #[Assert\NotBlank]
	#[ORM\Column(type: Types::BOOLEAN)]
	private bool $cdn = false;
	
	/** Заглавное фото */
    #[Assert\NotBlank]
	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
	private bool $root = false;
	
	
	public function __construct(ProductVariation $variation)
	{
		$this->id = new ProductOfferVariationImageUid();
		$this->variation = $variation;
	}
	
	
	public function __clone()
	{
		$this->id = new ProductOfferVariationImageUid();
	}
	
	
	public function getId() : ProductOfferVariationImageUid
	{
		return $this->id;
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof ProductVariationImageInterface)
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
		
		if($dto instanceof ProductVariationImageInterface)
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
		$this->dir = $this->variation->getId();
		$this->cdn = false;
	}
	
	
	public function updCdn(string $ext) : void
	{
		$this->ext = $ext;
		$this->cdn = true;
	}
	
	
	public function getUploadDir() : object
	{
		return $this->variation->getId();
	}
	
	
	public function getFileName() : string
	{
		return $this->name.'.'.$this->ext;
	}


    public static function getDirName(): string
    {
        return  ProductVariationUid::class;
    }
	
	
	public function root() : void
	{
		$this->root = true;
	}
	
}