<?php

namespace App\Form;

use App\Entity\User;
use App\Service\Utils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilePicChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo', FileType::class, [
                'constraints'=>[
                    new File([
                        'maxSize' => Utils::getUploadMaxSize(false),
                        'mimeTypes' => Utils::getImageMimeTypes(),
                        'mimeTypesMessage' => 'Please upload a valid picture',
                    ])
                ],
                'mapped'=>false,
                //'required'=>true,
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
            'data_class' => User::class,
        ]);
    }
}
