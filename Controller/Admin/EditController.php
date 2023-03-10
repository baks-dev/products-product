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

use App\Module\Products\Category\Repository\CategoryPropertyById\CategoryPropertyByIdInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\Security\RoleSecurity;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductHandler;
use BaksDev\Products\Product\UseCase\ProductAggregate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[RoleSecurity(['ROLE_ADMIN', 'ROLE_PRODUCT_EDIT'])]
final class EditController extends AbstractController
{
	#[Route('/admin/product/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
	public function edit(
		#[MapEntity] Entity\Event\ProductEvent $Event,
		Request $request,
		ProductHandler $handler,
		EntityManagerInterface $entityManager,
	) : Response
	{
		
		$ProductDTO = new ProductDTO();
		$Event->getDto($ProductDTO);
		
		
		$Info = $entityManager->getRepository(Entity\Info\ProductInfo::class)->findOneBy(['product' => $Event->getProduct()]);
		$Info->getDto($ProductDTO->getInfo());
		
		/* ?????????? ???????????????????? */
		$form = $this->createForm(ProductForm::class, $ProductDTO);
		$form->handleRequest($request);
		
		//dd($product);
		
		if($form->isSubmitted() && $form->isValid())
		{
			$Product = $handler->handle($ProductDTO);
			
			if($Product instanceof Entity\Product)
			{
				$this->addFlash('success', 'admin.success.update', 'admin.products.product');
				
				return $this->redirectToRoute('Product:admin.index');
			}
			
			$this->addFlash('danger', 'admin.danger.update', 'admin.products.product', $Product);
			
			return $this->redirectToReferer();
		}
		
		return $this->render(['form' => $form->createView()]);
		
	}
}