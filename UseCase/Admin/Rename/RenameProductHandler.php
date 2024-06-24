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

namespace BaksDev\Products\Product\UseCase\Admin\Rename;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\Messenger\ProductMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RenameProductHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
        private readonly MessageDispatchInterface $messageDispatch,
    ) {}


    public function handle(RenameProductDTO $command): Entity\Product|string
    {

        /**
         *  Валидация WbBarcodeDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        if(!$command->getEvent())
        {
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: Не указан идентификатор события', $uniqid), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }


        $EventRepo = $this->entityManager->getRepository(Entity\Event\ProductEvent::class)->find(
            $command->getEvent(),
        );

        if(null === $EventRepo)
        {
            $uniqid = uniqid('', false);

            $this->logger->error(sprintf(
                '%s: Событие ProductEvent не найдено (event: %s)',
                $uniqid,
                $command->getEvent()
            ), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        $EventRepo->setEntity($command);
        $EventRepo->setEntityManager($this->entityManager);
        $Event = $EventRepo->cloneEntity();
        //        $this->entityManager->clear();
        //        $this->entityManager->persist($Event);

        // Получаем продукт
        $Product = $this->entityManager->getRepository(Entity\Product::class)
            ->findOneBy(['event' => $command->getEvent()]);


        if(empty($Product))
        {
            $uniqid = uniqid('', false);

            $this->logger->error(sprintf(
                '%s: Агрегат Product не найден, либо был изменен (event: %s)',
                $uniqid,
                $command->getEvent()
            ), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        $Product->setEvent($Event); // Обновляем событие агрегата


        // Валидация события
        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }


        // Валидация агрегата
        $errors = $this->validator->validate($Product);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }


        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ProductMessage($Product->getId(), $Product->getEvent(), $command->getEvent()),
            transport: 'products-product',
        );

        return $Product;
    }
}
