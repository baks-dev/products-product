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
use BaksDev\Products\Category\Repository\AllCategory\AllCategoryInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterForm;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Получает и отрисовывает только информацию о категориях и прелоадер для продуктов
 *
 * @note для получения продуктов по каждой категории используется
 * @see CatalogProductsController
 */
#[AsController]
final class CatalogCategoriesController extends AbstractController
{
    #[Route(
        path: '/catalog',
        name: 'public.catalog.categories',
        methods: ['GET', 'POST'],
        priority: 1
    )]
    public function categories(
        Request $request,
        AllCategoryInterface $allCategoryRec,
    ): Response
    {

        $categoryUid = null;

        /** Из сессии */
        if($sessionFromForm = $request->getSession()->get(md5(ProductFilterForm::class)))
        {
            $sessionData = base64_decode($sessionFromForm);
            $categoryId = json_decode($sessionData, true, 512, JSON_THROW_ON_ERROR);

            if(isset($categoryId['category']))
            {
                $categoryUid = new CategoryProductUid($categoryId['category']);
            }
        }

        /** Из формы */
        $post = $request->request->all();
        if(isset($post['product_category_filter_form']['category']))
        {
            $categoryUid = new CategoryProductUid($post['product_category_filter_form']['category']);
        }

        /** Фильтр с главной страницы */
        $productCategoryFilterDTO = new ProductCategoryFilterDTO($categoryUid);
        $productFilterForm = $this
            ->createForm(ProductCategoryFilterForm::class,
                $productCategoryFilterDTO,
                ['action' => $this->generateUrl('products-product:public.catalog.index')]
            )
            ->handleRequest($request);

        /** Список только дочерних категорий */
        $childrenCategories = $allCategoryRec->getOnlyChildren();

        uasort($childrenCategories, static function($a, $b) {
            return $a['sort'] <=> $b['sort'];
        });

        return $this->render(
            [
                'categories' => $childrenCategories,
                'filter_tire' => $productFilterForm->createView(),
            ]
        );
    }
}
