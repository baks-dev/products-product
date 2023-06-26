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

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\UseCase\Admin\Delete\DeleteForm;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[RoleSecurity('ROLE_PRODUCT_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/product/delete/{id}', name: 'admin.delete', methods: ['POST', 'GET'])]
    public function delete(
        Request $request,
        ProductDeleteHandler $handler,
        #[MapEntity] Entity\Event\ProductEvent $Event,
        EntityManagerInterface $entityManager,
    ): Response {
        $product = new ProductDTO();
        $Event->getDto($product);

        $Info = $entityManager->getRepository(Entity\Info\ProductInfo::class)->findOneBy(['product' => $Event->getProduct()]);
        $Info->getDto($product->getInfo());

        $form = $this->createForm(DeleteForm::class, $product, [
            'action' => $this->generateUrl('Product:admin.delete', ['id' => $product->getEvent()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('delete')) {
                $handle = $handler->handle($product);

                if ($handle) {
                    $this->addFlash('success', 'admin.delete.success', 'products.product');

                    return $this->redirectToRoute('Product:admin.index');
                }
            }

            $this->addFlash('danger', 'admin.delete.danger', 'products.product');

            return $this->redirectToRoute('Product:admin.index');
            // return $this->redirectToReferer();
        }

        return $this->render(
            [
                'form' => $form->createView(),
                'name' => $Event->getNameByLocale($this->getLocale()), // название согласно локали
            ]
        );
    }
}
