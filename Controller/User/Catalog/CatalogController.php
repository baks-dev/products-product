<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Controller\User\Catalog;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Products\Category\Repository\AllCategoryByMenu\AllCategoryByMenuInterface;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Products\Product\Repository\CatalogProducts\CatalogProductsInterface;
use BaksDev\Products\Product\Repository\LiederProducts\LiederProductsInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CatalogController extends AbstractController
{
    #[Route('/catalog/{page<\d+>}', name: 'user.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CatalogProductsInterface $catalogProducts,
        LiederProductsInterface $leaderProduct,
        AllCategoryByMenuInterface $allCategory,
        FormFactoryInterface $formFactory,
        int $page = 0,
    ): Response
    {

        $categories = $allCategory->findAll();

        $tires = null;
        $bestOffers = null;

        foreach($categories as $key => $category)
        {
            $tires[$key] = $catalogProducts->find($key);
            $bestOffers[$key] = $leaderProduct->findAll($key);

            /**
             * Фильтр продукции по ТП
             */
            $filter = new ProductFilterDTO();
            $filter
                ->categoryInvisible()
                ->setCategory($key);

            $filterForm = $formFactory->createNamed(
                'form-'.$key,
                ProductFilterForm::class, $filter, [
                'action' => $this->generateUrl('products-product:user.catalog.category', ['category' => $category['category_url']]),
                'attr' => ['class' => 'product_filter_form w-100']
            ]);

            $filterForm->handleRequest($request);
            $filters[$key] = $filterForm->createView();
        }


        return $this->render(
            [
                'categories' => $categories,
                'tires' => $tires,
                'bestOffers' => $bestOffers,
                'filters' => $filters,
            ]
        );
    }
}
