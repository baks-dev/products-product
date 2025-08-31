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

namespace BaksDev\Products\Product\Repository\Cards\ModelsOrProductsByCategory\Tests;

use BaksDev\Products\Category\Repository\AllCategory\AllCategoryInterface;
use BaksDev\Products\Product\Repository\Cards\ModelsOrProductsByCategory\ModelOrProductByCategoryResult;
use BaksDev\Products\Product\Repository\Cards\ModelsOrProductsByCategory\ModelsOrProductsByCategoryInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('products-product')]
#[When(env: 'test')]
class ModelOrProductsByCategoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var AllCategoryInterface $categoriesRepo */
        $categoriesRepo = self::getContainer()->get(AllCategoryInterface::class);
        $categories = $categoriesRepo->getOnlyChildren();

        if(empty($categories))
        {
            $this->addWarning('Не найдено ни одной категории товаров');
            return;
        }

        /** @var ModelsOrProductsByCategoryInterface $repository */
        $repository = self::getContainer()->get(ModelsOrProductsByCategoryInterface::class);

        $results = $repository
            ->category(current($categories)['id'])
            ->findAllWithPaginator('AND');

        if(empty($results->getData()))
        {
            $this->addWarning(sprintf('Не найдено ни одного продукта по категории %s | %s',
                current($categories)['id'],
                current($categories)['category_name']
            ));

            return;
        }

        /** @var ModelOrProductByCategoryResult $result */
        foreach($results->getData() as $result)
        {
            self::assertInstanceOf(ModelOrProductByCategoryResult::class, $result);

            self::assertInstanceOf(ProductUid::class, $result->getProductId());
            self::assertInstanceOf(ProductEventUid::class, $result->getProductEvent());

            is_string($result->getProductName()) ?: self::assertNull($result->getProductName());

            is_string($result->getProductUrl()) ?: self::assertNull($result->getProductUrl());

            is_int($result->getProductSort()) ?: self::assertNull($result->getProductSort());

            is_string($result->getProductActiveFrom()) ?: self::assertNull($result->getProductActiveFrom());

            is_bool($result->getCategoryOfferCard()) ?: self::assertNull($result->getCategoryOfferCard());
            is_string($result->getProductOfferReference()) ?: self::assertNull($result->getProductOfferReference());
            is_string($result->getProductOfferValue()) ?: self::assertNull($result->getProductOfferValue());
            self::assertIsString($result->getOfferAgg());

            is_bool($result->getCategoryVariationCard()) ?: self::assertNull($result->getCategoryVariationCard());
            is_string($result->getProductVariationReference()) ?: self::assertNull($result->getProductVariationReference());
            is_string($result->getProductVariationValue()) ?: self::assertNull($result->getProductVariationValue());
            self::assertIsString($result->getVariationAgg());

            is_bool($result->getCategoryModificationCard()) ?: self::assertNull($result->getCategoryModificationCard());
            is_string($result->getProductModificationReference()) ?: self::assertNull($result->getProductModificationReference());
            is_string($result->getProductModificationValue()) ?: self::assertNull($result->getProductModificationValue());
            self::assertIsString($result->getModificationAgg());

            is_array($result->getInvariable()) ?: self::assertNull($result->getInvariable());

            is_array($result->getProductRootImages()) ?: self::assertNull($result->getProductRootImages());

            self::assertIsString($result->getCategoryName());

            is_bool($result->getProductPrice()) ?
                self::assertFalse($result->getProductPrice()) :
                self::assertInstanceOf(Money::class, $result->getProductPrice());

            is_bool($result->getProductOldPrice()) ?
                self::assertFalse($result->getProductOldPrice()) :
                self::assertInstanceOf(Money::class, $result->getProductOldPrice());

            is_bool($result->getProductCurrency()) ?: self::assertInstanceOf(Currency::class, $result->getProductCurrency());

            is_array($result->getCategorySectionField()) ?: self::assertNull($result->getCategorySectionField());
            is_int($result->getProductQuantity()) ?: self::assertNull($result->getProductQuantity());

            is_string($result->getProductOfferPostfix()) ?: self::assertNull($result->getProductOfferPostfix());
            is_string($result->getProductVariationPostfix()) ?: self::assertNull($result->getProductVariationPostfix());
            is_string($result->getProductModificationPostfix()) ?: self::assertNull($result->getProductModificationPostfix());
        }
    }

}