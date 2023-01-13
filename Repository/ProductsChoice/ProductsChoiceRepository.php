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

namespace App\Module\Products\Product\Repository\ProductsChoice;

use App\Module\Products\Product\Entity;
use App\Module\Products\Product\Type\Id\ProductUid;
use App\System\Type\Locale\Locale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductsChoiceRepository implements ProductsChoiceInterface
{
    
    private EntityManagerInterface $entityManager;
    private Locale $local;
    
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->local = new Locale( $translator->getLocale());
    }
    
    public function get()
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        $select = sprintf("new %s(product.id, CONCAT(trans.name, ', ', info.article))", ProductUid::class);
        
        $qb->select($select);
        
        $qb->from(Entity\Product::class, 'product');
        //$qb->from(Entity\Event\Event::class, 'event');
        $qb->join(Entity\Event\ProductEvent::class, 'event', 'WITH', 'event.id = product.event');
        

        $qb->join(
          Entity\Trans\Trans::class,
          'trans',
          'WITH',
          'trans.event = event.id AND trans.local = :local');
        
        $qb->setParameter('local', $this->local, Locale::TYPE);
    
    
        $qb->join(Entity\Info\Info::class, 'info', 'WITH', 'info.product = product.id');
        
        return $qb->getQuery()->getResult();
    }
    
}