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

use App\Module\Files\Res\Upload\Image\ImageUploadInterface;
use BaksDev\Products\Product\Entity\Offers\Offer\Image\Image;
use BaksDev\Products\Product\Entity\Trans\Trans;
use App\Module\Wildberries\Products\Product\Repository\ProductBarcode\ProductBarcodeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** ProductsRenameCommand */
#[AsCommand(
  name: 'baks:products:product:cdn',
  description: 'Передает файлы изображений продукта на CDN')
]
class ProductsImagesCdnCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ImageUploadInterface $imageUpload;
    private ParameterBagInterface $parameter;
    private MessageBusInterface $bus;
    private \App\Module\Files\Res\Messanger\Request\Images\Handler $handler;
    
    public function __construct(
      EntityManagerInterface $entityManager,
      ImageUploadInterface $imageUpload,
      ParameterBagInterface $parameter,
      MessageBusInterface $bus,
      \App\Module\Files\Res\Messanger\Request\Images\Handler $handler
    
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->imageUpload = $imageUpload;
        $this->parameter = $parameter;
        $this->bus = $bus;
        $this->handler = $handler;
    }
    
    protected function configure() {}
    
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        
        $productImages = $this->entityManager->getRepository(
          Image::class)->findBy(['cdn' => false]);
        
        /** @var Image $image */
        foreach($productImages as $image)
        {
            $command = new \App\Module\Files\Res\Messanger\Request\Images\Command(
              $image->getId(),
              get_class($image),
              $image->getFileName(),
              $image->getDirName(),
              'product_offer_image_dir');
            $this->bus->dispatch($command);
        }
        
        $io->success('Команда успешно завершена');
        
        return Command::SUCCESS;
        
    }
    
}