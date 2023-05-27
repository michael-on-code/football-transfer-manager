<?php

namespace App\Form;

use App\Entity\Player;
use App\Repository\TeamRepository;
use App\Service\Utils;
use DateTime;
use Monolog\Handler\Curl\Util;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PlayerEditType extends AbstractType
{

    public function __construct(private TeamRepository $teamRepo )
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
         
        $playerTeamID = $options['data']->getTeam()->getId();
        $teams = $this->teamRepo->findAll();
        $maxDate = new DateTime();
        $builder
            ->add('firstName', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field'
                    ]),
                    new Length([
                        'max'=>255,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    ],
                    'attr'=>[
                        'class'=>'form-control'
                    ]
            ])
            ->add('lastName', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field'
                    ]),
                    new Length([
                        'max'=>255,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    ],
                    'attr'=>[
                        'class'=>'form-control'
                    ]
            ])
            ->add('surname', TextType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field'
                    ]),
                    new Length([
                        'max'=>255,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    ],
                    'attr'=>[
                        'class'=>'form-control'
                    ]
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
            ])
            ->add('photoFile' , FileType::class, [
                'constraints'=>[
                    new File([
                        'maxSize' => Utils::getUploadMaxSize(false),
                        'mimeTypes' => Utils::getImageMimeTypes(),
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ]),
                ],
                'mapped'=>false,
                'required'=>false,
                'attr'=>[
                    'class'=>'image-uploader visuallyhidden',
                    'accept'=>implode(',', Utils::getUploadImageExtensions()),
                    'data-maxsize'=>Utils::getUploadMaxSize()
                ]
            ])
            ->add('birthdate', DateType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Image required'
                    ]),
                ],
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr'=>[
                    'class'=>'form-control',
                    'type'=>'date',
                    'max'=>$maxDate->format('Y-m-d'),
                ]
            ])
            ->add('about', TextareaType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field',
                    ])
                    ],
                    'required'=>true,
                    'attr'=>['class'=>'form-control', 'rows'=>'3']
            ])
           // ->add('createdAt')
           // ->add('user')
            ->add('team', ChoiceType::class, [
                'required'=>false,
                'disabled'=>true,
                'mapped'=>false,
                'choices'=>Utils::getTeamsForSelect($teams),
                'label'=>"Player's team",
                'attr'=>['class'=>'select2'],
                'data'=>$playerTeamID
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}
