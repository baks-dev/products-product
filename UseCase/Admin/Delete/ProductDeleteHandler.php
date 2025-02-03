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

namespace BaksDev\Products\Product\UseCase\Admin\Delete;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Messenger\ProductMessage;

final class ProductDeleteHandler extends AbstractHandler
{
    //    public function __construct(
    //        #[Target('productsProductLogger')] private LoggerInterface $logger,
    //        private EntityManagerInterface $entityManager,
    //        private ValidatorInterface $validator,
    //        private MessageDispatchInterface $messageDispatch
    //    ) {}

    public function handle(ProductDeleteDTO $command): Product|string
    {
        $this->setCommand($command);

        $this->preEventRemove(Product::class, ProductEvent::class);

        $this->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch
            ->addClearCacheOther('products-category')
            ->dispatch(
                message: new ProductMessage($this->main->getId(), $this->event->getId(), $command->getEvent()),
                transport: 'products-product'
            );

        return $this->main;
    }
}
