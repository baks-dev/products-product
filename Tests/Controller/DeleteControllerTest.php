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

use App\Module\Products\Product\Entity\Event\ProductEvent;
use App\Module\Products\Product\Type\Event\ProductEventUid;
use App\Module\User\AuthEmail\Account\Repository\UserByEmail\UserByEmailInterface;
use App\Module\User\AuthEmail\Account\Type\Email\AccountEmail;
use App\Module\User\User\Entity\User;
use App\System\Tests\UserRoleControllerTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DeleteControllerTest extends UserRoleControllerTest
{
    private const URL = '/admin/product/delete/%s';
    private const ROLE = 'ROLE_PRODUCT_DELETE';
    private ?ProductEventUid $event;
    
    /** Доступ по роли */
    public function testRoleProductSuccessful(): void
    {
        $client = static::createClient();
        
        /* Получаем одно из событий Продукта */
        $Event = $this->getProductEvent();
        
        if($Event)
        {
            $user = $this->getUserRole('ROLE_PRODUCT_DELETE');
            
            $client->loginUser($user, 'user');
            $client->request('GET', sprintf(self::URL, $Event->getValue()));
            
            self::assertResponseIsSuccessful();
        }
    }
    
    /* доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
        $client = static::createClient();
        
        /* Получаем одно из событий Продукта */
        $Event = $this->getProductEvent();
        
        if($Event)
        {
            $user = $this->getUserRole('ROLE_ADMIN');
            
            $client->loginUser($user, 'user');
            $client->request('GET', sprintf(self::URL, $Event->getValue()));
            
            self::assertResponseIsSuccessful();
        }
    }
    
    /* доступ по роли ROLE_USER */
    public function testRoleUserDeny(): void
    {
        $client = static::createClient();
        
        $Event = $this->getProductEvent();
        
        if($Event)
        {
            $user = $this->getUserRole('ROLE_USER');
            
            $client->loginUser($user, 'user');
            $client->request('GET', sprintf(self::URL, $Event->getValue()));
            
            self::assertResponseStatusCodeSame(403);
        }
    }
    
    public function getProductEvent() : ?ProductEventUid
    {
        if(empty($this->event))
        {
            /* Получаем одно из событий Продукта */
            $em = static::getContainer()->get(EntityManagerInterface::class);
            $this->event = $em->getRepository(ProductEvent::class)->findOneBy([])->getId();
        }
        
        return $this->event;
    }
    
}