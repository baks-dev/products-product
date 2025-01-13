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

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Messenger\ProductMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductDeleteHandler
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private MessageDispatchInterface $messageDispatch;


    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        MessageDispatchInterface $messageDispatch
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->messageDispatch = $messageDispatch;
    }

    public function handle(
        ProductDeleteDTO $command
    ): Product|string
    {

        /**
         *  Валидация WbBarcodeDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);
            return $uniqid;
        }


        /** Получаем событие */
        $Event = $this->entityManager->getRepository(ProductEvent::class)
            ->find($command->getEvent());

        if($Event === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by id: %s',
                ProductEvent::class,
                $command->getEvent()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /** Получаем корень агрегата */
        $Main = $this->entityManager->getRepository(Product::class)
            ->findOneBy(['event' => $command->getEvent()]);

        if(empty($Main))
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by event: %s',
                Product::class,
                $command->getEvent()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        // Сбрасываем семантическую ссылку
        $ProductInfo = $this->entityManager->getRepository(ProductInfo::class)
            ->find($Main->getId());

        if($ProductInfo)
        {
            $ProductInfo->setEntity($command->getInfo());
        }


        /* Применяем изменения к событию */
        $Event->setEntity($command);
        $this->entityManager->persist($Event);

        /* Удаляем корень агрегата */
        $this->entityManager->remove($Main);

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch
            ->addClearCacheOther('products-category')
            ->dispatch(
            message: new ProductMessage($Main->getId(), $Main->getEvent(), $command->getEvent()),
            transport: 'products-product'
        );


        return $Main;
    }
}
