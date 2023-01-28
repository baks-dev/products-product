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

namespace BaksDev\Products\Product\UseCase;

use App\Module\Files\Res\Upload\File\FileUploadInterface;
use App\Module\Files\Res\Upload\Image\ImageUploadInterface;

//use App\Module\Products\Category\Entity\Category;
use BaksDev\Products\Product\Entity\Event\ProductEventInterface;
use BaksDev\Products\Product\Repository\UniqProductUrl\UniqProductUrlInterface;
use App\System\Type\Modify\ModifyActionEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use BaksDev\Products\Product\Entity;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductAggregate
{
    private EntityManagerInterface $entityManager;
    private ImageUploadInterface $imageUpload;
    private FileUploadInterface $fileUpload;
    private UniqProductUrlInterface $uniqProductUrl;
    private ValidatorInterface $validator;
    
    public function __construct(
      EntityManagerInterface $entityManager,
      ImageUploadInterface $imageUpload,
      FileUploadInterface $fileUpload,
      UniqProductUrlInterface $uniqProductUrl,
      ValidatorInterface $validator
   
    )
    {
        $this->entityManager = $entityManager;
        $this->imageUpload = $imageUpload;
        $this->fileUpload = $fileUpload;
        $this->uniqProductUrl = $uniqProductUrl;
        $this->validator = $validator;
    }
    
    public function handle(
      ProductEventInterface $command
    ) : mixed
    {
        $errors = $this->validator->validate($command);
    
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new ValidatorException($errorsString);
        }
        
        
        
        
        
        if($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(Entity\Event\ProductEvent::class)->find($command->getEvent());
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new Entity\Event\ProductEvent();
        }
    
        
        
//
//        dump('clone Event');
//        dump($Event);
//
//

//
//        dump('setEntity Event');
//        dump($Event);
//
//        dd($command);
    
        $Event->setEntity($command);
        $this->entityManager->clear();
        $this->entityManager->persist($Event);
        
        /* Загрузка базового фото галлереи */
        if(method_exists($command, 'getPhotos') && !$command->getPhotos()->isEmpty())
        {
            /** @var \BaksDev\Products\Product\UseCase\Admin\NewEdit\Photo\PhotoCollectionDTO $getPhotos */
            foreach($command->getPhotos() as $key => $getPhotos)
            {
                if(!empty($getPhotos->file))
                {
                    /* Загружаем изображение */
                    $this->imageUpload->upload(
                      'product_image_dir',
                      $getPhotos->file,
                      $eventPhoto = new Entity\Photo\Photo($Event));
                    
                    !$getPhotos->isRoot() ?: $eventPhoto->root();
                    
                    $this->entityManager->persist($eventPhoto);
                    
                    if(!empty($getPhotos->getName()))
                    {
                        $command->getPhotos()->remove($key);
                    }
                }
            }
        }
    
        /* Загрузка файлов PDF галлереи */
        if(method_exists($command, 'getFiles') && !$command->getFiles()->isEmpty())
        {
            /** @var \BaksDev\Products\Product\UseCase\Admin\NewEdit\Files\FilesCollectionDTO $getFiles */
            foreach($command->getFiles() as $getFiles)
            {
                if(!empty($getFiles->file) && empty($getFiles->getName()))
                {
                    /* Загружаем файл */
                    $this->fileUpload->upload(
                      'product_file_dir',
                      $getFiles->file,
                      $eventFile = new Entity\Files\Files($Event));
                    $this->entityManager->persist($eventFile);
                }
            }
        }
        
        /* Загрузка файлов Видео галлереи */
        if(method_exists($command, 'getVideos') && !$command->getVideos()->isEmpty())
        {
            /** @var \BaksDev\Products\Product\UseCase\Admin\NewEdit\Video\VideoCollectionDTO $getVideos */
            foreach($command->getVideos() as $getVideos)
            {
                if(!empty($getVideos->file) && empty($getVideos->getName()))
                {
                    /* Загружаем файл */
                    $this->fileUpload->upload(
                      'product_video_dir',
                      $getVideos->file,
                      $eventVideo = new Entity\Video\Video($Event));
                    $this->entityManager->persist($eventVideo);
                }
            }
        }
        
        /* Загрузка фото торгового предложения */
        if(method_exists($command, 'getOffers') && !$command->getOffers()->isEmpty())
        {
            foreach($command->getOffers() as $getOffers)
            {
                if(!$getOffers->getOffer()->isEmpty())
                {
                    foreach($getOffers->getOffer() as $getOffer)
                    {
                        if(method_exists($getOffer, 'getImages')  && !$getOffer->getImages()->isEmpty())
                        {
                            /** @var \BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Image\ImageCollectionDTO $getImages */
                            foreach($getOffer->getImages() as $getImages)
                            {
                                if(!empty($getImages->file) && empty($getImages->getName()))
                                {
                                    /* Загружаем изображение */
                                    $this->imageUpload->upload(
                                      'product_offer_image_dir',
                                      $getImages->file,
                                      $offerImage = new Entity\Offers\Offer\Image\Image($getImages->getImgOffer()));
                                    
                                    !$getImages->isRoot() ?: $offerImage->root();
                                    
                                    $this->entityManager->persist($offerImage);
                                    
                                }
                            }
                        }
                    }
                }
            }
        }
        
        //
        //
        //        dump($Event);
        //        dd($command);
        
        //$Event->updCategoryEvent($command);
        
        /* Загрузка файла изображения */
        //        if(!empty($cover))
        //        {
        //            $this->imageUpload->upload('products', $cover, $Event->getUploadCover());
        //        }
        
        //dump($command);
        //dd($Event);
    
    
        
        
        $infoDTO = method_exists($command, 'getInfo') ? $command->getInfo() : false;
        
        
        /** @var Entity\Product $Product */
        if($Event->getProduct())
        {
            /* Восстанавливаем из корзины */
            if($Event->isModifyActionEquals(ModifyActionEnum::RESTORE))
            {
                $Product = new Entity\Product();
                $Product->setId($Event->getProduct());
                $this->entityManager->persist($Product);
    
    
                $Info = new Entity\Info\Info($Product);
                $this->entityManager->persist($Info);
                
                
                $remove = $this->entityManager->getRepository(Entity\Event\ProductEvent::class)
                  ->find($command->getEvent());
                $this->entityManager->remove($remove);
                
            }
            else
            {
                $Product = $this->entityManager->getRepository(Entity\Product::class)->findOneBy(
                  ['event' => $command->getEvent()]);
    
                $Info = $this->entityManager->getRepository(Entity\Info\Info::class)->find($Product->getId());
            }
            
            if(empty($Product))
            {
                return false;
            }
        }
        else
        {
            $Product = new Entity\Product();
            $this->entityManager->persist($Product);
    
            $Info = new Entity\Info\Info($Product);
            $this->entityManager->persist($Info);
            
            $Event->setProduct($Product);
            
        }
        
        /* Если URL изменяется */
        if(is_object($infoDTO) && method_exists($infoDTO, 'getUrl'))
        {
            /* Проверяем на уникальность Адрес персональной страницы */
            $uniqProductUrl =  $this->uniqProductUrl->get($infoDTO->getUrl(), $Product->getId());
            if($uniqProductUrl) { $infoDTO->updateUrlUniq(); } /* Обновляем URL на уникальный с префиксом */
        }
        
        
        /* Удаляем категорию */
        if($Event->isModifyActionEquals(ModifyActionEnum::DELETE))
        {
            $this->entityManager->remove($Product);
            $infoDTO->updateUrlUniq();
        }
    
        if($infoDTO) { $Info->setEntity($infoDTO); /* Обновляем INFO */ }
        
        $Product->setEvent($Event); /* Обновляем событие */
        
        
        //dd($this->entityManager->getUnitOfWork());
        
        
        $this->entityManager->flush();

        return $Product;
    }
    
}