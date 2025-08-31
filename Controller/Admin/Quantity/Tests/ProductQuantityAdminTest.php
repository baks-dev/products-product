<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Products\Product\Controller\Admin\Quantity\Tests;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Tests\ProductsProductNewAdminUseCaseTest;
use BaksDev\Users\User\Tests\TestUserAccount;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
final class ProductQuantityAdminTest extends WebTestCase
{
    private const string URL = '/admin/product/quantity/%s/%s/%s/%s';
    private const string ROLE = 'ROLE_PRODUCT_QUANTITY';

    /** Доступ по роли ROLE_PRODUCT */
    #[DependsOnClass(ProductsProductNewAdminUseCaseTest::class)]
    public function testRoleSuccessful(): void
    {
        $client = static::createClient();

        $usr = TestUserAccount::getModer(self::ROLE);

        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(
            self::URL,
            ProductEventUid::TEST,
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        ));

        self::assertResponseIsSuccessful();

    }

    /** Доступ по роли ROLE_ADMIN */
    #[DependsOnClass(ProductsProductNewAdminUseCaseTest::class)]
    public function testRoleAdminSuccessful(): void
    {
        $client = static::createClient();

        $usr = TestUserAccount::getAdmin();

        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(
            self::URL,
            ProductEventUid::TEST,
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        ));

        self::assertResponseIsSuccessful();
    }

    /** Доступ по роли ROLE_USER */
    #[DependsOnClass(ProductsProductNewAdminUseCaseTest::class)]
    public function testRoleUserFiled(): void
    {
        $client = static::createClient();

        $usr = TestUserAccount::getUsr();
        $client->loginUser($usr, 'user');
        $client->request('GET', sprintf(
            self::URL,
            ProductEventUid::TEST,
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        ));

        self::assertResponseStatusCodeSame(403);
    }

    /** Доступ по без роли */
    #[DependsOnClass(ProductsProductNewAdminUseCaseTest::class)]
    public function testGuestFiled(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', sprintf(
            self::URL,
            ProductEventUid::TEST,
            ProductOfferUid::TEST,
            ProductVariationUid::TEST,
            ProductModificationUid::TEST,
        ));

        // Full authentication is required to access this resource
        self::assertResponseStatusCodeSame(401);
    }
}