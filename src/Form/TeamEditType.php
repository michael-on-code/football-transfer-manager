<?php

namespace App\Form;

use App\Entity\Team;
use App\Service\Utils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TeamEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Field required'
                    ]),
                    new Length([
                        'max'=>255,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    ],
                    'attr'=>['class'=>'form-control'],
                    //'data'=>Utils::maybeNullOrEmpty($team, "name"),
            ])
            ->add('country', ChoiceType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Field required'
                    ]),
                    new Length([
                        'max'=>4,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    
                ],
                'choices'=>Utils::getCountriesForSelect(),
                'attr'=>['class'=>'select2'],
                //'data'=>Utils::maybeNullOrEmpty($team, "country"),
            ])
            ->add('logoFile', FileType::class, [
                'constraints'=>[
                    new File([
                        'maxSize' => Utils::getUploadMaxSize(false),
                        'mimeTypes' => Utils::getImageMimeTypes(),
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ]),
                    /* new NotBlank([
                        'message'=>'Image required'
                    ]), */
                ],
                'mapped'=>false,
                'required'=>false,
                'attr'=>[
                    'class'=>'image-uploader visuallyhidden',
                    'accept'=>implode(',', Utils::getUploadImageExtensions()),
                    'data-maxsize'=>Utils::getUploadMaxSize()
                ]
            ])
            ->add('description', TextareaType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field',
                    ]),
                    new Length([
                        'max'=>255,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    ],
                    'required'=>true,
                    'attr'=>['class'=>'form-control', 'rows'=>'4'],
                    //'data'=>Utils::maybeNullOrEmpty($team, "description"),
            ])
            //->add('createdAt')
            //->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
