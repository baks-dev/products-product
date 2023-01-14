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

namespace App\Module\Products\Product\Tests\Controller;

use App\Module\Users\AuthEmail\Account\Repository\UserByEmail\UserByEmailInterface;
use App\Module\Users\AuthEmail\Account\Type\Email\AccountEmail;
use App\Module\Users\User\Entity\User;
use App\System\Tests\UserRoleControllerTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IndexControllerTest extends UserRoleControllerTest
{
    private const URL = '/admin/products';
    private const ROLE = 'ROLE_PRODUCT';
    
    
    /** Доступ по роли ROLE_PRODUCT */
    public function testRoleProductSuccessful(): void
    {
        $client = static::createClient();
    
        $user = $this->getUserRole(self::ROLE);
        
        $client->loginUser($user, 'user');
        $client->request('GET', self::URL);
        
        self::assertResponseIsSuccessful();
        
    }
    
    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
        $client = static::createClient();
    
        $user = $this->getUserRole('ROLE_ADMIN');
        
        $client->loginUser($user, 'user');
        $client->request('GET', self::URL);
        
        self::assertResponseIsSuccessful();
    }
    
    
    
    /** Доступ по роли ROLE_USER */
    public function testRoleUserFiled(): void
    {
        $client = static::createClient();
        
        $user = $this->getUserRole('ROLE_USER');
        
        $client->loginUser($user, 'user');
        $client->request('GET', self::URL);
    
        self::assertResponseStatusCodeSame(403);
        
    }
    
}