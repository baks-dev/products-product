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

namespace BaksDev\Products\Product\Controller\Admin;

use App\Kernel;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PRODUCT_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/product/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        #[MapEntity] ProductEvent $Event,
        Request $request,
        ProductHandler $productHandler
    ): Response
    {
        $ProductDTO = new ProductDTO();
        $ProductDTO = $Event->getDto($ProductDTO);

        if($request->get('copy'))
        {
            $ProductDTO->copy();
        }

        // Если передана категория - присваиваем для подгрузки настроект (свойства, ТП)
        if($request->get('category'))
        {
            /** @var CategoryCollectionDTO $category */
            foreach($ProductDTO->getCategory() as $category)
            {
                if($category->getRoot())
                {
                    if($request->get('category') === 'null')
                    {
                        $category->setCategory(null);
                        break;
                    }

                    $category->setCategory(new CategoryProductUid($request->get('category')));
                }
            }

            if($category->getCategory() === null && $request->get('category') !== 'null')
            {
                $category->setRoot(true);
                $category->setCategory(new CategoryProductUid($request->get('category')));
            }
        }

        // Форма добавления
        $form = $this
            ->createForm(ProductForm::class, $ProductDTO)
            ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('product'))
        {
            $this->refreshTokenForm($form);

            $handle = $productHandler->handle($ProductDTO);

            $this->addFlash(
                'admin.page.edit',
                $handle instanceof Product ? 'admin.success.edit' : 'admin.danger.edit',
                'products-product.admin',
                $handle
            );

            return $this->redirectToRoute('products-product:admin.index');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
