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

namespace BaksDev\Products\Product\Controller\User\Catalog;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Products\Category\Entity\Info\ProductCategoryInfo;
use BaksDev\Products\Category\Repository\AllFilterFieldsByCategory\AllFilterFieldsByCategoryInterface;
use BaksDev\Products\Category\Repository\CategoryByUrl\CategoryByUrlInterface;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterForm;
use BaksDev\Products\Product\Repository\AllProductsByCategory\AllProductsByCategoryInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

final class VariationController extends AbstractController
{
	#[Route('/catalog/{url}/variation/{offer}/{variation}/{page<\d+>}', name: 'user.catalog.variation')]
	public function index(
		Request $request,
		//#[MapEntity(mapping: ['url' => 'url', 'active' => true])] ProductCategoryInfo $info,
		AllProductsByCategoryInterface $productsByCategory,
		CategoryByUrlInterface $categoryByUrl,
		string $url,
		string $offer,
		string $variation,
		int $page = 0,
	) : Response
	{
		
		
		//dd('Каталог с ВАРИАНТОВ с модифиакторами');
		
		//dump($info->getCategory());
		//dump($productsByCategory->fetchAllProductByCategoryAssociative($info->getCategory()));
		
		$info = $categoryByUrl->fetchCategoryAssociative($url);
		
		if(!$info)
		{
			throw new RouteNotFoundException('Page Not Found');
		}
		
		$CategoryUid = new ProductCategoryUid($info['category_id']);
		
		
		//$ProductCategoryFilterDTO = new ProductCategoryFilterDTO($info->getCategory());
		$ProductCategoryFilterDTO = new ProductCategoryFilterDTO($CategoryUid);
		$ProductCategoryFilterDTO->setOffer($offer !== 'all' ? $offer : null);
		$ProductCategoryFilterDTO->setVariation($variation);
		$filterForm = $this->createForm(ProductCategoryFilterForm::class, $ProductCategoryFilterDTO, ['action' => $this->generateUrl('Product:user.catalog.category', ['url' => $url])]);
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
		$Products = $productsByCategory->fetchAllProductByCategoryAssociative($CategoryUid, $ProductCategoryFilterDTO, $property);
		
		/** Если список пуст - пробуем предложить другие варианты */
		if(!$Products->getData())
		{
			$Products = $productsByCategory->fetchAllProductByCategoryAssociative($CategoryUid, $ProductCategoryFilterDTO, $property, 'OR');
			
			if($Products->getData())
			{
				$otherProducts = true;
			}
		}
		
		
		
		return $this->render([
			'category' => $info,
			'products' => $Products,
			'filter' =>  $filterForm->createView(),
			'other' =>  $otherProducts,
			'fields' =>  $fields,
		]);
		
		//        return $this->render('home/index.html.twig', [
		//            'controller_name' => 'HomeController',
		//        ]);
	}
	
}
