<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Controller\Public;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Core\Type\UidType\Uid;
use BaksDev\Products\Category\Repository\AllCategory\AllCategoryInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterForm;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Products\Product\Repository\Cards\ProductCatalog\ProductCatalogInterface;
use BaksDev\Products\Product\Repository\LiederCategory\ProductLiederInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Получает и отрисовывает только информацию о продуктах по каждой категории
 *
 * @see CatalogCategoriesController
 */
#[AsController]
final class CatalogProductsController extends AbstractController
{
    #[Route(
        path: '/catalog/products/{category}',
        name: 'public.catalog.products',
        methods: ['GET', 'POST'],
        priority: 0
    )]
    public function products(
        Request $request,
        #[ParamConverter(CategoryProductUid::class)] $category,
        ProductCatalogInterface $catalogProducts,
        ProductLiederInterface $productsLeader,
        AllCategoryInterface $allCategoryRec,
        FormFactoryInterface $formFactory,
    ): Response
    {

        /** Фильтр по offer, variation, modification со страницы каталога */
        $productCategoryFilterDTO = new ProductCategoryFilterDTO($category);
        $productFilterForm = $this
            ->createForm(ProductCategoryFilterForm::class,
                $productCategoryFilterDTO,
                ['action' => $this->generateUrl('products-product:public.catalog.index')]
            )
            ->handleRequest($request);

        /** Информация о категории по идентификатору */
        $categoryInfo = array_find($allCategoryRec->getOnlyChildren(), function(array $info) use ($category) {
            return $category->equals($info['id']);
        });

        /** Получаем значения из POST параметров по названию формы */
        $formData = $request->get($productFilterForm->getName());

        $searchText = null;

        if(false === empty($formData))
        {
            /** Получаем значения из формы, исключая:
             * - пустое значения + не uuid + токен формы
             */
            $searchValues = array_filter($formData, static function($value, $key) {
                return false === empty($value) && false === Uid::isUid($value) && $key !== '_token';
            }, ARRAY_FILTER_USE_BOTH);

            /** Конкатенируем все полученные значения из формы в поисковую строку */
            $searchText = implode(' ', $searchValues);
        }

        $SearchDTO = new SearchDTO()->setQuery($searchText);

        /** Свойства продукции, участвующие в фильтрации */
        $propertyFields = null;

        if($productFilterForm->isSubmitted())
        {
            /** Обрабатываем полученные значения из POST */
            foreach($formData as $key => $data)
            {
                /** У фильтров по свойствам ключ - uuid */
                if(true === Uid::isUid($key) && false === empty($data))
                {
                    /** 1 - булевый флаг в форме */
                    $propertyFields[$key] = $data === '1' ? true : $data;
                }
            }
        }

        /** Продукция */
        $products = $catalogProducts
            ->forCategory($category)
            ->property($propertyFields)
            ->filter($productCategoryFilterDTO)
            ->search($SearchDTO)
            ->maxResult(6)
            ->findAll();

        $bestOffers = null;
        $filterForm = null;

        /** Ищем доп информацию только если нашли продукты */
        if(false === empty($products))
        {
            /** Лучшие предложения */
            $bestOffers = $productsLeader
                ->forCategory($category)
                ->maxResult(10)
                ->find();

            /** Фильтр для продукции каждой категории*/
            $filter = new ProductFilterDTO()
                ->categoryInvisible()
                ->setCategory($category)
                ->setOffer($productCategoryFilterDTO->getOffer())
                ->setVariation($productCategoryFilterDTO->getVariation())
                ->setModification($productCategoryFilterDTO->getModification());

            $filterForm = $formFactory->createNamed(
                name: $categoryInfo['category_url'],
                type: ProductFilterForm::class,
                data: $filter,
                options: [
                    'action' => $this->generateUrl('products-product:public.catalog.category',
                        ['category' => $categoryInfo['category_url']]
                    ),
                    'attr' => ['class' => 'product_filter_form w-100']
                ])
                ->handleRequest($request);
        }

        return $this->render(
            [
                'category' => $categoryInfo,
                'products' => $products,
                'bestOffers' => $bestOffers,
                'filter' => $filterForm?->createView(),
            ]
        );
    }
}
