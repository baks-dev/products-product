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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit;

use BaksDev\Core\Services\Reference\ReferenceChoice;
use BaksDev\Products\Category\Repository\CategoryModificationForm\CategoryModificationFormInterface;
use BaksDev\Products\Category\Repository\CategoryOffersForm\CategoryOffersFormInterface;
use BaksDev\Products\Category\Repository\CategoryPropertyById\CategoryPropertyByIdInterface;
use BaksDev\Products\Category\Repository\CategoryVariationForm\CategoryVariationFormInterface;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Property\PropertyCollectionDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductForm extends AbstractType
{
    private CategoryPropertyByIdInterface $categoryProperty;

    private CategoryOffersFormInterface $categoryOffers;

    private CategoryVariationFormInterface $categoryVariation;

    private CategoryModificationFormInterface $categoryModification;

    private ReferenceChoice $reference;


    public function __construct(
        CategoryPropertyByIdInterface $categoryProperty,
        CategoryOffersFormInterface $categoryOffers,
        CategoryVariationFormInterface $categoryVariation,
        CategoryModificationFormInterface $categoryModification,
        ReferenceChoice $reference,
    ) {

        $this->categoryProperty = $categoryProperty;
        $this->categoryOffers = $categoryOffers;
        $this->categoryVariation = $categoryVariation;
        $this->categoryModification = $categoryModification;

        $this->reference = $reference;

    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('info', Info\InfoForm::class, ['label' => false]);

        $builder->add('active', Active\ActiveForm::class, ['label' => false]);

        $builder->add('price', Price\PriceForm::class, ['label' => false]);

        /* CATEGORIES CollectionType */
        $builder->add('category', CollectionType::class, [
            'entry_type' => Category\CategoryCollectionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__categories__',
        ]);

        /* FILES Collection */
        $builder->add('file', CollectionType::class, [
            'entry_type' => Files\FilesCollectionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__files__',
        ]);

        /* SEO Collection */
        $builder->add('seo', CollectionType::class, [
            'entry_type' => Seo\SeoCollectionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__seo__',
        ]);

        /* TRANS CollectionType */
        $builder->add('translate', CollectionType::class, [
            'entry_type' => Trans\ProductTransForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__trans__',
        ]);


        /* TRANS CollectionType */
        $builder->add('description', CollectionType::class, [
            'entry_type' => Description\ProductDescriptionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__description__',
        ]);

        /* PHOTOS CollectionType */
        $builder->add('photo', CollectionType::class, [
            'entry_type' => Photo\PhotoCollectionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__photos__',
        ]);

        /* FILES CollectionType */
        $builder->add('file', CollectionType::class, [
            'entry_type' => Files\FilesCollectionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__files__',
        ]);

        /* VIDEOS CollectionType */
        $builder->add('video', CollectionType::class, [
            'entry_type' => Video\VideoCollectionForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__videos__',
        ]);

        /*
         * PROPERTIES
        */

        /* @var ArrayCollection $categories */
        $categories = $options['data']->getCategory();
        /* @var CategoryCollectionDTO $category */
        $category = $categories->current();

        $propertyCategory = $category->getCategory() ? $this->categoryProperty->findByCategory($category->getCategory()) : null;

        /* CollectionType */
        $builder->add('property', CollectionType::class, [
            'entry_type' => Property\PropertyCollectionForm::class,
            'entry_options' => [
                'label' => false,
                'properties' => $propertyCategory,
            ],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__properties__',
        ]);

        $builder->add('dataOffer', HiddenType::class, ['mapped' => false]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($propertyCategory) {

                /* @var ProductDTO $data */
                $data = $event->getData();
                $form = $event->getForm();


                if($data && $propertyCategory)
                {
                    $sort = 0;

                    foreach($propertyCategory as $propCat)
                    {
                        $new = true;

                        foreach($data->getProperty() as $fieldProperty)
                        {

                            /* Если поле уже заполнено - не объявляем */
                            if($propCat->fieldUid->equals($fieldProperty->getField()))
                            {
                                $fieldProperty->setSection($propCat->sectionUid);
                                $fieldProperty->setSort($sort);

                                $new = false;
                                break;
                            }

                            /* Удаляем свойства, Которые были удалены из категории */
                            if(!isset($propertyCategory[(string) $fieldProperty->getField()]))
                            {
                                $data->removeProperty($fieldProperty);
                            }

                        }

                        /* Если поле не заполнено ранее - создаем */
                        if($new)
                        {
                            $Property = new PropertyCollectionDTO();
                            $Property->setField($propCat->fieldUid);
                            $Property->setSection($propCat->sectionUid);
                            $Property->setSort($sort);
                            $data->addProperty($Property);
                        }

                        $sort++;
                    }
                }
            }
        );

        /* Сохранить ******************************************************/
        $builder->add(
            'product',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );

        /*
         * OFFERS
        */

        //$offers = $category ? $this->categoryOffers->get($category->getCategory()) : null; //  $this->getField->get($profileType);

        /** Создаем торговое предложение  */

        /* Получаем Торговые предложения категории */
        $offersCategory = $category->getCategory() ? $this->categoryOffers->findByCategory($category->getCategory()) : null;

        /* Получаем множественные варианты ТП */
        $variationCategory = $offersCategory ? $this->categoryVariation->findByOffer($offersCategory->id) : null;

        /* Получаем модификации мноественных вариантов */
        $modificationCategory = $variationCategory ? $this->categoryModification->findByVariation($variationCategory->id) : null;

        $builder->add('offer', CollectionType::class, [
            'entry_type' => Offers\ProductOffersCollectionForm::class,
            'entry_options' => [
                'label' => false,
                //'category_id' => $category,
                'offers' => $offersCategory,
                'variation' => $variationCategory,
                'modification' => $modificationCategory,
            ],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__offers__',
        ]);

        if($offersCategory)
        {
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($offersCategory, $variationCategory, $modificationCategory) {

                    /* @var ProductDTO $data */
                    $data = $event->getData();
                    $form = $event->getForm();


                    if(!empty($offersCategory))
                    {
                        /* Создаем свойство с идентификатором ТП для прототипа */
                        $form->add('dataOffer', HiddenType::class, ['data' => $offersCategory->id, 'mapped' => false]);

                        if($offersCategory->reference)
                        {
                            $reference = $this->reference->getChoice($offersCategory->reference);

                            if($reference)
                            {

                                $form->add(
                                    'data-offer-reference',
                                    $reference->form(),
                                    [
                                        'label' => false,
                                        'required' => false,
                                        'mapped' => false,
                                        'attr' => ['style' => 'display: none;'],
                                    ]
                                );

                            }
                        }
                    }


                    if(!empty($variationCategory))
                    {
                        /* Создаем свойство с идентификатором множественного варианта для прототипа */
                        $form->add(
                            'dataVariation',
                            HiddenType::class,
                            ['data' => $variationCategory->id, 'mapped' => false]
                        );

                        if($variationCategory->reference)
                        {

                            //$form->add('data-offer-reference', HiddenType::class,  ['data' => $offersCategory->id, 'mapped' => false]);

                            $reference = $this->reference->getChoice($variationCategory->reference);

                            if($reference)
                            {
                                $form->add(
                                    'data-variation-reference',
                                    $reference->form(),
                                    [
                                        'label' => false,
                                        'required' => false,
                                        'mapped' => false,
                                        'attr' => ['style' => 'display: none;'],
                                    ]
                                );


                            }
                        }
                    }

                    if(!empty($modificationCategory))
                    {
                        /* Создаем свойство с идентификатором модификации для прототипа */
                        $form->add(
                            'dataModification',
                            HiddenType::class,
                            ['data' => $modificationCategory->id, 'mapped' => false]
                        );

                        if($modificationCategory->reference)
                        {

                            //$form->add('data-offer-reference', HiddenType::class,  ['data' => $offersCategory->id, 'mapped' => false]);

                            $reference = $this->reference->getChoice($modificationCategory->reference);

                            if($reference)
                            {

                                $form->add(
                                    'data-modification-reference',
                                    $reference->form(),
                                    [
                                        'label' => false,
                                        'required' => false,
                                        'mapped' => false,
                                        'attr' => ['style' => 'display: none;'],
                                    ]
                                );


                            }
                        }
                    }


                    if(!empty($offersCategory) && $data->getOffer()->isEmpty())
                    {

                        $ProductOffersCollectionDTO = new Offers\ProductOffersCollectionDTO();
                        $ProductOffersCollectionDTO->setCategoryOffer($offersCategory->id);

                        if($offersCategory->image)
                        {
                            $ProductOfferImageCollectionDTO = new Offers\Image\ProductOfferImageCollectionDTO();
                            $ProductOfferImageCollectionDTO->setRoot(true);
                            $ProductOffersCollectionDTO->addImage($ProductOfferImageCollectionDTO);
                        }

                        if($variationCategory)
                        {

                            $ProductOffersVariationCollectionDTO = new Offers\Variation\ProductOffersVariationCollectionDTO();
                            $ProductOffersVariationCollectionDTO->setCategoryVariation($variationCategory->id);

                            if($variationCategory->image)
                            {
                                $ProductOfferVariationImageCollectionDTO =
                                    new Offers\Variation\Image\ProductVariationImageCollectionDTO();
                                $ProductOfferVariationImageCollectionDTO->setRoot(true);
                                $ProductOffersVariationCollectionDTO->addImage(
                                    $ProductOfferVariationImageCollectionDTO
                                );
                            }

                            $ProductOffersCollectionDTO->addVariation($ProductOffersVariationCollectionDTO);


                            if($modificationCategory)
                            {
                                $ProductOffersVariationModificationCollectionDTO =
                                    new Offers\Variation\Modification\ProductOffersVariationModificationCollectionDTO();
                                $ProductOffersVariationModificationCollectionDTO
                                    ->setCategoryModification($modificationCategory->id);

                                if($modificationCategory->image)
                                {
                                    $ProductOfferVariationModificationImageCollectionDTO =
                                        new Offers\Variation\Modification\Image\ProductModificationImageCollectionDTO();
                                    $ProductOfferVariationModificationImageCollectionDTO->setRoot(true);
                                    $ProductOffersVariationModificationCollectionDTO->addImage($ProductOfferVariationModificationImageCollectionDTO);


                                }

                                $ProductOffersVariationCollectionDTO->addModification($ProductOffersVariationModificationCollectionDTO);
                            }

                        }

                        $data->addOffer($ProductOffersCollectionDTO);
                    }
                }
            );
        }


    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ProductDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ]
        );
    }

}
