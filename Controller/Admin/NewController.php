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

use App\Module\Products\Category\Type\Id\CategoryUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Event\ProductEventUidConverter;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductForm;
use BaksDev\Products\Product\UseCase\ProductAggregate;
use BaksDev\Core\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use BaksDev\Products\Product\Entity;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[ReleSecurity(['ROLE_ADMIN', 'ROLE_PRODUCT_NEW'])]
final class NewController extends AbstractController
{
    #[Route('/admin/product/new/{id}', name: 'admin.newedit.new', defaults: ["id" => null], methods: ['GET', 'POST'])]
    //#[ParamConverter('id', class: ProductEventUid::class, converter: ProductEventUid::TYPE)]
    public function new(
      Request $request,
      ProductAggregate $handler,
      ?ProductEventUid $id,
      EntityManagerInterface $entityManager,
      //TranslatorInterface $translator,
      //Handler $handler,
      //EntityManagerInterface $em,
    ) : Response
    {
        
        $Event = $entityManager->getRepository(Entity\Event\ProductEvent::class)->find($id);
        $product = new ProductDTO();

        if($Event)
        {
            $Event->getDto($product);
            $product->setId(new ProductEventUid());
        }
        
        
        
        
        
//        $cat = null;
//
//        if($request->get('category'))
//        {
//            $cat = $em->getRepository(Category::class)->find(new CategoryUid($request->get('category')));
//        }
        
 //       $eventProduct = new Event();
 //       if($cat) { $eventProduct->getCategory()[0]->setCategory($cat->getId()); /* Присваиваем категорию */ }
    
        //$category = new \App\Module\Product\Entity\Product\Category($eventProduct);
        
        
        //$category->setCategory($cat->getId());
        //$eventProduct->addCategory($category);
    
        

        if($request->get('category'))
        {
            $CategoryCollectionDTO = new CategoryCollectionDTO();
            $CategoryCollectionDTO->rootCategory();
            $CategoryCollectionDTO->setCategory(new CategoryUid($request->get('category')));
            $product->addCategory($CategoryCollectionDTO);
        }

        /* Форма добавления */
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);
    
        
        if($form->isSubmitted() && $form->isValid())
        {
            $handle = $handler->handle($product);
    
            if($handle)
            {
                $this->addFlash('success', 'admin.new.success', 'products.product');
                return $this->redirectToRoute('Product:admin.index');
            }
        }
        
        return $this->render(['form' => $form->createView()]);
        
    }
}