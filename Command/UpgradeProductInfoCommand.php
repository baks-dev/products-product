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

namespace BaksDev\Products\Product\Command;

use BaksDev\Core\Command\Update\ProjectUpgradeInterface;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Menu\Admin\Entity\MenuAdmin;
use BaksDev\Menu\Admin\Repository\ActiveEventMenuAdmin\ActiveMenuAdminEventRepositoryInterface;
use BaksDev\Menu\Admin\Repository\ExistPath\MenuAdminExistPathRepositoryInterface;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminHandler;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\MenuAdminPathDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\MenuAdminPathSectionDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\Path\MenuAdminPathSectionPathDTO;
use BaksDev\Menu\Admin\UseCase\Command\Menu\MenuAdminPath\Section\Path\Trans\MenuAdminPathSectionPathTransDTO;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Repository\CurrentProductEvent\CurrentProductEventInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'baks:products-product:upgrade:info',
    description: 'Обновляет ссылки меню администратора',
    aliases: ['baks:project:upgrade:products-product:info']
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeProductInfoCommand extends Command implements ProjectUpgradeInterface
{

    private ORMQueryBuilder $ORMQueryBuilder;
    private CurrentProductEventInterface $currentProductEvent;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder,
        CurrentProductEventInterface $currentProductEvent
    )
    {
        parent::__construct();


        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->currentProductEvent = $currentProductEvent;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Обновляем сущность Info продукции');


        $em = $this->ORMQueryBuilder->getEntityManager();
        $ProductsInfo = $em->getRepository(ProductInfo::class)->findBy(['event' => null]);


        /** @var ProductInfo $ProductInfo */
        foreach($ProductsInfo as $i => $ProductInfo)
        {
            $ProductEvent = $this->currentProductEvent->getProductEvent($ProductInfo->getProduct());

            if(!$ProductEvent)
            {
                continue;
            }

            $ProductInfo->setEvent($ProductEvent);
        }

        $em->flush();
        $em->clear();

        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 0;
    }
}
