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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers;

use BaksDev\Core\Services\Reference\ReferenceChoice;
use BaksDev\Products\Category\Type\Offers\Id\CategoryProductOffersUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
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

final class ProductOffersCollectionForm extends AbstractType
{
    private ReferenceChoice $reference;

    public function __construct(ReferenceChoice $reference)
    {
        $this->reference = $reference;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $offer = $options['offers'];
        $variation = $options['variation'];
        $modification = $options['modification'];

        $builder->add('categoryOffer', HiddenType::class);

        $builder->get('categoryOffer')->addModelTransformer(
            new CallbackTransformer(
                function($categoryOffer) {
                    return $categoryOffer instanceof CategoryProductOffersUid ? $categoryOffer->getValue() : $categoryOffer;
                },
                function($categoryOffer) {
                    return new CategoryProductOffersUid($categoryOffer);
                }
            )
        );


        $builder->add('const', HiddenType::class);

        $builder->get('const')->addModelTransformer(
            new CallbackTransformer(
                function($const) {
                    return $const instanceof ProductOfferConst ? $const->getValue() : $const;
                },
                function($const) {
                    return new ProductOfferConst($const);
                }
            )
        );

        $builder->add('name', TextType::class);

        $builder->add('postfix', TextType::class);

        $builder->add('article', TextType::class);

        $builder->add('value', TextType::class, ['label' => $offer?->name, 'attr' => ['class' => 'mb-3']]);

        $builder->add('price', Price\ProductOfferPriceForm::class, ['label' => false]);


        /** Торговые предложения */
        $builder->add('image', CollectionType::class, [
            'entry_type' => Image\ProductOfferImageCollectionForm::class,
            'entry_options' => [
                'label' => false,
            ],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__offer_image__',
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($offer) {
                $data = $event->getData();
                $form = $event->getForm();

                if($data)
                {

                    /* Если ТП - справочник - перобразуем поле ChoiceType   */
                    if($offer?->reference)
                    {
                        $reference = $this->reference->getChoice($offer->reference);

                        if($reference)
                        {
                            $form->add(
                                'value',
                                $reference->form(),
                                [
                                    'label' => $offer?->name,
                                    'required' => false,
                                    //'mapped' => false,
                                    //'attr' => [ 'data-select' => 'select2' ],
                                ]
                            );
                        }
                    }


                    if($offer?->postfix)
                    {
                        $form->add('postfix', TextType::class, ['attr' => ['placeholder' => $offer->postfixName]]);
                    }
                    else
                    {
                        $form->remove('postfix');
                    }

                    /* Удаляем количественный учет */
                    if(!$offer?->quantitative)
                    {
                        $form->remove('quantity');
                    }

                    /* Удаляем артикул если запрещено */
                    if(!$offer?->article)
                    {
                        $form->remove('article');
                    }


                    /* Удаляем пользовательское изображение если запрещено */
                    if(!$offer?->image)
                    {
                        $form->remove('image');
                    }

                    /* Удаляем Прайс на торговое предложение, если нет прайса */
                    if(!$offer?->price)
                    {
                        $form->remove('price');
                    }
                }


            }
        );

        if($variation)
        {
            /** Множественные варианты торгового предложения */
            $builder->add('variation', CollectionType::class, [
                'entry_type' => Variation\ProductVariationCollectionForm::class,
                'entry_options' => [
                    'label' => false,
                    'variation' => $variation,
                    'modification' => $modification,
                ],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
                'prototype_name' => '__offer_variation__',
            ]);
        }


        $builder->add('DeleteOffer', ButtonType::class, ['label_html' => true,]);

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOffersCollectionDTO::class,
            //'category_id' => null,
            //'offer_data' => null,
            'offers' => null,
            'variation' => null,
            'modification' => null,
        ]);
    }

}
