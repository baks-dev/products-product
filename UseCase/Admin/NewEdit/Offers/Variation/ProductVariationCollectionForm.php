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
 *
 */

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation;

use BaksDev\Core\Services\Reference\ReferenceChoice;
use BaksDev\Products\Category\Repository\CategoryVariationForm\CategoryVariationFormDTO;
use BaksDev\Products\Category\Type\Offers\Variation\CategoryProductVariationUid;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Cost\ProductVariationCostForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Opt\ProductVariationOptForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Price\ProductOfferVariationPriceForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductVariationCollectionForm extends AbstractType
{
    private ReferenceChoice $reference;


    public function __construct(ReferenceChoice $reference)
    {
        $this->reference = $reference;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $variation = $options['variation'];
        $modification = $options['modification'];

        $builder->add('categoryVariation', HiddenType::class);


        $builder->get('categoryVariation')->addModelTransformer(
            new CallbackTransformer(
                function($categoryVariation) {
                    return $categoryVariation instanceof CategoryProductVariationUid ? $categoryVariation->getValue() : $categoryVariation;
                },
                function($categoryVariation) {
                    return new CategoryProductVariationUid($categoryVariation);
                }
            )
        );

        $builder->add('const', HiddenType::class);

        $builder->get('const')->addModelTransformer(
            new CallbackTransformer(
                function($const) {
                    return $const instanceof ProductVariationConst ? $const->getValue() : $const;
                },
                function($const) {
                    return new ProductVariationConst($const);
                }
            )
        );


        $builder->add('article', TextType::class);

        $builder->add('postfix', TextType::class);

        $builder->add('value', TextType::class, ['label' => $variation->name, 'attr' => ['class' => 'mb-3']]);

        $builder->add('price', ProductOfferVariationPriceForm::class, ['label' => false]);

        $builder->add('cost', ProductVariationCostForm::class, ['label' => false]);

        $builder->add('opt', ProductVariationOptForm::class, ['label' => false]);

        /**
         * Штрихкод - для конкретной вложенности
         */
        if($variation instanceof CategoryVariationFormDTO && null === $modification)
        {
            $builder->add('barcode', TextType::class, ['required' => true]);

            $builder->get('barcode')->addModelTransformer(
                new CallbackTransformer(
                    function(?ProductBarcode $barcode) {
                        return $barcode instanceof ProductBarcode ? $barcode : new ProductBarcode(ProductBarcode::generate());
                    },
                    function(?string $barcode) {
                        return null === $barcode ? new ProductBarcode(ProductBarcode::generate()) : new ProductBarcode($barcode);
                    }
                )
            );
        }

        /** Торговые предложения */
        $builder->add('image', CollectionType::class, [
            'entry_type' => Image\ProductOfferVariationImageCollectionForm::class,
            'entry_options' => [
                'label' => false,
            ],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__variation_image__',
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($variation) {
                $data = $event->getData();
                $form = $event->getForm();

                if($data)
                {

                    /* Получаем данные торговые предложения категории */
                    //$offerCat = $data->getOffer();

                    /* Если ТП - справочник - перобразуем поле ChoiceType   */
                    if($variation->reference)
                    {
                        $reference = $this->reference->getChoice($variation->reference);

                        if($reference)
                        {

                            $form->add(
                                'value',
                                $reference->form(),
                                [
                                    'label' => $variation->name,
                                    'required' => false,
                                    //'mapped' => false,
                                    //'attr' => [ 'data-select' => 'select2' ],
                                ]
                            );

                        }
                    }

                    if($variation->postfix)
                    {
                        $form->add('postfix', TextType::class, ['attr' => ['placeholder' => $variation->postfixName]]);
                    }
                    else
                    {
                        $form->remove('postfix');
                    }

                    /* Удаляем количественный учет */
                    if(!$variation->quantitative)
                    {
                        $form->remove('quantity');
                    }

                    /* Удаляем артикул если запрещено */
                    if(!$variation->article)
                    {
                        $form->remove('article');
                    }


                    /* Удаляем пользовательское изображение если запрещено */
                    if(!$variation->image)
                    {
                        $form->remove('image');
                    }

                    /* Удаляем Прайс на торговое предложение, если нет прайса */
                    if(!$variation->price)
                    {
                        $form->remove('price');
                        $form->remove('cost');
                        $form->remove('opt');
                    }
                }
            }
        );


        if($modification)
        {
            /** Множественные варианты торгового предложения */
            $builder->add('modification', CollectionType::class, [
                'entry_type' => Modification\ProductModificationCollectionForm::class,
                'entry_options' => [
                    'label' => false,
                    'modification' => $modification,
                ],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => '__variation_modification__',
            ]);
        }

        $builder->add(
            'DeleteVariation',
            ButtonType::class,
            [
                'label_html' => true,
                'attr' =>
                    ['class' => 'btn btn-sm btn-icon btn-light-danger del-item-variation'],
            ]
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductVariationCollectionDTO::class,
            'variation' => null,
            'modification' => null,
            //'offers' => null,
        ]);
    }

}
