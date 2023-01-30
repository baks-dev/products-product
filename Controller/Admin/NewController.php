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

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\Security\RoleSecurity;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//#[RoleSecurity(['ROLE_ADMIN', 'ROLE_PRODUCT_NEW'])]
final class NewController extends AbstractController
{
	#[Route('/admin/product/new/{id}', name: 'admin.newedit.new', defaults: ["id" => null], methods: ['GET', 'POST'])]
	//#[ParamConverter('id', class: ProductEventUid::class, converter: ProductEventUid::TYPE)]
	public function new(
		Request $request,
		EntityManagerInterface $entityManager,
		//ProductAggregate $handler,
		?ProductEventUid $id = null,
		
		//TranslatorInterface $translator,
		//Handler $handler,
		//EntityManagerInterface $em,
	) : Response
	{
		
		$product = new ProductDTO();
		
		if($id)
		{
			$Event = $entityManager->getRepository(Entity\Event\ProductEvent::class)->find($id);
			
			if($Event)
			{
				$Event->getDto($product);
				$product->setId(new ProductEventUid());
			}
		}
		
		if($request->get('category'))
		{
			$CategoryCollectionDTO = new CategoryCollectionDTO();
			$CategoryCollectionDTO->rootCategory();
			$CategoryCollectionDTO->setCategory(new ProductCategoryUid($request->get('category')));
			$product->addCategory($CategoryCollectionDTO);
		}
		
		/* Форма добавления */
		$form = $this->createForm(ProductForm::class, $product);
		$form->handleRequest($request);
		
		/*if($form->isSubmitted() && $form->isValid())
		{
			$handle = $handler->handle($product);
			
			if($handle)
			{
				$this->addFlash('success', 'admin.new.success', 'products.product');
				return $this->redirectToRoute('Product:admin.index');
			}
		}*/
		
		//dd($product);
		
		return $this->render(['form' => $form->createView()]);
		
	}
	
}