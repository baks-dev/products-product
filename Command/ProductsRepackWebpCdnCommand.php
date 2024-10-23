<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
    description: 'Сжатие обложек карточек товаров которые не пережаты'
)
]
class ProductsRepackWebpCdnCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CDNUploadImage $CDNUploadImage
    )
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
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
