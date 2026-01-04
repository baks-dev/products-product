<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Products\Product\Messenger\Cost;


use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Products\Product\Entity\Offers\Cost\ProductOfferCost;
use BaksDev\Products\Product\Entity\Offers\Variation\Cost\ProductVariationCost;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Cost\ProductModificationCost;
use BaksDev\Products\Product\Repository\Search\AllProducts\SearchAllProductsRepository;
use BaksDev\Products\Product\Repository\Search\AllProducts\SearchAllResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\UseCase\Admin\Cost\Offer\UpdateOfferCostDTO;
use BaksDev\Products\Product\UseCase\Admin\Cost\Offer\UpdateOfferCostHandler;
use BaksDev\Products\Product\UseCase\Admin\Cost\Offer\Variation\Modification\UpdateModificationCostDTO;
use BaksDev\Products\Product\UseCase\Admin\Cost\Offer\Variation\Modification\UpdateModificationCostHandler;
use BaksDev\Products\Product\UseCase\Admin\Cost\Offer\Variation\UpdateVariationCostDTO;
use BaksDev\Products\Product\UseCase\Admin\Cost\Offer\Variation\UpdateVariationCostHandler;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Search\Index\SearchIndexInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class ProductCostDispatcher
{
    public function __construct(
        #[Target('productsProductLogger')] private LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')] private string $project_dir,
        private SearchAllProductsRepository $SearchAllProductsRepository,
        private UpdateModificationCostHandler $UpdateModificationCostHandler,
        private UpdateVariationCostHandler $UpdateVariationCostHandler,
        private UpdateOfferCostHandler $UpdateOfferCostHandler,
    ) {}

    public function __invoke(ProductCostMessage $message): void
    {

        /** Получаем файл */

        foreach($message->getFiles() as $file)
        {
            $upload = null;
            $upload[] = $this->project_dir;
            $upload[] = 'public';
            $upload[] = 'upload';
            $upload[] = 'products-product';
            $upload[] = 'cost';
            $upload[] = $file;


            // Директория загрузки файла
            $realPath = implode(DIRECTORY_SEPARATOR, $upload);


            /**
             * Загружаем файл.
             * IOFactory автоматически определит тип файла (XLSX, XLS, CSV и т.д.)
             * и выберет соответствующий ридер.
             */
            $spreadsheet = IOFactory::load($realPath);


            // Получаем итератор для всех листов в книге
            foreach($spreadsheet->getAllSheets() as $worksheet)
            {
                // Итерируемся по всем строкам листа с помощью итератора
                foreach($worksheet->getRowIterator() as $row)
                {
                    // Получаем номер текущей строки
                    $rowIndex = $row->getRowIndex();

                    // 1. Получаем первую ячейку (колонка A) c названием продукта
                    $cellProduct = $worksheet->getCell('A'.$rowIndex);
                    $valueProduct = $cellProduct->getValue(); // или getCalculatedValue() для формул

                    if(empty($valueProduct))
                    {
                        continue;
                    }

                    // Удаляем всё, кроме букв, цифр и пробелов
                    $valueProduct = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $valueProduct);

                    // Добавляем пробел между 60R15, приводим к формату 60 R15
                    $valueProduct = preg_replace('/(\d+)(R\d+)/', '$1 $2', $valueProduct);


                    /** Поиск продукта по названию */

                    $SearchDTO = new SearchDTO()
                        ->setQuery($valueProduct)
                        ->setSearchTags(['products-product']);

                    $result = $this->SearchAllProductsRepository
                        ->search($SearchDTO)
                        ->maxResult(1)
                        ->findAll();

                    if(false === $result || false === $result->valid())
                    {
                        $this->logger->critical(
                            'Продукт по названию не найден',
                            [$valueProduct, self::class.':'.__LINE__],
                        );

                        continue;
                    }

                    /** @var SearchAllResult $SearchAllResult */
                    $SearchAllResult = $result->current();


                    /** Если найден продукт  */
                    $this->logger->critical($valueProduct, [var_export($SearchAllResult, true), self::class.':'.__LINE__]);


                    // 2. Получаем вторую ячейку (колонка D) Код упаковки
                    $cellPrice = $worksheet->getCell('B'.$rowIndex);
                    $valuePrice = $cellPrice->getValue();

                    if(empty($valuePrice))
                    {
                        continue;
                    }

                    // Ищем число с возможной запятой или точкой как разделителем дробной части
                    if(false === preg_match('/[\d]+[.,]?[\d]*/', $valuePrice, $matches))
                    {
                        continue;
                    }

                    $valuePrice = str_replace(',', '.', $matches[0]);


                    /** Присваиваем себестоимость продукта */

                    /**
                     * Если найден Modification - ищем закупочную цену ModificationCost
                     */
                    if(true === ($SearchAllResult->getProductModificationUid() instanceof ProductModificationUid))
                    {
                        $UpdateModificationCostDTO = new UpdateModificationCostDTO(
                            $SearchAllResult->getProductModificationUid(),
                            new Money($valuePrice),
                            new Currency('USD'),
                        );

                        $ProductModificationCost = $this->UpdateModificationCostHandler->handle($UpdateModificationCostDTO);

                        if(false === ($ProductModificationCost instanceof ProductModificationCost))
                        {
                            $this->logger->info(
                                sprintf('%s: Обновили закупочную стоимость ModificationCost продукта => %s',
                                    $SearchAllResult->getProductArticle(),
                                    $valuePrice,
                                ),
                            );
                        }

                        continue;
                    }

                    /**
                     * Если Modification === FALSE, но передан Variation - ищем закупочную цену VariationCost
                     */
                    if(
                        false === ($SearchAllResult->getProductModificationUid() instanceof ProductModificationUid) &&
                        true === ($SearchAllResult->getProductVariationUid() instanceof ProductVariationUid)
                    )
                    {

                        $UpdateVariationCostDTO = new UpdateVariationCostDTO(
                            $SearchAllResult->getProductVariationUid(),
                            new Money($valuePrice),
                            new Currency('USD'),
                        );

                        $ProductVariationCost = $this->UpdateVariationCostHandler->handle($UpdateVariationCostDTO);

                        if(false === ($ProductVariationCost instanceof ProductVariationCost))
                        {
                            $this->logger->info(
                                sprintf('%s: Обновили закупочную стоимость VariationCost продукта => %s',
                                    $SearchAllResult->getProductArticle(),
                                    $valuePrice,
                                ),
                            );
                        }

                        continue;
                    }

                    /**
                     * Если Variation === FALSE, но передан Offer - ищем закупочную цену OfferCost
                     */
                    if(
                        false === ($SearchAllResult->getProductVariationUid() instanceof ProductVariationUid) &&
                        true === ($SearchAllResult->getProductOfferUid() instanceof ProductOfferUid)
                    )
                    {
                        $UpdateOfferCostDTO = new UpdateOfferCostDTO(
                            $SearchAllResult->getProductOfferUid(),
                            new Money($valuePrice),
                            new Currency('USD'),
                        );

                        $ProductOfferCost = $this->UpdateOfferCostHandler->handle($UpdateOfferCostDTO);

                        if(false === ($ProductOfferCost instanceof ProductOfferCost))
                        {
                            $this->logger->info(
                                sprintf('%s: Обновили закупочную стоимость OfferCost продукта => %s',
                                    $SearchAllResult->getProductArticle(),
                                    $valuePrice,
                                ),
                            );
                        }

                        continue;
                    }

                    /**
                     * Если Offer === FALSE, но передан Event - ищем количественный учет ProductPrice
                     */

                    if(
                        false === ($SearchAllResult->getProductOfferUid() instanceof ProductOfferUid) &&
                        true === ($SearchAllResult->getProductEvent() instanceof ProductEventUid)
                    )
                    {
                        continue;
                    }

                }

            }

        }


    }
}
