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

namespace App\Module\Products\Product\Repository\UniqProductUrl;

use App\Module\Products\Product\Entity\Info\Info;
use App\Module\Products\Product\Type\Id\ProductUid;
use Doctrine\DBAL\Connection;

final class UniqProductUrlQuery implements UniqProductUrlInterface
{
    
    private Connection $connection;

    
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get(string $url, ProductUid $product) : mixed
    {
        $qbSub = $this->connection->createQueryBuilder();
        $qbSub->select('1');
        $qbSub->from(Info::TABLE, 'info');
        $qbSub->where('info.url = :url');
        $qbSub->andWhere('info.product != :product');
        
        
        $qb = $this->connection->createQueryBuilder();
        $qb->select('EXISTS(' . $qbSub->getSQL() . ')');
        $qb->setParameter('url', $url);
        $qb->setParameter('product', $product);

        return $qb->executeQuery()->fetchOne();
    }
    
}