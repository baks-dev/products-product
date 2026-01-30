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

namespace BaksDev\Products\Product\Forms\ProductFilter\Admin;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Materials\Catalog\BaksDevMaterialsCatalogBundle;
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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductFilterForm extends AbstractType
{
    private SessionInterface|false $session = false;

    private string $sessionKey;


    public function __construct(
        private readonly RequestStack $request,
        private readonly CategoryChoiceInterface $categoryChoice,
        private readonly OfferFieldsCategoryChoiceInterface $offerChoice,
        private readonly VariationFieldsCategoryChoiceInterface $variationChoice,
        private readonly ModificationFieldsCategoryChoiceInterface $modificationChoice,
        private readonly AllFilterFieldsByCategoryInterface $fields,
        private readonly FieldsChoice $choice,
    )
    {
        $this->sessionKey = md5(self::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('all', CheckboxType::class);


        /**
         * Категория
         */

        $builder->add('category', HiddenType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event): void {

            /** @var ProductFilterDTO $data */
            $data = $event->getData();
            $builder = $event->getForm();

            /** Если в приложении участвует сырье - добавляем фильтр по сырью */
            if(class_exists(BaksDevMaterialsCatalogBundle::class) && false === is_null($data->getMaterials()))
            {
                $builder->add('materials', CheckboxType::class);
            }

            if($this->session === false)
            {
                $this->session = $this->request->getSession();
            }

            if($this->session && $this->session->get('statusCode') === 307)
            {
                $this->session->remove($this->sessionKey);
                $this->session = false;
            }

            if($this->session && (time() - $this->session->getMetadataBag()->getLastUsed()) > 300)
            {
                $this->session->remove($this->sessionKey);
                $this->session = false;
            }

            if($data->isAllVisible() === false)
            {
                $builder->remove('all');
            }

            if($this->session)
            {
                $sessionData = $this->request->getSession()->get($this->sessionKey);
                $sessionJson = $sessionData ? base64_decode($sessionData) : false;
                $sessionArray = $sessionJson !== false && json_validate($sessionJson) ? json_decode($sessionJson, true, 512, JSON_THROW_ON_ERROR) : false;


                if($sessionArray !== false)
                {
                    !isset($sessionArray['all']) ?: $data->setAll($sessionArray['all'] === true);
                    !isset($sessionArray['category']) ?: $data->setCategory(new CategoryProductUid($sessionArray['category'], $sessionArray['category_name'] ?? null));
                    !isset($sessionArray['offer']) ?: $data->setOffer($sessionArray['offer']);
                    !isset($sessionArray['variation']) ?: $data->setVariation($sessionArray['variation']);
                    !isset($sessionArray['modification']) ?: $data->setModification($sessionArray['modification']);
                    !isset($sessionArray['materials']) ?: $data->setMaterials($sessionArray['materials']);
                }
            }


            /** Если жестко не указана категория - выводим список для выбора */

            if($data && $data->isInvisible() === false)
            {

                $builder->add('category', ChoiceType::class, [
                    'choices' => $this->categoryChoice->findAll(),
                    'choice_value' => function(?CategoryProductUid $category) {
                        return $category?->getValue();
                    },
                    'choice_label' => function(CategoryProductUid $category) {
                        return (is_int($category->getAttr()) ? str_repeat(' - ', $category->getAttr() - 1) : '').$category->getOptions();
                    },
                    'label' => false,
                    'required' => false,
                ]);
            }


            $Category = $data->getCategory();
            $dataRequest = $this->request->getMainRequest()?->get($builder->getName());

            if(isset($dataRequest['category']))
            {
                $Category = empty($dataRequest['category']) ? null : new CategoryProductUid($dataRequest['category']);
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
                                'translation_domain' => $inputOffer->domain(),
                                //'empty_data' => $data->getOffer(),
                            ],
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
                                        //'empty_data' => $data->getVariation(),
                                    ],
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
                                                //'empty_data' => $data->getModification(),
                                            ],
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

                        $ProductFilterPropertyDTO->setValue($sessionArray['properties'][$field['const']] ?? null);

                        $data->addProperty($ProductFilterPropertyDTO);

                    }
                }


                /* TRANS CollectionType */
                $builder->add('property', CollectionType::class, [
                    'entry_type' => Property\ProductFilterPropertyForm::class,
                    'entry_options' => ['label' => false],
                    'label' => false,
                    'by_reference' => false,
                    'allow_delete' => false,
                    'allow_add' => false,
                    'prototype_name' => '__property__',
                ]);


            }
            else
            {
                //$data->setOffer(null);
                //$data->setVariation(null);
                //$data->setModification(null);
                //$data->setProperty(null);
            }


        });


        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {

                /** @var ProductFilterDTO $data */
                $data = $event->getData();

                if($this->session === false)
                {
                    $this->session = $this->request->getSession();
                }

                if($this->session)
                {
                    $sessionArray = [];
                    $sessionArray['all'] = $data->getAll();

                    if($data->getCategory())
                    {

                        $sessionArray['category'] = (string) $data->getCategory();
                        $sessionArray['category_name'] = $data->getCategory()->getOptions();

                        empty($data->getOffer()) ?: $sessionArray['offer'] = (string) $data->getOffer();
                        empty($data->getVariation()) ?: $sessionArray['variation'] = (string) $data->getVariation();
                        empty($data->getModification()) ?: $sessionArray['modification'] = (string) $data->getModification();
                    }


                    $sessionArray['materials'] = $data->getMaterials() === true;

                    $properties = [];

                    if($data->getProperty())
                    {
                        /** @var Property\ProductFilterPropertyDTO $property */
                        foreach($data->getProperty() as $property)
                        {
                            if(!empty($property->getValue()) && $property->getValue() !== 'false')
                            {
                                $properties[$property->getConst()] = $property->getValue();
                            }
                        }

                    }

                    empty($properties) ?: $sessionArray['properties'] = $properties;

                    if($sessionArray)
                    {
                        $sessionJson = json_encode($sessionArray, JSON_THROW_ON_ERROR);
                        $sessionData = base64_encode($sessionJson);
                        $this->request->getSession()->set($this->sessionKey, $sessionData);
                        return;
                    }

                    $this->session->remove($this->sessionKey);
                }


                //                $session = [];
                //
                //                if($data->getProperty())
                //                {
                //                    /** @var Property\ProductFilterPropertyDTO $property */
                //                    foreach($data->getProperty() as $property)
                //                    {
                //                        if(!empty($property->getValue()) && $property->getValue() !== 'false')
                //                        {
                //                            $session[$property->getConst()] = $property->getValue();
                //                        }
                //                    }
                //
                //                }


                // $this->request->getSession()->set('catalog_filter', $session);
            },
        );


        //        $builder->addEventListener(
        //            FormEvents::PRE_SET_DATA,
        //            function(FormEvent $event): void {},
        //        );


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductFilterDTO::class,
                'validation_groups' => false,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }
}
