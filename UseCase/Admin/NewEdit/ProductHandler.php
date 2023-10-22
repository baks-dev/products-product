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

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Messenger\ProductMessage;
use BaksDev\Products\Product\Repository\UniqProductUrl\UniqProductUrlInterface;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Files\FilesCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Image\ProductOfferImageCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Image\ProductVariationImageCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Modification\Image\ProductModificationImageCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Photo\PhotoCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Video\VideoCollectionDTO;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final class ProductHandler extends AbstractHandler
{

    private UniqProductUrlInterface $uniqProductUrl;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,
        UniqProductUrlInterface $uniqProductUrl,
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);


        $this->uniqProductUrl = $uniqProductUrl;
    }

    public function handle(ProductDTO $command): Product|string
    {
        /* Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new Product();
        $this->event = new ProductEvent();

        try
        {
            $command->getEvent() ? $this->preUpdate($command) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid;
        }

        /** Проверяем уникальность семантической ссылки продукта */
        $infoDTO = $command->getInfo();
        $uniqProductUrl = $this->uniqProductUrl->get($infoDTO->getUrl(), $this->main->getId());
        if($uniqProductUrl)
        {
            $infoDTO->updateUrlUniq(); // Обновляем URL на уникальный с префиксом
        }

        // Загрузка базового фото галереи
        foreach($this->event->getPhoto() as $ProductPhoto)
        {
            /** @var PhotoCollectionDTO $PhotoCollectionDTO */
            $PhotoCollectionDTO = $ProductPhoto->getEntityDto();

            if(null !== $PhotoCollectionDTO->file)
            {
                $this->imageUpload->upload($PhotoCollectionDTO->file, $ProductPhoto);
            }
        }

        // Загрузка файлов PDF галереи
        foreach($this->event->getFile() as $ProductFile)
        {
            /** @var FilesCollectionDTO $FilesCollectionDTO */
            $FilesCollectionDTO = $ProductFile->getEntityDto();

            if($FilesCollectionDTO->file !== null)
            {
                $this->fileUpload->upload($FilesCollectionDTO->file, $ProductFile);
            }
        }


        // Загрузка файлов Видео галереи
        foreach($this->event->getVideo() as $ProductVideo)
        {
            /** @var VideoCollectionDTO $VideoCollectionDTO */
            $VideoCollectionDTO = $ProductVideo->getEntityDto();

            if($VideoCollectionDTO->file !== null)
            {
                $this->fileUpload->upload($VideoCollectionDTO->file, $ProductVideo);
            }
        }


        /**
         * Загрузка фото торгового предложения.
         *
         * @var ProductOffer $ProductOffer
         */

        foreach($this->event->getOffer() as $ProductOffer)
        {

            /** @var ProductOfferImage $ProductOfferImage */
            foreach($ProductOffer->getImage() as $ProductOfferImage)
            {
                /** @var ProductOfferImageCollectionDTO $ProductOfferImageCollectionDTO */
                $ProductOfferImageCollectionDTO = $ProductOfferImage->getEntityDto();

                if($ProductOfferImageCollectionDTO->file !== null)
                {
                    $this->imageUpload->upload($ProductOfferImageCollectionDTO->file, $ProductOfferImage);
                }
            }

            /** @var ProductVariation $ProductVariation */
            foreach($ProductOffer->getVariation() as $ProductVariation)
            {
                /** @var ProductVariationImage $ProductVariationImage */
                foreach($ProductVariation->getImage() as $ProductVariationImage)
                {
                    /** @var ProductVariationImageCollectionDTO $ProductVariationImageCollectionDTO */
                    $ProductVariationImageCollectionDTO = $ProductVariationImage->getEntityDto();

                    if($ProductVariationImageCollectionDTO->file !== null)
                    {
                        $this->imageUpload->upload($ProductVariationImageCollectionDTO->file, $ProductVariationImage);
                    }
                }

                /** @var ProductModification $ProductModification */
                foreach($ProductVariation->getModification() as $ProductModification)
                {

                    /** @var ProductModificationImage $ProductModificationImage */
                    foreach($ProductModification->getImage() as $ProductModificationImage)
                    {
                        /** @var ProductModificationImageCollectionDTO $ProductModificationImageCollectionDTO */
                        $ProductModificationImageCollectionDTO = $ProductModificationImage->getEntityDto();

                        if($ProductModificationImageCollectionDTO->file !== null)
                        {
                            $this->imageUpload->upload($ProductModificationImageCollectionDTO->file, $ProductModificationImage);
                        }
                    }

                }
            }
        }

        /* Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ProductMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'products',
        );

        return $this->main;

    }


    public function OLDhandle(ProductDTO $command): Product|string
    {
        // Объявялем событие
        if($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(ProductEvent::class)->find(
                $command->getEvent(),
            );

            if(null === $EventRepo)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    ProductEvent::class,
                    $command->getEvent(),
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new ProductEvent();
            $Event->setEntity($command);
            $this->entityManager->persist($Event);
        }

        //        $this->entityManager->clear();
        //        $this->entityManager->persist($Event);

        //
        //        // Загрузка базового фото галлереи
        //        foreach($command->getPhoto() as $Photo)
        //        {
        //            /**
        //             * Загружаем базового фото галлереи.
        //             *
        //             * @var Photo\PhotoCollectionDTO $Photo
        //             */
        //            if(null !== $Photo->file)
        //            {
        //                $ProductPhoto = $Photo->getEntityUpload();
        //                $this->imageUpload->upload($Photo->file, $ProductPhoto);
        //            }
        //        }
        //
        //        // Загрузка файлов PDF галлереи
        //        foreach($command->getFile() as $File)
        //        {
        //            /**
        //             * Загружаем базового фото галлереи.
        //             *
        //             * @var Files\FilesCollectionDTO $File
        //             */
        //            if(null !== $File->file)
        //            {
        //                $ProductFile = $File->getEntityUpload();
        //                $this->fileUpload->upload($File->file, $ProductFile);
        //            }
        //        }

        //        // Загрузка файлов Видео галлереи
        //        foreach($command->getVideo() as $Video)
        //        {
        //            /**
        //             * Загружаем базового фото галлереи.
        //             *
        //             * @var Video\VideoCollectionDTO $Video
        //             */
        //            if(null !== $Video->file)
        //            {
        //                /** TODO  */
        //                $ProductVideo = $Video->getEntityUpload();
        //                $this->imageUpload->upload($Video->file, $ProductVideo);
        //            }
        //        }

        /** Загрузка фото торгового предложения.
         *
         * @var Offers\ProductOffersCollectionDTO $offer
         */
        foreach($command->getOffer() as $offer)
        {
            /**
             * Загрузка фото торгового предложения.
             *
             * @var Offers\Image\ProductOfferImageCollectionDTO $offerImage
             */
            foreach($offer->getImage() as $offerImage)
            {
                if(null !== $offerImage->file)
                {
                    /** TODO  */
                    $ProductOfferImage = $offerImage->getEntityUpload();
                    $this->imageUpload->upload($offerImage->file, $ProductOfferImage);
                }
            }

            /**
             * Загрузка фото множественного варианта.
             *
             * @var Offers\Variation\ProductOffersVariationCollectionDTO $variation
             */
            foreach($offer->getVariation() as $variation)
            {
                /** Загрузка фото торгового предложения.
                 *
                 * @var Offers\Variation\Image\ProductVariationImageCollectionDTO $variationImage
                 */
                foreach($variation->getImage() as $variationImage)
                {
                    if(null !== $variationImage->file)
                    {
                        /** TODO  */
                        $ProductOfferVariationImage = $variationImage->getEntityUpload();
                        $this->imageUpload->upload($variationImage->file, $ProductOfferVariationImage);
                    }
                }

                /**
                 * Загрузка фото модификации множественного варианта.
                 *
                 * @var Offers\Variation\Modification\ProductOffersVariationModificationCollectionDTO $modification
                 */
                foreach($variation->getModification() as $modification)
                {
                    /** Загрузка фото торгового предложения.
                     *
                     * @var Offers\Variation\Modification\Image\ProductModificationImageCollectionDTO $modificationImage
                     */
                    foreach($modification->getImage() as $modificationImage)
                    {
                        if(null !== $modificationImage->file)
                        {
                            /** TODO  */
                            $ProductOfferVariationModificationImage = $modificationImage->getEntityUpload();
                            $this->imageUpload->upload(
                                $modificationImage->file,
                                $ProductOfferVariationModificationImage,
                            );
                        }
                    }
                }
            }
        }


        if($Event->getMain())
        {
            // Получаем продукт
            $Product = $this->entityManager->getRepository(Product::class)
                ->findOneBy(['event' => $command->getEvent()]);

            // Получаем информацию о продукте
            $ProductInfo = $this->entityManager->getRepository(ProductInfo::class)
                ->find($Product->getId());

            if(empty($Product))
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by event: %s',
                    Product::class,
                    $command->getEvent(),
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }
        }
        else
        {
            $Product = new Product();
            $this->entityManager->persist($Product);

            $ProductInfo = new ProductInfo($Product);
            $this->entityManager->persist($ProductInfo);

            $Event->setMain($Product);
        }

        /** Проверяем уникальность семантической ссылки продукта */
        $infoDTO = $command->getInfo();
        $uniqProductUrl = $this->uniqProductUrl->get($infoDTO->getUrl(), $Product->getId());
        if($uniqProductUrl)
        {
            $infoDTO->updateUrlUniq(); // Обновляем URL на уникальный с префиксом
        }

        $ProductInfo->setEntity($infoDTO); // Обновляем ProductInfo
        $Product->setEvent($Event); // Обновляем событие


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }


        //$this->getRemoveEntity($Event);
        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ProductMessage($Product->getId(), $Product->getEvent(), $command->getEvent()),
            transport: 'products',
        );

        return $Product;
    }


    //    public function getRemoveEntity($Event): void
    //    {
    //        if($Event->getRemoveEntity())
    //        {
    //            foreach($Event->getRemoveEntity() as $remove)
    //            {
    //                $this->entityManager->remove($remove);
    //            }
    //        }
    //    }
}
