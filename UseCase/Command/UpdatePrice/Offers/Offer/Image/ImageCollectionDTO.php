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

namespace BaksDev\Products\Product\UseCase\Command\UpdatePrice\Offers\Offer\Image;

use BaksDev\Products\Product\Entity\Offers\Offer\Image\ImageInterface;
use BaksDev\Products\Product\Entity\Offers\Offer\Offer;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageCollectionDTO implements ImageInterface
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
	
	private ?Offer $imgOffer = null;
	
	private readonly string $name;
	
	private readonly string $ext;
	
	private readonly bool $cdn;
	
	private readonly bool $root;
	
	private readonly int $size;
	
	private readonly ProductOfferUid $dir;
	
	
	/**
	 * @return string|null
	 */
	public function getName() : ?string
	{
		return $this->name;
	}
	
	
	/**
	 * @param string|null $name
	 */
	public function setName(?string $name) : void
	{
		$this->name = $name;
	}
	
	
	/**
	 * @return string|null
	 */
	public function getExt() : ?string
	{
		return $this->ext;
	}
	
	
	/**
	 * @param string|null $ext
	 */
	public function setExt(?string $ext) : void
	{
		$this->ext = $ext;
	}
	
	
	/**
	 * @return bool
	 */
	public function getCdn() : bool
	{
		return $this->cdn;
	}
	
	
	/**
	 * @param bool $cdn
	 */
	public function setCdn(bool $cdn) : void
	{
		$this->cdn = $cdn;
	}
	
	
	/**
	 * @return ProductOfferUid|null
	 */
	public function getDir() : ?ProductOfferUid
	{
		return $this->dir;
	}
	
	
	public function setDir(ProductOfferUid $dir) : void
	{
		$this->dir = $dir;
	}
	
	
	/**
	 * @return Offer|null
	 */
	public function getImgOffer() : ?Offer
	{
		return $this->imgOffer;
	}
	
	
	/**
	 * @param Offer|null $imgOffer
	 */
	public function setImgOffer(?Offer $imgOffer) : void
	{
		$this->imgOffer = $imgOffer;
	}
	
	
	/**
	 * @return bool
	 */
	public function getRoot() : bool
	{
		return $this->root;
	}
	
	
	/**
	 * @param bool $root
	 */
	public function setRoot(bool $root) : void
	{
		$this->root = $root;
	}
	
	
	/**
	 * @return int|null
	 */
	public function getSize() : ?int
	{
		return $this->size;
	}
	
	
	/**
	 * @param int|null $size
	 */
	public function setSize(?int $size) : void
	{
		$this->size = $size;
	}
	
}

