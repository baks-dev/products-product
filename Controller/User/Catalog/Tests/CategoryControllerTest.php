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

namespace BaksDev\Products\Product\Controller\User\Catalog\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group products-product */
#[When(env: 'test')]
final class CategoryControllerTest extends WebTestCase
{
    private const URL = '/catalog/%s';

    private const ROLE = 'ROLE_PRODUCT_EDIT';

    private static ?string $identifier = null;

    public static function setUpBeforeClass(): void
    {
        /** @var DBALQueryBuilder $qb */
        $qb = self::getContainer()->get(DBALQueryBuilder::class);

        /** @var DBALQueryBuilder $dbal */
        $dbal = $qb->createQueryBuilder(self::class);

        $dbal->select('info.url');
        $dbal->from(CategoryProduct::class, 'category');
        $dbal->leftJoin(
            'category',
            CategoryProductInfo::class,
            'info',
            'info.event = category.event'
        );

        $dbal->orderBy('category.event', 'DESC');

        self::$identifier = $dbal->fetchOne();
    }

    public function testSuccessful(): void
    {
        if(self::$identifier)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $client->request('GET', sprintf(self::URL, self::$identifier));
                self::assertResponseIsSuccessful();
            }
        }

        self::assertTrue(true);
    }
}
