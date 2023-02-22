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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit;

use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Products\Product\Repository\UniqProductUrl\UniqProductUrlInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use BaksDev\Products\Product\Entity;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductHandler
{
	private EntityManagerInterface $entityManager;
	
	private ImageUploadInterface $imageUpload;
	
	private FileUploadInterface $fileUpload;
	
	private UniqProductUrlInterface $uniqProductUrl;
	
	private ValidatorInterface $validator;
	
	private LoggerInterface $logger;
	
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageUploadInterface $imageUpload,
		FileUploadInterface $fileUpload,
		UniqProductUrlInterface $uniqProductUrl,
		ValidatorInterface $validator,
		LoggerInterface $logger,
	
	)
	{
		$this->entityManager = $entityManager;
		$this->imageUpload = $imageUpload;
		$this->fileUpload = $fileUpload;
		$this->uniqProductUrl = $uniqProductUrl;
		$this->validator = $validator;
		$this->logger = $logger;
	}
	
	
	public function handle(
		ProductDTO $command,
	) : mixed
	{
		
		/* Валидация */
		$errors = $this->validator->validate($command);
		
		if(count($errors) > 0)
		{
			$uniqid = uniqid('', false);
			$errorsString = (string) $errors;
			$this->logger->error($uniqid.': '.$errorsString);
			
			return $uniqid;
		}
		
		/* Объявялем событие */
		if($command->getEvent())
		{
			$EventRepo = $this->entityManager->getRepository(Entity\Event\ProductEvent::class)->find(
				$command->getEvent()
			);
			
			if($EventRepo === null)
			{
				$uniqid = uniqid('', false);
				$errorsString = sprintf(
					'Not found %s by id: %s',
					Entity\Event\ProductEvent::class,
					$command->getEvent()
				);
				$this->logger->error($uniqid.': '.$errorsString);
				
				return $uniqid;
			}
			
			$Event = $EventRepo->cloneEntity();
			
		}
		else
		{
			$Event = new Entity\Event\ProductEvent();
			$this->entityManager->persist($Event);
		}
		
		$this->entityManager->clear();
		
		$Event->setEntity($command);
		
		/* Загрузка базового фото галлереи */
		foreach($command->getPhoto() as $Photo)
		{
			/** Загружаем базового фото галлереи
			 *
			 * @var Photo\PhotoCollectionDTO $Photo
			 */
			if($Photo->file !== null)
			{
				/** TODO  **/
				$ProductPhoto = $Photo->getEntityUpload();
				$this->imageUpload->upload($Photo->file, $ProductPhoto);
			}
		}
		
		/* Загрузка файлов PDF галлереи */
		foreach($command->getFile() as $File)
		{
			/** Загружаем базового фото галлереи
			 *
			 * @var Files\FilesCollectionDTO $File
			 */
			if($File->file !== null)
			{
				/** TODO  **/
				$ProductFile = $File->getEntityUpload();
				$this->fileUpload->upload($File->file, $ProductFile);
			}
		}
		
		/* Загрузка файлов Видео галлереи */
		foreach($command->getVideo() as $Video)
		{
			/** Загружаем базового фото галлереи
			 *
			 * @var Video\VideoCollectionDTO $Video
			 */
			if($Video->file !== null)
			{
				/** TODO  **/
				$ProductVideo = $Video->getEntityUpload();
				$this->fileUpload->upload($Video->file, $ProductVideo);
			}
		}
		
		/** Загрузка фото торгового предложения
		 *
		 * @var Offers\ProductOffersCollectionDTO $offer
		 */
		foreach($command->getOffer() as $offer)
		{
			/** Загрузка фото торгового предложения
			 *
			 * @var Offers\Image\ProductOfferImageCollectionDTO $offerImage
			 */
			
			foreach($offer->getImage() as $offerImage)
			{
				if($offerImage->file !== null)
				{
					/** TODO  **/
					$ProductOfferImage = $offerImage->getEntityUpload();
					$this->fileUpload->upload($offerImage->file, $ProductOfferImage);
				}
			}
			
			/** Загрузка фото множественного варианта
			 *
			 * @var Offers\Variation\ProductOffersVariationCollectionDTO $variation
			 */
			foreach($offer->getVariation() as $variation)
			{
				/** Загрузка фото торгового предложения
				 *
				 * @var Offers\Variation\Image\ProductOfferVariationImageCollectionDTO $variationImage
				 */
				
				foreach($variation->getImage() as $variationImage)
				{
					if($variationImage->file !== null)
					{
						/** TODO  **/
						$ProductOfferVariationImage = $variationImage->getEntityUpload();
						$this->fileUpload->upload($variationImage->file, $ProductOfferVariationImage);
					}
				}
			}
		}
		
		/** @var Entity\Product $Product */
		if($Event->getProduct())
		{
			/* Получаем продукт */
			$Product = $this->entityManager->getRepository(Entity\Product::class)
				->findOneBy(['event' => $command->getEvent()])
			;
			
			/* Получаем информацию о продукте */
			$ProductInfo = $this->entityManager->getRepository(Entity\Info\ProductInfo::class)
				->find($Product->getId())
			;
			
			if(empty($Product))
			{
				$uniqid = uniqid('', false);
				$errorsString = sprintf(
					'Not found %s by event: %s',
					Entity\Product::class,
					$command->getEvent()
				);
				$this->logger->error($uniqid.': '.$errorsString);
				
				return $uniqid;
			}
		}
		else
		{
			$Product = new Entity\Product();
			$this->entityManager->persist($Product);
			
			$ProductInfo = new Entity\Info\ProductInfo($Product);
			$this->entityManager->persist($ProductInfo);
			
			$Event->setProduct($Product);
			
		}
		
		/** Проверяем уникальность семантической ссылки продукта */
		$infoDTO = $command->getInfo();
		$uniqProductUrl = $this->uniqProductUrl->get($infoDTO->getUrl(), $Product->getId());
		if($uniqProductUrl)
		{
			$infoDTO->updateUrlUniq(); /* Обновляем URL на уникальный с префиксом */
		}
		
		$ProductInfo->setEntity($infoDTO); /* Обновляем ProductInfo */
		$Product->setEvent($Event); /* Обновляем событие */
		
		$this->entityManager->persist($Event);
		//dump($this->entityManager->getUnitOfWork());
		//dd($Event);
		

		$this->entityManager->flush();
		
		return $Product;
	}
	
}