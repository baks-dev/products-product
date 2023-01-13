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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Property;

//use App\Module\Product\Entity\Product\Property;
//use App\Module\Product\Repository\Product\Event\Property\PropertyFormByCategory\PropertyFormByCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/* Форма свойств продукта */

final class PropertyCollectionForm extends AbstractType
{
    
    //    private PropertyFormByCategoryRepository $propertyCategory;
    //
    //    public function __construct(PropertyFormByCategoryRepository $propertyCategory)
    //    {
    //
    //        $this->propertyCategory = $propertyCategory;
    //    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        
        //        $builder->add(
        //          'section',
        //          TextType::class,
        //          ['mapped' => false]
        //        );
        
        $builder->add(
          'value',
          TextType::class,
        
        );
        
        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($options)
          {
              $data = $event->getData();
              $form = $event->getForm();

              if($data)
              {
                  $propertyCategory = $options['properties'];
                  $id = (string) $data->getField();
                  
                  if(empty($id) || !isset($propertyCategory[$id])) { return; }
                  
                  $propCat = $propertyCategory[$id];
                  
                  /* СЕКЦИЯ */
                  $form->add(
                    'section',
                    HiddenType::class,
                    [
                      //'mapped' => false,
                      //'data' => (string) $propCat->sectionUid,
                      'label' => $propCat->sectionTrans
                    ]
                  );
                  
                  
                  match ($propCat->fieldType)
                  {
                      /* INTEGER */
                      'integer' => $form->add
                      (
                        'value',
                        IntegerType::class,
                        [
                          'label' => $propCat->fieldTrans,
                          'required' => $propCat->fieldRequired,
                        ]
                      )
                  ,
                      
                      /* MAIL */
                      'mail' => $form->add
                      (
                        'value',
                        EmailType::class,
                        [
                          'label' => $propCat->fieldTrans,
                          'required' => $propCat->fieldRequired,
                          'help' => $propCat->fieldDesc,
                        ]),
                      
                      /* PHONE */
                      'phone' => $form->add
                      (
                        'value',
                        TextType::class,
                        [
                          'label' => $propCat->fieldTrans,
                          'required' => $propCat->fieldRequired,
                          'attr' =>
                            [
                              'placeholder' => $propCat->fieldDesc,
                            ]
                        ]),
                      
                      /* SELECT */
                      'select' => $form->add
                      (
                        'value',
                        ChoiceType::class,
                        [
                          'label' => $propCat->fieldTrans,
                          'required' => $propCat->fieldRequired,
                          'placeholder' => $propCat->fieldDesc,
                        ]),
                      
                      /* TEXTAREA */
                      'textarea' => $form->add(
                        'value',
                        TextareaType::class,
                        [
                          'label' => $propCat->fieldTrans,
                          'required' => $propCat->fieldRequired,
                          'help' => $propCat->fieldDesc,
                        ]),
                      
                      default => $form->add
                      (
                        'value',
                        TextType::class,
                        [
                          'label' => $propCat->fieldTrans,
                          'required' => $propCat->fieldRequired,
                        ])
                      
                  };
                  
                  
              }
          });
        
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => PropertyCollectionDTO::class,
            'properties' => null,
          ]);
    }
    
}
