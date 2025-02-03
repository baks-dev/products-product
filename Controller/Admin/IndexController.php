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

namespace BaksDev\Products\Product\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Products\Product\Repository\AllProducts\AllProductsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity(['ROLE_PRODUCT', 'ROLE_PRODUCT_INDEX'])]
final class IndexController extends AbstractController
{
    #[Route('/admin/products/{page<\d+>}', name: 'admin.index', methods: ['GET', 'POST',])]
    public function index(
        Request $request,
        AllProductsInterface $getAllProduct,
        int $page = 0,
    ): Response
    {

        /**
         * Фильтр продукции по ТП
         */
        $filter = new ProductFilterDTO();

        $filterForm = $this
            ->createForm(
                type: ProductFilterForm::class,
                data: $filter,
                options: ['action' => $this->generateUrl('products-product:admin.index'),]
            )
            ->handleRequest($request);


        // Поиск
        $search = new SearchDTO();

        $searchForm = $this
            ->createForm(
                type: SearchForm::class,
                data: $search,
                options: ['action' => $this->generateUrl('products-product:admin.index'),]
            )
            ->handleRequest($request);


        $isFilter = (bool) ($search->getQuery() || $filter->getOffer() || $filter->getVariation() || $filter->getModification());


        $getAllProduct
            ->search($search)
            ->filter($filter);

        if($isFilter)
        {
            // Получаем список торговых предложений
            $query = $getAllProduct->getAllProductsOffers($this->getProfileUid());
        }
        else
        {
            // Получаем список продукции
            $query = $getAllProduct->getAllProducts($this->getProfileUid());
        }


        return $this->render(
            [
                'query' => $query,
                'search' => $searchForm->createView(),
                'filter' => $filterForm->createView(),
                //'profile' => $profileForm->createView(),
            ],
            file: $isFilter ? 'offers.html.twig' : 'product.html.twig'
        );
    }
}
