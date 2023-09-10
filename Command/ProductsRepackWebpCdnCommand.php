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

//use App\Module\Files\Res\Messanger\Request\Images\Handler;
//use App\Module\Products\Product\Entity\Offers\Offer\Image\Image;
//use App\Module\Products\Product\Entity\Trans\Trans;
//use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
//use App\Module\Products\Product\Type\Offers\Image\ImageUid;
//use App\Module\Wildberries\Products\Product\Repository\ProductBarcode\ProductBarcodeInterface;
use BaksDev\Files\Resources\Messanger\Request\Images\CDNUploadImage;
use BaksDev\Files\Resources\Messanger\Request\Images\CDNUploadImageMessage;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/** ProductsRenameCommand */
#[AsCommand(
	name: 'baks:products:webp',
	description: 'Сжатие обложек карточек товаров которые не пережаты')
]
class ProductsRepackWebpCdnCommand extends Command
{
	private EntityManagerInterface $entityManager;
	
	private ParameterBagInterface $parameter;
    private string $upload;
    private CDNUploadImage $CDNUploadImage;

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/upload/')] string $upload,
		EntityManagerInterface $entityManager,
        CDNUploadImage $CDNUploadImage
	)
	{
		parent::__construct();
		$this->entityManager = $entityManager;
        $this->upload = $upload;
        $this->CDNUploadImage = $CDNUploadImage;
    }
	
	
	protected function execute(InputInterface $input, OutputInterface $output) : int
	{
		$io = new SymfonyStyle($input, $output);
		
		$offerImages = $this->entityManager->getRepository(ProductOfferImage::class)->findBy(['cdn' => false]);

		/** Применить ко всем директориям разрешение */
		// sudo find /home/crm.white-sign.ru/public/assets/images/products/offer -type d -exec chmod 773 {} \;
		
		$progressBar = new ProgressBar($output);
		$progressBar->start();
		
		/** @var $offerImage ProductOfferImage */
		foreach($offerImages as $offerImage)
		{
			$progressBar->advance();
			
			$uploadDir = $this->upload.ProductOfferImage::TABLE.'/'.$offerImage->getDir();

//			/* Пропускаем, если пережатый файл уже имеется */
//			if(is_file($uploadDir.'/'.$offerImage->getFileName()))
//			{
//				continue;
//			}

            /** Применить к директории разрешение */
            //exec('sudo find '.$uploadDir.' -type d -exec chmod 773 {} \;');
			
			//$fileInfo = pathinfo($uploadDir.'/'.$offerImage->getFileName());
			
			/* Пропускаем, если нет оригинала файла */
			if(!is_file($uploadDir.'/'.$offerImage->getFileName()))
			{
                $io->error(sprintf('Отсутствует изображение торгового предложения %s', $offerImage->getId()));
				continue;
			}

			$message = new CDNUploadImageMessage(
                $offerImage->getId(),
                ProductOfferImage::class,
                $offerImage->getFileName(),
				$offerImage->getDir(),
			);

            ($this->CDNUploadImage)($message);


		}
		
		$this->entityManager->clear();
		$progressBar->finish();
		$io->success('Команда успешно завершена');
		
		return Command::SUCCESS;
	}
	
}