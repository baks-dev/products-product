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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Image;


use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductOfferVariationImageInterface;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductOfferVariationUid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductOfferVariationImageCollectionDTO implements ProductOfferVariationImageInterface
{
	
	/** Обложка категории */
	#[Assert\File(
		maxSize: '2048k',
		mimeTypes: [
			'image/png',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/webp',
		],
		mimeTypesMessage: 'Please upload a valid file'
	)]
	public ?File $file = null;
	
	/** Название файла */
	private ?string $name = null;
	
	/** Расширение */
	private ?string $ext = null;
	
	/** Флаг загрузки CDN */
	private bool $cdn = false;
	
	/** Главное фото */
	private bool $root = false;
	
	/** Размер файла */
	private ?int $size = null;
	
	/** Диреткория загрузки файла */
	private ?ProductOfferVariationUid $dir = null;
	
	/** Сущность для загрузки и обновления файла  */
	private mixed $entityUpload = null;
	
	
	
	/** Название файла */
	
	public function getName() : ?string
	{
		return $this->name;
	}
	
	
	public function setName(?string $name) : void
	{
		$this->name = $name;
	}
	
	
	/** Расширение */
	
	public function getExt() : ?string
	{
		return $this->ext;
	}
	
	
	public function setExt(?string $ext) : void
	{
		$this->ext = $ext;
	}
	
	
	/** Флаг загрузки CDN */
	
	public function getCdn() : bool
	{
		return $this->cdn;
	}
	
	
	public function setCdn(bool $cdn) : void
	{
		$this->cdn = $cdn;
	}
	
	
	/** Диреткория загрузки файла */
	
	public function getDir() : ?ProductOfferVariationUid
	{
		return $this->dir;
	}
	
	
	public function setDir(ProductOfferVariationUid $dir) : void
	{
		$this->dir = $dir;
	}
	
	
//	/** Идентификатор торгового предложения */
//
//	public function getOffer() : ?ProductOffer
//	{
//		return $this->offer;
//	}
//
//
//	public function setOffer(?ProductOffer $imgOffer) : void
//	{
//		$this->offer = $imgOffer;
//	}
//
	
	/** Главное фото */
	
	public function getRoot() : bool
	{
		return $this->root;
	}
	
	
	public function setRoot(bool $root) : void
	{
		$this->root = $root;
	}
	
	
	/** Размер файла */
	
	public function getSize() : ?int
	{
		return $this->size;
	}
	
	
	public function setSize(?int $size) : void
	{
		$this->size = $size;
	}
	
	

	public function getEntityUpload() : mixed
	{
		return $this->entityUpload;
	}
	

	public function setEntityUpload(mixed $entityUpload) : void
	{
		$this->entityUpload = $entityUpload;
	}
	
	
}

