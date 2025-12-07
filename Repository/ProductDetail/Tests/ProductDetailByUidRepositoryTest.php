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

namespace BaksDev\Products\Product\Repository\ProductDetail\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class ProductDetailByUidRepositoryTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        /** @var ProductDetailByEventInterface $ProductDetailByUidRepository */
        $ProductDetailByUidRepository = self::getContainer()->get(ProductDetailByEventInterface::class);

        $ProductDetailByUidResult = $ProductDetailByUidRepository
            ->event(new ProductEventUid('019739b9-1855-7eaf-90e7-094390e8df54'))
            ->offer(new ProductOfferUid('019739b9-1875-78bf-b834-9ddc03b464c0'))
            ->variation(new ProductVariationUid('019739b9-1875-78bf-b834-9ddc04ad9507'))
            ->modification(new ProductModificationUid('019739b9-1876-77c3-adc2-aa1d59f680bd'))
            ->findResult();

        if($ProductDetailByUidResult instanceof ProductDetailByEventResult)
        {
            $ProductDetailByUidResult->getProductId(); // : ProductEventUid
            $ProductDetailByUidResult->getProductMain(); // : ProductUid
            $ProductDetailByUidResult->getProductName(); // : null|string
            $ProductDetailByUidResult->getProductUrl(); // : null|string
            $ProductDetailByUidResult->getProductArticle(); // : null|string
            $ProductDetailByUidResult->getCategoryName(); // : null|string
            $ProductDetailByUidResult->getCategoryUrl(); // : null|string
            $ProductDetailByUidResult->getCategorySectionField(); // : null|string
            $ProductDetailByUidResult->getProductOfferUid(); // : ProductOfferUid|null
            $ProductDetailByUidResult->getProductOfferValue(); // : null|string
            $ProductDetailByUidResult->getProductOfferPostfix(); // : null|string
            $ProductDetailByUidResult->getProductVariationUid(); // : ProductVariationUid|null
            $ProductDetailByUidResult->getProductVariationValue(); // : null|string
            $ProductDetailByUidResult->getProductVariationPostfix(); // : null|string
            $ProductDetailByUidResult->getProductModificationUid(); // : ProductModificationUid|null
            $ProductDetailByUidResult->getProductModificationValue(); // : null|string
            $ProductDetailByUidResult->getProductModificationPostfix(); // : null|string
            $ProductDetailByUidResult->getProductModificationName(); // : null|string
            $ProductDetailByUidResult->getProductVariationName(); // : null|string
            $ProductDetailByUidResult->getProductOfferName(); // : null|string
            $ProductDetailByUidResult->getProductModificationReference(); // ; // : null|string
            $ProductDetailByUidResult->getProductVariationReference(); // : null | string
            $ProductDetailByUidResult->getProductOfferReference(); // : null|string
            $ProductDetailByUidResult->getProductImage(); // : null|string
            $ProductDetailByUidResult->getProductImageExt(); // : null|string
            $ProductDetailByUidResult->isProductImageCdn(); // : bool
            $ProductDetailByUidResult->getProductTotal(); // : int

            $ProductDetailByUidResult->getProductOfferNamePostfix(); // : null|string
            $ProductDetailByUidResult->getProductVariationNamePostfix(); // : null|string
            $ProductDetailByUidResult->getProductModificationNamePostfix(); // : null|string
            $ProductDetailByUidResult->isActive(); // : bool
            $ProductDetailByUidResult->getActiveFrom(); // : DateTimeImmutable|null
            $ProductDetailByUidResult->getActiveTo(); // : DateTimeImmutable|null
            $ProductDetailByUidResult->getProductPreview(); // : null|string
            $ProductDetailByUidResult->getProductDescription(); // : null|string
            $ProductDetailByUidResult->getProductBarcode(); // : null|string
            $ProductDetailByUidResult->getProductPrice(); // : Money|false
            $ProductDetailByUidResult->getProductOldPrice(); // : Money|false
            $ProductDetailByUidResult->getProductCurrency(); // : Currency
            $ProductDetailByUidResult->getProductQuantity(); // : int|null
        }

        self::assertTrue(true);

    }


}