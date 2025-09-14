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

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteDTO;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteForm;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PRODUCT_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/product/delete/{id}', name: 'admin.delete', methods: ['POST', 'GET'])]
    public function delete(
        Request $request,
        ProductDeleteHandler $productDeleteHandler,
        #[MapEntity] ProductEvent $Event
    ): Response
    {

        $ProductDeleteDTO = new ProductDeleteDTO();
        $Event->getDto($ProductDeleteDTO);

        $form = $this->createForm(ProductDeleteForm::class, $ProductDeleteDTO, [
            'action' => $this->generateUrl('products-product:admin.delete', ['id' => $ProductDeleteDTO->getEvent()]),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('delete'))
        {
            $this->refreshTokenForm($form);

            $handle = $productDeleteHandler->handle($ProductDeleteDTO);

            $this->addFlash(
                'page.delete',
                $handle instanceof Product ? 'success.delete' : 'danger.delete',
                'products-product.admin',
                $handle
            );

            return $this->redirectToRoute('products-product:admin.index');
        }

        return $this->render(
            [
                'form' => $form->createView(),
                'name' => $Event->getNameByLocale($this->getLocale()), // название согласно локали
            ]
        );
    }
}
