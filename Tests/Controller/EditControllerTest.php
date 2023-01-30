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

namespace BaksDev\Products\Product\Tests\Controller;

use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Users\AuthEmail\Account\Repository\UserByEmail\UserByEmailInterface;
use BaksDev\Users\AuthEmail\Account\Type\Email\AccountEmail;
use App\System\Tests\UserRoleControllerTest;
use Doctrine\ORM\EntityManagerInterface;

final class EditControllerTest extends UserRoleControllerTest
{
	private const URL = '/admin/product/edit/%s';
	private const ROLE = 'ROLE_PRODUCT_EDIT';
	
	private ?ProductEventUid $event;
	
	
	/** Доступ по роли */
	public function testRoleProductSuccessful() : void
	{
		$client = static::createClient();
		
		/* Получаем одно из событий Продукта */
		$Event = $this->getProductEvent();
		
		if($Event)
		{
			$user = $this->getUserRole(self::ROLE);
			
			$client->loginUser($user, 'user');
			$client->request('GET', sprintf(self::URL, $Event->getValue()));
			
			self::assertResponseIsSuccessful();
		}
	}
	
	
	/* доступ по роли ROLE_ADMIN */
	public function testRoleAdminSuccessful() : void
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
	public function testRoleUserDeny() : void
	{
		$client = static::createClient();
		
		/* Получаем одно из событий Продукта */
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