<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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
use BaksDev\Products\Product\Forms\ProductProfileFilter\ProductProfileFilterDTO;
use BaksDev\Products\Product\Forms\ProductProfileFilter\ProductProfileFilterForm;
use BaksDev\Products\Product\Forms\ProductProfileFilter\ProductProfileFilterFormAdmin;
use BaksDev\Products\Product\Repository\AllProducts\AllProductsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[RoleSecurity('ROLE_PRODUCT')]
final class IndexController extends AbstractController
{
    #[Route('/admin/products/{page<\d+>}', name: 'admin.index', methods: [
        'GET',
        'POST',
    ])]
    public function index(
        Request $request,
        AllProductsInterface $getAllProduct,
        int $page = 0,
    ): Response
    {
        // Поиск
        $search = new SearchDTO($request);
        $searchForm = $this->createForm(SearchForm::class, $search, [
            'action' => $this->generateUrl('Product:admin.index'),
        ]);
        $searchForm->handleRequest($request);


        // Фильтр
        $filter = new ProductFilterDTO($request);
        $filterForm = $this->createForm(ProductFilterForm::class, $filter, [
            'action' => $this->generateUrl('Product:admin.index'),
        ]);
        $filterForm->handleRequest($request);
        !$filterForm->isSubmitted() ?: $this->redirectToReferer();


        /**
         * Фильтр профиля пользователя
         */

        $profile = new ProductProfileFilterDTO($request, $this->getProfileUid());
        $ROLE_ADMIN = $this->isGranted('ROLE_ADMIN');

        if($ROLE_ADMIN)
        {
            $profileForm = $this->createForm(ProductProfileFilterFormAdmin::class, $profile, [
                'action' => $this->generateUrl('Product:admin.index'),
            ]);
        }
        else
        {
            $profileForm = $this->createForm(ProductProfileFilterForm::class, $profile, [
                'action' => $this->generateUrl('Product:admin.index'),
            ]);
        }

        $profileForm->handleRequest($request);
        !$profileForm->isSubmitted() ?: $this->redirectToReferer();



        // Получаем список
        $query = $getAllProduct->getAllProducts($search, $profile, $filter);

        return $this->render(
            [
                'query' => $query,
                'counter' => $getAllProduct->count(),
                'search' => $searchForm->createView(),
                'filter' => $filterForm->createView(),
                'profile' => $profileForm->createView(),
            ]
        );
    }
}
