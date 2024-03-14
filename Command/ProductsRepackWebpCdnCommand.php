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

namespace BaksDev\Products\Product\Command;

use BaksDev\Files\Resources\Messenger\Request\Images\CDNUploadImage;
use BaksDev\Files\Resources\Messenger\Request\Images\CDNUploadImageMessage;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'baks:products-product:webp',
	description: 'Сжатие обложек карточек товаров которые не пережаты')
]
class ProductsRepackWebpCdnCommand extends Command
{
	private EntityManagerInterface $entityManager;

    private CDNUploadImage $CDNUploadImage;

    public function __construct(

        EntityManagerInterface $entityManager,
        CDNUploadImage $CDNUploadImage
	)
	{
        //$this->upload = $upload;
        $this->CDNUploadImage = $CDNUploadImage;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
	
	
	protected function execute(InputInterface $input, OutputInterface $output) : int
	{
		$io = new SymfonyStyle($input, $output);


        $progressBar = new ProgressBar($output);
        $progressBar->start();


        /** Галерея фото */

        $productPhotos = $this->entityManager->getRepository(ProductPhoto::class)->findBy(['cdn' => false]);
        $this->entityManager->clear();

        /** @var $productPhoto ProductPhoto */
        foreach($productPhotos as $k => $productPhoto)
        {
            $message = new CDNUploadImageMessage(
                $productPhoto->getId(),
                ProductPhoto::class,
                $productPhoto->getPathDir()
            );

            ($this->CDNUploadImage)($message);

            $progressBar->advance();
        }


        /** Торговые предложения */
		
		$offerImages = $this->entityManager->getRepository(ProductOfferImage::class)->findBy(['cdn' => false]);
        $this->entityManager->clear();
		
		/** @var $offerImage ProductOfferImage */
		foreach($offerImages as $offerImage)
		{
			$message = new CDNUploadImageMessage(
                $offerImage->getId(),
                ProductOfferImage::class,
                $offerImage->getPathDir()
			);

            ($this->CDNUploadImage)($message);

            $progressBar->advance();
		}


        /** Множественные варианты  */

        $variationImages = $this->entityManager->getRepository(ProductVariationImage::class)->findBy(['cdn' => false]);
        $this->entityManager->clear();

        /** @var $variationImage ProductVariationImage */
        foreach($variationImages as $variationImage)
        {
            $message = new CDNUploadImageMessage(
                $variationImage->getId(),
                ProductVariationImage::class,
                $variationImage->getPathDir()
            );

            ($this->CDNUploadImage)($message);

            $progressBar->advance();
        }



        /** Модификации множественных вариантов */

        $modificationImages = $this->entityManager->getRepository(ProductModificationImage::class)->findBy(['cdn' => false]);
        $this->entityManager->clear();

        /** @var $modificationImage ProductModificationImage */
        foreach($modificationImages as $modificationImage)
        {
            $message = new CDNUploadImageMessage(
                $modificationImage->getId(),
                ProductModificationImage::class,
                $modificationImage->getPathDir()
            );

            ($this->CDNUploadImage)($message);

            $progressBar->advance();
        }



		$progressBar->finish();
		$io->success('Команда успешно завершена');
		
		return Command::SUCCESS;
	}
	
}