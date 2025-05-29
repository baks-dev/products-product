<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Forms\ProductsQuantityForm;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Repository\ModificationFieldsCategoryChoice\ModificationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\OfferFieldsCategoryChoice\OfferFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\VariationFieldsCategoryChoice\VariationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductsQuantityForm extends AbstractType
{
    public function __construct(
        private readonly CategoryChoiceInterface $categoryChoice,
        private readonly OfferFieldsCategoryChoiceInterface $offerChoice,
        private readonly VariationFieldsCategoryChoiceInterface $variationChoice,
        private readonly ModificationFieldsCategoryChoiceInterface $modificationChoice,
        private readonly FieldsChoice $choice,

    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * Категория
         */

        $builder->add('category', ChoiceType::class, [
            'choices' => $this->categoryChoice->findAll(),
            'choice_value' => function(?CategoryProductUid $category) {
                return $category?->getValue();
            },
            'choice_label' => function(CategoryProductUid $category) {
                return (is_int($category->getAttr()) ? str_repeat(' - ', $category->getAttr() - 1) : '').$category->getOptions();
            },
            'label' => false,
            'required' => true,
        ]);

        $builder->add(
            'offer',
            HiddenType::class,
        );

        /**
         * Множественный вариант торгового предложения
         */

        $builder->add(
            'variation',
            HiddenType::class,
        );

        /**
         * Модификация множественного варианта торгового предложения
         */

        $builder->add(
            'modification',
            HiddenType::class,
        );

        /**
         * Событие на изменение
         */

        $builder->get('variation')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {

                $parent = $event->getForm()->getParent();

                if(!$parent)
                {
                    return;
                }

                $category = $parent->get('category')->getData();

                if(false === empty($category))
                {
                    $this->formOfferModifier($event->getForm()->getParent(), $category);
                }
            },
        );

        // Количество
        $builder->add('quantity', IntegerType::class, ['label' => 'Количество', 'required' => true]);

        // Сохранить
        $builder->add(
            'submit',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    private function formOfferModifier(FormInterface $form, ?CategoryProductUid $category): void
    {
        if(null === $category)
        {
            return;
        }

        /** Торговое предложение раздела */
        $offerField = $this->offerChoice
            ->category($category)
            ->findAllCategoryProductOffers();

        if($offerField)
        {
            $inputOffer = $this->choice->getChoice($offerField->getField());

            if($inputOffer)
            {
                $form->add(
                    'offer',
                    method_exists($inputOffer, 'formFilterExists') ? $inputOffer->formFilterExists() : $inputOffer->form(),
                    [
                        'label' => $offerField->getOption(),
                        'priority' => 200,
                        'required' => true,
                    ]
                );

                /** Множественные варианты торгового предложения */
                $variationField = $this->variationChoice
                    ->offer($offerField)
                    ->findCategoryProductVariation();

                if($variationField)
                {
                    $inputVariation = $this->choice->getChoice($variationField->getField());
                    if($inputVariation)
                    {
                        $form->add(
                            'variation',
                            method_exists($inputVariation, 'formFilterExists') ? $inputVariation->formFilterExists() : $inputVariation->form(),
                            [
                                'label' => $variationField->getOption(),
                                'priority' => 199,
                                'required' => true,
                            ]
                        );
                        /** Модификации множественных вариантов торгового предложения */
                        $modificationField = $this->modificationChoice
                            ->variation($variationField)
                            ->findAllModification();

                        if($modificationField)
                        {
                            $inputModification = $this->choice->getChoice($modificationField->getField());
                            if($inputModification)
                            {
                                $form->add(
                                    'modification',
                                    method_exists($inputModification, 'formFilterExists') ? $inputModification->formFilterExists() : $inputModification->form(),
                                    [
                                        'label' => $modificationField->getOption(),
                                        'priority' => 198,
                                        'required' => true,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductsQuantityDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ]
        );
    }
}