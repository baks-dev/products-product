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
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Category\Repository\AllFilterFieldsByCategory\AllFilterFieldsByCategoryInterface;
use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Repository\ModificationFieldsCategoryChoice\ModificationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\OfferFieldsCategoryChoice\OfferFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\VariationFieldsCategoryChoice\VariationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductFilterForm extends AbstractType
{
    public function __construct(
        private readonly RequestStack $request,
        private readonly CategoryChoiceInterface $categoryChoice,
        private readonly OfferFieldsCategoryChoiceInterface $offerChoice,
        private readonly VariationFieldsCategoryChoiceInterface $variationChoice,
        private readonly ModificationFieldsCategoryChoiceInterface $modificationChoice,
        private readonly AllFilterFieldsByCategoryInterface $fields,
        private readonly FieldsChoice $choice,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('all', CheckboxType::class);

        /**
         * Категория
         */

        $builder->add('category', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {

            /** @var ProductFilterDTO $data */
            $data = $event->getData();
            $builder = $event->getForm();

            if(!$data->isAllVisible())
            {
                $builder->remove('all');
            }

            /** Если жестко не указана категория - выводим список для выбора */
            if($data && !$data->getCategory(true))
            {

                $builder->add('category', ChoiceType::class, [
                    'choices' => $this->categoryChoice->findAll(),
                    'choice_value' => function (?CategoryProductUid $category) {
                        return $category?->getValue();
                    },
                    'choice_label' => function (CategoryProductUid $category) {
                        return $category->getOptions();
                    },
                    'label' => false,
                    'required' => false,
                ]);
            }

        });


        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event): void {
                /** @var ProductFilterDTO $data */
                $data = $event->getData();

                $this->request->getSession()->remove(ProductFilterDTO::category);

                $this->request->getSession()->set(ProductFilterDTO::all, $data->getAll());

                $this->request->getSession()->set(ProductFilterDTO::category, $data->getCategory());
                $this->request->getSession()->set(ProductFilterDTO::offer, $data->getOffer());
                $this->request->getSession()->set(ProductFilterDTO::variation, $data->getVariation());
                $this->request->getSession()->set(ProductFilterDTO::modification, $data->getModification());


                $session = [];

                if($data->getProperty())
                {
                    /** @var Property\ProductFilterPropertyDTO $property */
                    foreach($data->getProperty() as $property)
                    {
                        if(!empty($property->getValue()) && $property->getValue() !== 'false')
                        {
                            $session[$property->getConst()] = $property->getValue();
                        }
                    }

                }


                $this->request->getSession()->set('catalog_filter', $session);
            }
        );


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {

                // this would be your entity, i.e. SportMeetup

                /** @var ProductFilterDTO $data */

                $data = $event->getData();
                $builder = $event->getForm();

                $Category = $data->getCategory();

                if(isset($this->request->getMainRequest()?->get($builder->getName())['category']))
                {
                    $Category = !empty($this->request->getMainRequest()?->get($builder->getName())['category']) ?
                        new CategoryProductUid($this->request->getMainRequest()?->get($builder->getName())['category']) : null;

                }

                if($Category)
                {
                    /** Торговое предложение раздела */

                    $offerField = $this->offerChoice
                        ->category($Category)
                        ->findAllCategoryProductOffers();

                    if($offerField)
                    {
                        $inputOffer = $this->choice->getChoice($offerField->getField());

                        if($inputOffer)
                        {
                            $builder->add(
                                'offer',
                                method_exists($inputOffer, 'formFilterExists') ? $inputOffer->formFilterExists() : $inputOffer->form(),
                                [
                                    'label' => $offerField->getOption(),
                                    'priority' => 200,
                                    'required' => false,
                                    'translation_domain' => $inputOffer->domain()
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
                                    $builder->add(
                                        'variation',
                                        method_exists($inputVariation, 'formFilterExists') ? $inputVariation->formFilterExists() : $inputVariation->form(),
                                        [
                                            'label' => $variationField->getOption(),
                                            'priority' => 199,
                                            'required' => false,
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
                                            $builder->add(
                                                'modification',
                                                method_exists($inputModification, 'formFilterExists') ? $inputModification->formFilterExists() : $inputModification->form(),
                                                [
                                                    'label' => $modificationField->getOption(),
                                                    'priority' => 198,
                                                    'required' => false,
                                                ]
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $fields = $this->fields
                        ->category($Category)
                        ->findAll();

                    if($fields)
                    {
                        foreach($fields as $field)
                        {
                            if(empty($field['const']))
                            {
                                continue;
                            }

                            $ProductFilterPropertyDTO = new Property\ProductFilterPropertyDTO();

                            $ProductFilterPropertyDTO->setConst($field['const']);
                            $ProductFilterPropertyDTO->setLabel($field['name']);
                            $ProductFilterPropertyDTO->setType($field['type']);

                            $data->addProperty($ProductFilterPropertyDTO);


                        }
                    }

                    $session = $this->request->getSession()->get('catalog_filter');

                    /* TRANS CollectionType */
                    $builder->add('property', CollectionType::class, [
                        'entry_type' => Property\ProductFilterPropertyForm::class,
                        'entry_options' => ['label' => false, 'session' => $session],
                        'label' => false,
                        'by_reference' => false,
                        'allow_delete' => false,
                        'allow_add' => false,
                        'prototype_name' => '__property__',
                    ]);


                }
                else
                {
                    $data->setOffer(null);
                    $data->setVariation(null);
                    $data->setModification(null);
                    $data->setProperty(null);
                }
            }
        );


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductFilterDTO::class,
                'validation_groups' => false,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ]
        );
    }

}
