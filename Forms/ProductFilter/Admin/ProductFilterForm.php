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

namespace App\Module\Products\Product\Forms\ProductFilter\Admin;

use App\Module\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use App\Module\Products\Category\Type\Id\CategoryUid;
use App\Module\Products\Product\Repository\ProductUserProfileChoice\ProductUserProfileChoiceInterface;
use App\Module\User\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProductFilterForm extends AbstractType
{
    
    private CategoryChoiceInterface $categoryChoice;
    private ProductUserProfileChoiceInterface $profileChoice;
    private RequestStack $request;
    
    public function __construct(
      CategoryChoiceInterface $categoryChoice,
      ProductUserProfileChoiceInterface $profileChoice,
      RequestStack $request
    )
    {
        $this->categoryChoice = $categoryChoice;
        $this->profileChoice = $profileChoice;
        $this->request = $request;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('profile', ChoiceType::class, [
          'choices' => $this->profileChoice->get(),
          'choice_value' => function (?UserProfileUid $profile)
          {
              return $profile?->getValue();
          },
          'choice_label' => function (UserProfileUid $profile)
          {
              return $profile->getName();
          },
          'label' => false,
          'attr' => ['onchange' => 'this.form.submit()'],
        ]);
        
    
        $builder->add('category', ChoiceType::class, [
          'choices' => $this->categoryChoice->get(),
          'choice_value' => function (?CategoryUid $category)
          {
              return $category?->getValue();
          },
          'choice_label' => function (CategoryUid $category)
          {
              return $category->getOption();
          },
          'label' => false,
          'attr' => ['onchange' => 'this.form.submit()'],
        ]);
        
        
        
        $builder->addEventListener(
          FormEvents::POST_SUBMIT,
          function (FormEvent $event)
          {
              /** @var ProductFilterDTO $data */
              $data = $event->getData();
              
              $this->request->getSession()->set(ProductFilterDTO::profile, $data->getProfile());
              $this->request->getSession()->set(ProductFilterDTO::category, $data->getCategory());
          }
        );
        
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => ProductFilterDTO::class,
            'method' => 'POST',
          ]);
    }
    
}
