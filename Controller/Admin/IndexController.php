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

namespace App\Module\Products\Product\Controller\Admin;

//use App\Module\Product\Repository\Product\AllProduct;
use App\Module\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use App\Module\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use App\Module\Products\Product\Repository\AllProducts\AllProductsInterface;
use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
use App\System\Controller\AbstractController;
<<<<<<< HEAD
//use App\System\Handler\Search\Command;
use App\System\Handler\Search\SearchDTO;
use App\System\Handler\Search\SearchForm;
=======
//use App\System\Form\Search\Command;
use App\System\Form\Search\SearchDTO;
use App\System\Form\Search\SearchForm;
>>>>>>> 8d75f0b (Baks Development)
use App\System\Helper\Paginator;
use App\System\Type\Locale\Locale;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('"ROLE_ADMIN" in role_names or "ROLE_PRODUCT" in role_names'))]
final class IndexController extends AbstractController
{
    #[Route('/admin/products/{page<\d+>}', name: 'admin.product.index',  methods: [
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