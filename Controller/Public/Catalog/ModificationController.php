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

namespace BaksDev\Products\Product\Controller\Public\Catalog;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Products\Category\Repository\CategoryByUrl\CategoryByUrlInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterForm;
use BaksDev\Products\Product\Repository\AllProductsByCategory\AllProductsByCategoryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[AsController]
final class ModificationController extends AbstractController
{
    /** @depricate */
    #[Route('/catalog/{category}/modification/{offer}/{variation}/{modification}/{page<\d+>}', name: 'public.catalog.modification')]
    public function index(
        Request $request,
        AllProductsByCategoryInterface $productsByCategory,
        CategoryByUrlInterface $categoryByUrl,
        string $category,
        string $offer = 'all',
        string $variation = 'all',
        string $modification = 'all',
        int $page = 0,
    ): Response
    {

        $info = $categoryByUrl->findByUrl($category);

        if(!$info)
        {
            throw new RouteNotFoundException('Page Not Found');
        }

        $CategoryUid = new CategoryProductUid($info['category_id']);

        $ProductCategoryFilterDTO = new ProductCategoryFilterDTO($CategoryUid);
        $ProductCategoryFilterDTO->setOffer($offer !== 'all' ? $offer : null);
        $ProductCategoryFilterDTO->setVariation($variation !== 'all' ? $variation : null);
        $ProductCategoryFilterDTO->setModification($modification !== 'all' ? $modification : null);

        $filterForm = $this->createForm(
            ProductCategoryFilterForm::class,
            $ProductCategoryFilterDTO,
            ['action' => $this->generateUrl('products-product:public.catalog.category', ['category' => $category])]
        );
        $filterForm->handleRequest($request);

        $property = null;
        $fields = null;
        foreach($filterForm->all() as $item)
        {
            if($item instanceof Form && !empty($item->getViewData()))
            {

                if($item->getConfig()->getMapped())
                {
                    continue;
                }

                $property[$item->getName()] = $item->getNormData();

                $fields[] = [
                    'field_name' => $item->getConfig()->getOption('label'),
                    'field_value' => $item->getNormData(),
                    'field_type' => $item->getConfig()->getOption('block_name'),
                ];
            }
        }


        $otherProducts = false;
        $Products = $productsByCategory
            ->filter($ProductCategoryFilterDTO)
            ->property($property)
            ->fetchAllProductByCategoryAssociative($CategoryUid, 'AND');

        /** Если список пуст - пробуем предложить другие варианты */
        if(!$Products->getData())
        {
            $Products = $productsByCategory
                ->filter($ProductCategoryFilterDTO)
                ->property($property)
                ->fetchAllProductByCategoryAssociative($CategoryUid, 'OR');

            if($Products->getData())
            {
                $otherProducts = true;
            }
        }

        // Поиск по всему сайту
        $allSearch = new SearchDTO();
        $allSearchForm = $this->createForm(SearchForm::class, $allSearch, [
            'action' => $this->generateUrl('search:public.search'),
        ]);

        return $this->render([
            'category' => $info,
            'products' => $Products,
            'filter' => $filterForm->createView(),
            'other' => $otherProducts,
            'fields' => $fields,
            'all_search' => $allSearchForm->createView(),
        ],
        // routingName: 'public.catalog.category'
        );

    }

}
