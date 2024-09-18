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

namespace BaksDev\Products\Product\Controller\Admin\Tests;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewTest;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group products-product
 *
 * @depends BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewTest::class
 */
#[When(env: 'test')]
final class EditControllerTest extends WebTestCase
{
    private const URL = '/admin/product/edit/%s';
    private const ROLE = 'ROLE_PRODUCT_EDIT';


    /** Доступ по роли */
    public function testRoleSuccessful(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getModer(self::ROLE);

        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(self::URL, ProductEventUid::TEST));

        self::assertResponseIsSuccessful();

    }

    // доступ по роли ROLE_ADMIN
    public function testRoleAdminSuccessful(): void
    {


        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getAdmin();

        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(self::URL, ProductEventUid::TEST));

        self::assertResponseIsSuccessful();

    }

    // доступ по роли ROLE_USER
    public function testRoleUserDeny(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        $usr = TestUserAccount::getUsr();
        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(self::URL, ProductEventUid::TEST));

        self::assertResponseStatusCodeSame(403);

    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', sprintf(self::URL, ProductEventUid::TEST));

        // Full authentication is required to access this resource
        self::assertResponseStatusCodeSame(401);

    }
}
