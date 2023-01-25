<?php
/*
 *  Copyright Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Controller\Admin;

//use App\Module\Product\Repository\Product\AllProduct;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Products\Product\Repository\AllProducts\AllProductsInterface;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Core\Controller\AbstractController;

//use BaksDev\Core\Form\Search\Command;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;


use App\System\Helper\Paginator;
use BaksDev\Core\Type\Locale\Locale;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[ReleSecurity(['ROLE_ADMIN', 'ROLE_PRODUCT'])]
final class IndexController extends AbstractController
{
    #[Route('/admin/products/{page<\d+>}', name: 'admin.index',  methods: [
      'GET',
      'POST'
    ])]
    public function index(
      Request $request,
      AllProductsInterface $getAllProduct,
      int $page = 0,
    ) : Response
    {
    

        /* Поиск */
        $search = new SearchDTO();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);
    
    
        /* Фильтр */
        $filter = new ProductFilterDTO($request);
        $filterForm = $this->createForm(ProductFilterForm::class, $filter);
        $filterForm->handleRequest($request);

        /* Получаем список */
        $stmt = $getAllProduct->get($search, $filter);
        $query = new Paginator($page, $stmt, $request);
        
        return $this->render(
          [
            'query' => $query,
            'counter' => $getAllProduct->count(),
            'search' => $searchForm->createView(),
            'filter' => $filterForm->createView(),
          ]);
    }

}