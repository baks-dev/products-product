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

use App\Module\Products\Category\Repository\CategoryPropertyById\CategoryPropertyByIdInterface;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductForm;
use BaksDev\Products\Product\UseCase\ProductAggregate;
use BaksDev\Core\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;



#[ReleSecurity(['ROLE_ADMIN', 'ROLE_PRODUCT_EDIT'])]
final class EditController extends AbstractController
{
    #[Route('/admin/product/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
      Request $request,
      ProductAggregate $handler,
      #[MapEntity] Entity\Event\ProductEvent $Event,
      CategoryPropertyByIdInterface $categoryProperty,
      EntityManagerInterface $entityManager,
    ) : Response
    {

        $product = new ProductDTO();
        $Event->getDto($product);
    
        $Info = $entityManager->getRepository(Entity\Info\Info::class)->findOneBy(['product' => $Event->getProduct()]);
        $Info->getDto($product->getInfo());

        /* Форма добавления */
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);
  
        
        //dd($product);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $handle = $handler->handle($product);
    
            if($handle)
            {
                $this->addFlash('success', 'admin.update.success', 'products.product');
                return $this->redirectToRoute('Product:admin.index');
            }
        }
        
        return $this->render(['form' => $form->createView()]);
        
    }

//    #[Route('/zcnimskdzz/style', name: 'admin.newedit.new.css', methods: ['GET'], format: "css")]
//    public function css() : Response
//    {
//        return $this->assets(
//          [
//            '/plugins/datepicker/datepicker.min.css', // Календарь
//            '/plugins/nice-select2/nice-select2.min.css', // Select2
//           // '/css/select2.min.css', // Select2
//           // '/css/select2.min.css', // Select2
//          ]);
//    }
//
//    #[Route('/zcnimskdzz/app', name: 'admin.newedit.new.js', methods: ['GET'], format: "js")]
//    public function js() : Response
//    {
//        return $this->assets
//        (
//          [
//
//            '/plugins/semantic/semantic.min.js',
//            '/plugins/nice-select2/nice-select2.min.js', // Select2
//
//            /* Календарь */
//            '/plugins/datepicker/datepicker.min.js',
//            '/plugins/datepicker/datepicker.lang.min.js',
//            '/plugins/datepicker/init.min.js',
//
//            '/product/product.min.js',
//
//          ]);
//    }
    
}