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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer;

//use App\Module\Product\Entity\Product\Offers\Offer;
use App\Module\Products\Category\Repository\CategoryOffersForm\CategoryOffersFormDTO;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Image\ImageCollectionForm;
use App\Module\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Price\PriceForm;
use App\System\Type\Reference\ReferenceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OfferForm extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('offer', HiddenType::class);
        
        $builder->add('value', TextType::class);

        $builder->add('price', PriceForm::class, ['label' => false]);
        
        $builder->add('quantity', Quantity\QuantityForm::class, ['label' => false]);
        
        $builder->add('article', TextType::class);
        
        /** Торговые предложения */
        $builder->add('images', CollectionType::class, [
          'entry_type' => ImageCollectionForm::class,
          'entry_options' => [
            'label' => false,
          ],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
          'prototype_name' => '__images__'
        ]);
    
        if($options['offers'])
        {
            foreach($options['offers'] as $offer)
            {
                if($offer->multiple)
                {
                    $builder->add
                    (
                      'addMultipleOffer',
                      ButtonType::class,
                      [
                        'label_html' => true,
                      ]);
            
            
                    $builder->add
                    (
                      'deleteMultipleOffer',
                      ButtonType::class,
                      [
                        'label_html' => true,
                        'attr' =>
                          ['class' => 'btn-icon btn-light-danger del-item-collection'],
                      ]);
            
                    break;
                }
            }
        }
        
        
        

        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($options)
          {
              $data = $event->getData();
              $form = $event->getForm();

              if($data)
              {
                  
                  
                  /* Получаем данные торговые предложения категории */
                  $offerCat = $data->getOffer();
                  
      
                  /* Массив торговых предложений */
                  $offers = $options['offers'];
                  $key = (string) $data->getOffer();
                  
                  /** @var CategoryOffersFormDTO $offer */
                  $offer = $offers[$key];
    
    
                  
                  
                  /* Если не справочник - тектсовое поле */
                  $form->add(
                    'value', TextType::class,
                    [
                      'label' => $offer->name
                    ]
                  );
    
                  $form->add('offer', HiddenType::class, ['attr' => ['data-offer' => $offerCat->getValue()]]);
                  
                  

                  /* Если справочник - ChoiceType */
                  if($offer->reference)
                  {

                      /* Получаем справочник по типу Reference */
                      $choices = new ReferenceType($offer->reference);
                      
                      $form
                        ->add('value', ChoiceType::class, [
                          'choices' => $choices->getChoice(),
                          'choice_value' => function ($choice)
                          {
                              return is_string($choice) ? $choice : $choice?->value;
                          },
                          'choice_label' => function ($choice)
                          {
                              return $choice?->value;
                          },

                          'choice_translation_domain' => 'reference.'.$offer->reference,

                          'label' => $offer->name,
                          'expanded' => false,
                          'multiple' => false,
                          'required' => true,
                          'placeholder' => $this->translator->trans('placeholder.'.$offer->reference, domain: 'reference.'.$offer->reference),
                          'attr' => ['data-select' => 'select2', 'data-offer' => $offer->reference ? $offerCat->getValue() : false],
                        ]);
                      
                      
                      /* Если множественный */
                      if(!$offer->multiple)
                      {
                          $form->remove('addMultipleOffer');
                          $form->remove('deleteMultipleOffer');
                      }

                  }
    
                  /* Удаляем количественный учет */
                  if(!$offer->quantitative)
                  {
                      $form->remove('quantity');
                  }
                 
                  
                  /* Удаляем артикул если запрещено */
                  if(!$offer->article)
                  {
                      $form->remove('article');
                  }

                  /* Удаляем пользовательское изображение если запрещено */
                  if(!$offer->image) {
                      $form->remove('images');
                  }

                  /* Удаляем Прайс на торговое предложение, если нет прайса */
                  if(!$offer->price)
                  {
                     $form->remove('price');
                  }
              }

          }
        );
        
        
        
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => OfferDTO::class,
            'offer_data' => null,
            'offers' => null,
          ]);
    }
    
}
