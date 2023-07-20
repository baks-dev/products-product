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

use BaksDev\Products\Category\Entity\Info\ProductCategoryInfo;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group products-product
 */
final class CategoryControllerTest extends WebTestCase
{
    private const URL = '/catalog/%s';

    private const ROLE = 'ROLE_PRODUCT_EDIT';

    private static ?string $identifier = null;

    public static function setUpBeforeClass(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::$identifier = $em->getRepository(ProductCategoryInfo::class)->findOneBy([], ['event' => 'DESC'])?->getUrl();
    }

    public function testSuccessful(): void
    {
        if (self::$identifier)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $client->request('GET', sprintf(self::URL, self::$identifier));
                self::assertResponseIsSuccessful();
            }
        }
        else
        {
            self::assertTrue(true);
        }
    }
}
