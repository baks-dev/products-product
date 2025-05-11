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

namespace BaksDev\Products\Product\Controller\Public;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Products\Category\Repository\AllCategory\AllCategoryInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterForm;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Products\Product\Repository\Cards\ProductCatalog\ProductCatalogInterface;
use BaksDev\Products\Product\Repository\LiederCategory\ProductLiederInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CatalogController extends AbstractController
{
    #[Route('/catalog/{page<\d+>}', name: 'public.catalog.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ProductCatalogInterface $catalogProducts,
        ProductLiederInterface $productsLeader,
        AllCategoryInterface $allCategoryRec,
        FormFactoryInterface $formFactory,
        int $page = 0,
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

        $productFilterForm = $this->createForm(ProductCategoryFilterForm::class, $productCategoryFilterDTO);
        $productFilterForm->handleRequest($request);

        /** Свойства продукции, участвующие в фильтрации */
        $propertyFields = null;
        if($productFilterForm->isSubmitted() && $productFilterForm->isValid())
        {
            foreach($productFilterForm->all() as $item)
            {
                if($item instanceof Form && !empty($item->getViewData()))
                {
                    if($item->getConfig()->getMapped())
                    {
                        continue;
                    }

                    $propertyFields[$item->getName()] = $item->getNormData();
                }
            }
        }

        /** Список только дочерних категорий */
        $childrenCategories = $allCategoryRec->getOnlyChildren();

        $products = null;
        $bestOffers = null;
        $filters = null;

        foreach($childrenCategories as $category)
        {
            /** Продукция */
            $products[$category['id']] = $catalogProducts
                ->forCategory($category['id'])
                ->maxResult(4)
                ->property($propertyFields)
                ->filter($productCategoryFilterDTO)
                ->toArray();

            /** Лучшие предложения */
            $bestOffers[$category['id']] = $productsLeader
                ->forCategory($category['id'])
                ->maxResult(10)
                ->find();

            /** Фильтр для продукции каждой категории*/
            $filter = new ProductFilterDTO();
            $filter
                ->categoryInvisible()
                ->setCategory($category['id']);

            $filterForm = $formFactory->createNamed(
                $category['id'],
                ProductFilterForm::class, $filter, [
                'action' => $this->generateUrl('products-product:public.catalog.category',
                    ['category' => $category['category_url']]
                ),
                'attr' => ['class' => 'product_filter_form w-100']
            ]);

            $filterForm->handleRequest($request);
            $filters[$category['id']] = $filterForm->createView();
        }

        return $this->render(
            [
                'categories' => $childrenCategories,
                'products' => $products,
                'bestOffers' => $bestOffers,
                'filters' => $filters,
            ]
        );
    }
}
