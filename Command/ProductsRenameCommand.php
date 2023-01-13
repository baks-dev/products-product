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

namespace App\Module\Products\Product\Command;

use App\Module\Products\Product\Entity\Trans\Trans;
use App\Module\Wildberries\Products\Product\Repository\ProductBarcode\ProductBarcodeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** ProductsRenameCommand */


#[AsCommand(
  name       : 'app:products:product:rename',
  description: 'Обновление названий карточек товаров')
]
class ProductsRenameCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private ProductBarcodeInterface $productBarcode;
    
    public function __construct(
      EntityManagerInterface $entityManager,
      HttpClientInterface $httpClient,
      ProductBarcodeInterface $productBarcode
      
    
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->productBarcode = $productBarcode;
    }
    
    protected function configure() {}
    
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);
        
        $httpClient = $this->httpClient->request('GET', 'https://crm.baks.dev/api/manufacture/store/products');
    
        $content = $httpClient->toArray();
    
        foreach($content as $item)
        {

           $product = $this->productBarcode->get($item['product_barcode']);
                if($product)
                {
                   
                    $trans = $this->entityManager->getRepository(Trans::class)->findBy(['event' => $product['product_event']]);
                    
                    if($trans)
                    {
                        $name = trim(str_replace(['Футболка', 'Футболки', 'Кружка', 'мужская', 'женская', "\""], '', $item['product_name']));
                        
                        if(empty($name))
                        {
                            continue;
                        }
                        
                        foreach($trans as $tran)
                        {
                            $tran->setName($name);
                        }
    
                        $this->entityManager->flush();
                        $this->entityManager->clear();
                        
                        dump($name);
                    }
                    
                    
    
                    
                }
        }

        $io->success('Команда успешно завершена');
        
        return Command::SUCCESS;
    }
    
}