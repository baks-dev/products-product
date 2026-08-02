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

declare(strict_types=1);

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Certificate\Type;


use BaksDev\Products\Product\Type\Certificate\Document\CertificateDocument;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentCollection;
use BaksDev\Products\Product\Type\Certificate\Document\Types\CertificateDocumentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductCertificateTypeForm extends AbstractType
{
    public function __construct(
        private readonly CertificateDocumentCollection $CertificateDocumentCollection
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('value', ChoiceType::class, [
            'choices' => $this->CertificateDocumentCollection->cases(),
            'choice_value' => function(CertificateDocumentInterface|CertificateDocument|null $type) {

                if($type instanceof CertificateDocumentInterface)
                {
                    $type = new CertificateDocument($type);
                }

                return $type;
            },
            'choice_label' => function(CertificateDocumentInterface|CertificateDocument $type) {

                if($type instanceof CertificateDocumentInterface)
                {
                    $type = new CertificateDocument($type);
                }

                return $type;
            },
            'label' => false,
            'translation_domain' => 'certificate.document',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCertificateTypeDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}