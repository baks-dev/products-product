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

declare(strict_types=1);

namespace BaksDev\Products\Product\Forms\ProductCategoryFilter\User;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Category\Repository\AllFilterFieldsByCategory\AllFilterFieldsByCategoryInterface;
use BaksDev\Products\Category\Repository\ModificationFieldsCategoryChoice\ModificationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\OfferFieldsCategoryChoice\OfferFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\VariationFieldsCategoryChoice\VariationFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductCategoryFilterForm extends AbstractType
{

    private SessionInterface|false $session = false;

    private string $sessionKey;

    public function __construct(

        private readonly AllFilterFieldsByCategoryInterface $fields,
        private readonly OfferFieldsCategoryChoiceInterface $offerChoice,
        private readonly VariationFieldsCategoryChoiceInterface $variationChoice,
        private readonly ModificationFieldsCategoryChoiceInterface $modificationChoice,
        private readonly FieldsChoice $choice,
        private readonly RequestStack $request,
    )
    {
        $this->sessionKey = md5(ProductFilterForm::class);
    }


    /**
     * Форма фильтрации товаров в разделе
     */

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event): void {

            $sessionArray = false;

            /** @var ProductFilterDTO $data */
            $data = $event->getData();
            $builder = $event->getForm();

            $Request = $this->request->getMainRequest();

            if($Request && 'POST' === $Request->getMethod())
            {
                $sessionArray = current($Request->request->all());
            }
            else
            {
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

                if($this->session)
                {
                    $sessionData = $this->request->getSession()->get($this->sessionKey);
                    $sessionJson = $sessionData ? base64_decode($sessionData) : false;
                    $sessionArray = $sessionJson !== false && json_validate($sessionJson) ? json_decode($sessionJson, true, 512, JSON_THROW_ON_ERROR) : false;


                }
            }

            if($sessionArray !== false)
            {
                ///isset($sessionArray['all']) ? $data->setAll($sessionArray['all'] === true) : false;
                isset($sessionArray['category']) ? $data->setCategory(new CategoryProductUid($sessionArray['category'], $sessionArray['category_name'] ?? null)) : false;
                isset($sessionArray['offer']) ? $data->setOffer($sessionArray['offer']) : false;
                isset($sessionArray['variation']) ? $data->setVariation($sessionArray['variation']) : false;
                isset($sessionArray['modification']) ? $data->setModification($sessionArray['modification']) : false;

                if($Request && 'POST' === $Request->getMethod())
                {
                    $sessionJson = json_encode($sessionArray, JSON_THROW_ON_ERROR);
                    $sessionData = base64_encode($sessionJson);
                    $this->request->getSession()->set($this->sessionKey, $sessionData);
                }

            }

        });


        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {

                if($this->session === false)
                {
                    $this->session = $this->request->getSession();
                }

                if($this->session)
                {
                    /** @var ProductFilterDTO $data */
                    $data = $event->getData();

                    $sessionArray = [];

                    if($data->getCategory())
                    {
                        if($data->getCategory())
                        {
                            $sessionArray['category'] = (string) $data->getCategory();
                            $sessionArray['category_name'] = $data->getCategory()->getOptions();
                        }

                        $data->getOffer() ? $sessionArray['offer'] = (string) $data->getOffer() : false;
                        $data->getVariation() ? $sessionArray['variation'] = (string) $data->getVariation() : false;
                        $data->getModification() ? $sessionArray['modification'] = (string) $data->getModification() : false;
                    }

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

                    !empty($properties) ? $sessionArray['properties'] = $properties : false;

                    if($sessionArray)
                    {
                        $sessionJson = json_encode($sessionArray, JSON_THROW_ON_ERROR);
                        $sessionData = base64_encode($sessionJson);
                        $this->request->getSession()->set($this->sessionKey, $sessionData);
                        return;
                    }

                    $this->session->remove($this->sessionKey);
                }
            }
        );


        $data = $builder->getData();

        if($data->getCategory())
        {

            /** Торговое предложение раздела */

            $offerField = $this->offerChoice
                ->category($data->getCategory())
                ->findAllCategoryProductOffers();

            if($offerField)
            {
                $inputOffer = $this->choice->getChoice($offerField->getField());

                if($inputOffer)
                {
                    $builder->add(
                        'offer',
                        method_exists($inputOffer, 'formFilterAvailable') ? $inputOffer->formFilterAvailable() : $inputOffer->form(),
                        [
                            'label' => $offerField->getOption(),
                            'priority' => 200,
                            'required' => false,
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
                                method_exists($inputVariation, 'formFilterAvailable') ? $inputVariation->formFilterAvailable() : $inputVariation->form(),
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
                                        method_exists($inputModification, 'formFilterAvailable') ? $inputModification->formFilterAvailable() : $inputModification->form(),
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


            /** Свойства, участвующие в фильтре */

            $fields = $this->fields
                ->category($data->getCategory())
                ->findAll();

            if($fields)
            {
                $session = $this->request->getSession()->get('catalog_filter');

                $i = 100;
                foreach($fields as $field)
                {
                    $input = $this->choice->getChoice(new InputField($field['type']));

                    if($input)
                    {
                        $builder->add(
                            $field['const'],
                            $input->form(),
                            [
                                'label' => $field['name'],
                                'mapped' => false,
                                'priority' => $i,
                                'required' => false,
                                'block_name' => $field['type'],
                                'data' => $session[$field['type']] ?? null,
                            ]
                        );
                    }

                    $i--;
                }

                //                $builder->addEventListener(
                //                    FormEvents::POST_SUBMIT,
                //                    function(FormEvent $event) {
                //
                //                        $data = $event->getForm()->all();
                //
                //                        $session = [];
                //
                //                        foreach($data as $datum)
                //                        {
                //                            if(!empty($datum->getViewData()))
                //                            {
                //                                if($datum->getNormData() === true)
                //                                {
                //                                    $item = 'true';
                //                                }
                //                                else
                //                                {
                //                                    $item = (string) $datum->getNormData();
                //                                }
                //
                //                                $session[$datum->getConfig()->getOption('block_name')] = $item;
                //                            }
                //                        }
                //
                //                        $this->request->getSession()->set('catalog_filter', $session);
                //
                //                    }
                //                );


            }

        }

        /* Сохранить ******************************************************/
        $builder->add(
            'filter',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCategoryFilterDTO::class,
        ]);
    }

}
