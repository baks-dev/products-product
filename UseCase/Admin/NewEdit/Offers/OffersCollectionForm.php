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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Offers;

//use App\Module\Product\Entity\Product\Offers;
//use App\Module\Product\Handler\Admin\Product\NewEdit\Offers\OfferForm;
//use App\Module\Product\Repository\Category\Offers\CategoryOffersFormRepository;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\OfferForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

final class OffersCollectionForm extends AbstractType
{
    //    private TranslatorInterface $translator;
    //
    //    public function __construct(TranslatorInterface $translator)
    //    {
    //        $this->translator = $translator;
    //    }
    
    
//    private CategoryOffersFormRepository $offers;
//
//    public function __construct(CategoryOffersFormRepository $offers) {
//
//        $this->offers = $offers;
//    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        
        /** Торговые предложения */
        $builder->add('offer', CollectionType::class, [
          'entry_type' => OfferForm::class,
          'entry_options' => [
            'label' => false,
            'offer_data' => $options['offer_data'],
            'offers' =>  $options['offers'],
          ],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__variation__'
        ]);
        
        
        $builder->add
        (
          'DeleteOffer',
          ButtonType::class,
          [
            'label_html' => true,
            'attr' =>
              ['class' => 'btn btn-sm btn-icon btn-light-danger del-item-offer'],
          ]);
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => OffersCollectionDTO::class,
            'category_id' => null,
            'offer_data' => null,
            'offers' => null,
          ]
        );
    }
    
}
