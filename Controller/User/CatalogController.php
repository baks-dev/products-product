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

namespace BaksDev\Products\Product\Controller\User;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Products\Category\Repository\AllCategoryByMenu\AllCategoryByMenuInterface;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Products\Product\Repository\ProductCatalog\ProductCatalogInterface;
use BaksDev\Products\Product\Repository\ProductLieder\ProductLiederInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CatalogController extends AbstractController
{
    #[Route('/catalog/{page<\d+>}', name: 'user.catalog.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ProductCatalogInterface $catalogProducts,
        ProductLiederInterface $productsLeader,
        AllCategoryByMenuInterface $allCategory,
        FormFactoryInterface $formFactory,
        int $page = 0,
    ): Response
    {
        $categories = $allCategory->findAll();

        $products = null;
        $bestOffers = null;
        $filters = null;

        foreach($categories as $categoryUid => $category)
        {
            $products[$categoryUid] = $catalogProducts
                ->forCategory($categoryUid)
                ->maxResult(4)
                ->find();

            $bestOffers[$categoryUid] = $productsLeader
                ->forCategory($categoryUid)
                ->maxResult(10)
                ->find();

            /**
             * Фильтр продукции по ТП
             */
            $filter = new ProductFilterDTO();
            $filter
                ->categoryInvisible()
                ->setCategory($categoryUid);

            $filterForm = $formFactory->createNamed(
                $categoryUid,
                ProductFilterForm::class, $filter, [
                'action' => $this->generateUrl('products-product:user.catalog.category',
                    ['category' => $category['category_url']]
                ),
                'attr' => ['class' => 'product_filter_form']
            ]);

            $filterForm->handleRequest($request);
            $filters[$categoryUid] = $filterForm->createView();
        }

        return $this->render(
            [
                'categories' => $categories,
                'products' => $products,
                'bestOffers' => $bestOffers,
                'filters' => $filters,
            ]
        );
    }
}
