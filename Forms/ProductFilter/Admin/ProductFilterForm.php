<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Products\Product\Forms\ProductFilter\Admin;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Repository\ModificationFieldsCategoryChoice\ModificationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\OfferFieldsCategoryChoice\OfferFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\VariationFieldsCategoryChoice\VariationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductFilterForm extends AbstractType
{

    private RequestStack $request;


    private CategoryChoiceInterface $categoryChoice;
    private OfferFieldsCategoryChoiceInterface $offerChoice;
    private VariationFieldsCategoryChoiceInterface $variationChoice;
    private ModificationFieldsCategoryChoiceInterface $modificationChoice;
    private FieldsChoice $choice;

    public function __construct(
        RequestStack $request,

        CategoryChoiceInterface $categoryChoice,
        OfferFieldsCategoryChoiceInterface $offerChoice,
        VariationFieldsCategoryChoiceInterface $variationChoice,
        ModificationFieldsCategoryChoiceInterface $modificationChoice,
        FieldsChoice $choice,

    )
    {
        $this->request = $request;
        $this->categoryChoice = $categoryChoice;
        $this->offerChoice = $offerChoice;
        $this->variationChoice = $variationChoice;
        $this->modificationChoice = $modificationChoice;
        $this->choice = $choice;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /**
         * Категория
         */

        $builder->add('category', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event): void {
            /** @var ProductFilterDTO $data */
            $data = $event->getData();

            /** Если жестко не указана категория - выводим список для выбора */
            if($data && !$data->getCategory(true))
            {
                $builder = $event->getForm();


                $builder->add('category', ChoiceType::class, [
                    'choices' => $this->categoryChoice->getCategoryCollection(),
                    'choice_value' => function(?ProductCategoryUid $category) {
                        return $category?->getValue();
                    },
                    'choice_label' => function(ProductCategoryUid $category) {
                        return $category->getOptions();
                    },
                    'label' => false,
                    'required' => false,
                ]);
            }

        });


        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {
                /** @var ProductFilterDTO $data */
                $data = $event->getData();

                $this->request->getSession()->remove(ProductFilterDTO::category);

                $this->request->getSession()->set(ProductFilterDTO::category, $data->getCategory());
                $this->request->getSession()->set(ProductFilterDTO::offer, $data->getOffer());
                $this->request->getSession()->set(ProductFilterDTO::variation, $data->getVariation());
                $this->request->getSession()->set(ProductFilterDTO::modification, $data->getModification());

            }
        );


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event): void {
                // this would be your entity, i.e. SportMeetup

                /** @var ProductFilterDTO $data */

                $data = $event->getData();
                $builder = $event->getForm();

                $Category = $data->getCategory();

                if(isset($this->request->getMainRequest()?->get($builder->getName())['category']))
                {
                    $Category = !empty($this->request->getMainRequest()?->get($builder->getName())['category']) ?
                        new ProductCategoryUid($this->request->getMainRequest()?->get($builder->getName())['category']) : null;

                }

                if($Category)
                {
                    /** Торговое предложение раздела */

                    $offerField = $this->offerChoice->getOfferFieldCollection($Category);

                    if($offerField)
                    {
                        $inputOffer = $this->choice->getChoice($offerField->getField());

                        if($inputOffer)
                        {
                            $builder->add('offer',

                                $inputOffer->form(),
                                [
                                    'label' => $offerField->getOption(),
                                    //'mapped' => false,
                                    'priority' => 200,
                                    'required' => false,

                                    //'block_name' => $field['type'],
                                    //'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
                                ]
                            );


                            /** Множественные варианты торгового предложения */

                            $variationField = $this->variationChoice->getVariationFieldType($offerField);

                            if($variationField)
                            {

                                $inputVariation = $this->choice->getChoice($variationField->getField());

                                if($inputVariation)
                                {
                                    $builder->add('variation',
                                        $inputVariation->form(),
                                        [
                                            'label' => $variationField->getOption(),
                                            //'mapped' => false,
                                            'priority' => 199,
                                            'required' => false,

                                            //'block_name' => $field['type'],
                                            //'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
                                        ]
                                    );

                                    /** Модификации множественных вариантов торгового предложения */

                                    $modificationField = $this->modificationChoice->getModificationFieldType($variationField);


                                    if($modificationField)
                                    {
                                        $inputModification = $this->choice->getChoice($modificationField->getField());

                                        if($inputModification)
                                        {
                                            $builder->add('modification',
                                                $inputModification->form(),
                                                [
                                                    'label' => $modificationField->getOption(),
                                                    //'mapped' => false,
                                                    'priority' => 198,
                                                    'required' => false,

                                                    //'block_name' => $field['type'],
                                                    //'data' => isset($session[$field['type']]) ? $session[$field['type']] : null,
                                                ]
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    $data->setOffer(null);
                    $data->setVariation(null);
                    $data->setModification(null);
                }
            }
        );






    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults
        (
            [
                'data_class' => ProductFilterDTO::class,
                'validation_groups' => false,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ]
        );
    }

}
