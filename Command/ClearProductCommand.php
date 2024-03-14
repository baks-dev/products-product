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

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Files\Resources\Messenger\Request\Images\CDNUploadImage;
use BaksDev\Files\Resources\Messenger\Request\Images\CDNUploadImageMessage;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteDTO;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteHandler;
use BaksDev\Wildberries\Products\Entity\Cards\WbProductCard;
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
    name: 'baks:products-product:clear',
    description: 'Удаляет все карточки, у которых нет связей с маркетплейсами. Очищает корзину и неактивные события старше 30 суток')
]
class ClearProductCommand extends Command
{
    private DBALQueryBuilder $DBALQueryBuilder;
    private ProductDeleteHandler $productDeleteHandler;
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        ORMQueryBuilder $ORMQueryBuilder,
        ProductDeleteHandler $productDeleteHandler,
    )
    {
        parent::__construct();

        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->productDeleteHandler = $productDeleteHandler;
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning('Важно, чтобы были подключены все локали для очистки связей.');

        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('product.id');
        $qb->addSelect('product.event');

        $qb->from(Product::TABLE, 'product');

        /**
         * Получаем карточки, которые отсутствуют маркетплейсах
         */

        /* Wildberries */
        if(class_exists(WbProductCard::class))
        {
            $exist = $this->DBALQueryBuilder->createQueryBuilder(self::class);
            $exist->select('1');
            $exist->from(WbProductCard::TABLE, 'wb_card');
            $exist->where('wb_card.product = product.id');

            $qb->where('NOT EXISTS('.$exist->getSQL().')');

            foreach($qb->fetchAllAssociative() as $item)
            {
                $ProductDeleteDTO = new ProductDeleteDTO();
                $ProductDeleteDTO->setId(new ProductEventUid($item['event']));
                $Product = $this->productDeleteHandler->handle($ProductDeleteDTO);

                if(!$Product instanceof Product)
                {
                    $remove = $this->DBALQueryBuilder->createQueryBuilder(self::class);
                    $remove
                        ->delete(Product::TABLE, 'product')
                        ->where('product.id = :id')
                        ->setParameter('id', $item['id'])
                        ->andWhere('product.event = :event')
                        ->setParameter('event', $item['event'])
                        ->executeQuery();
                }

                $io->text(sprintf('Удалили продукцию event: %s', $item['event']));
            }
        }



        /**
         * Чистим корзину и события старше 30 суток карточки которых удалены
         */

        $clear = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $clear->select('product_modify.event');
        $clear->from(ProductModify::TABLE, 'product_modify');
        $clear->leftJoin(
            'product_modify',
            ProductEvent::TABLE,
            'product_event',
            'product_event.id = product_modify.event'
        );

        $modify = $this->DBALQueryBuilder->createQueryBuilder(self::class);
        $modify->select('1');
        $modify->from(Product::TABLE, 'product');
        $modify->where('product.id = product_event.main');

        $clear->where('NOT EXISTS('.$modify->getSQL().')');
        $clear->andWhere('product_modify.mod_date < ( NOW() - interval \'30 DAY\')');

        $EntityManager = $this->ORMQueryBuilder->getEntityManager();
        $ProductEventRepository = $EntityManager->getRepository(ProductEvent::class);


        $batchSize = 20;

        foreach($clear->fetchFirstColumn() as $i => $item)
        {
            $remove = $ProductEventRepository->find($item);
            $EntityManager->remove($remove);

            if (($i % $batchSize) === 0) {
                $EntityManager->flush();
                $EntityManager->clear();

                $io->text(sprintf('Удалили %s событий', $i) );
            }
        }


        $EntityManager->flush();
        $EntityManager->clear();

        $io->success('Команда успешно завершена');

        return Command::SUCCESS;
    }

}