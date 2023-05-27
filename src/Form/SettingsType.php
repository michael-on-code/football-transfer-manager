<?php

namespace App\Form;

use App\Entity\Parameters;
use App\Service\Utils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $siteParams = $options['data'];
        $builder
            ->add('siteName', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field'
                    ]),
                    new Length([
                        'max'=>255
                    ])
                ],
                'required'=>true,
                'mapped'=>false,
                'data'=>Utils::maybeNullOrEmpty($siteParams, 'siteName'),
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('siteDescription', TextareaType::class, [
                'mapped'=>false,
                'required'=>false,
                'data'=>Utils::maybeNullOrEmpty($siteParams, 'siteDescription'),
                'attr'=>[
                    'class'=>'form-control',
                    'rows'=>'4'
                ]
            ])
            ->add('siteCurrency', TextType::class, [
                'mapped'=>false,
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field'
                    ]),
                    new Length([
                        'max'=>10,
                        'maxMessage'=>"Must not exceed {{ limit }} characters"
                    ])
                ],
                'data'=>Utils::maybeNullOrEmpty($siteParams, 'siteCurrency'),
                'attr'=>[
                    'class'=>'form-control',
                ]
            ])
            ->add('siteLogo', HiddenType::class, [
                'mapped'=>false,
                'data'=>Utils::maybeNullOrEmpty($siteParams, 'siteLogo'),
                'required'=>false,
            ])
            ->add('siteLogoFile', FileType::class, [
                'constraints'=>[
                    new File([
                        'maxSize' => Utils::getUploadMaxSize(false),
                        'mimeTypes' => Utils::getImageMimeTypes(),
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
                'mapped'=>false,
                'required'=>false,
                'attr'=>[
                    'class'=>'image-uploader visuallyhidden',
                    'accept'=>implode(',', Utils::getUploadImageExtensions()),
                    'data-maxsize'=>Utils::getUploadMaxSize()
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            //'error_bubbling'=>true
        ]);
    }
}
