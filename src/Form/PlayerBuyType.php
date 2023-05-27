<?php

namespace App\Form;

use App\Entity\Player;
use App\Service\Utils;
use NumberFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class PlayerBuyType extends AbstractType
{
    public function __construct()
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field'
                    ]),
                    new PositiveOrZero([
                        'message'=>'Amount must be a positive number'
                    ]),
                    ],
                    'label'=>'Transfer amount',
                    'rounding_mode'=>NumberFormatter::ROUND_HALFUP,
                    'scale'=>2,
                    'html5'=>true,
                    'mapped'=>false,
                    'required'=>true,
                    'attr'=>['class'=>'form-control']
            ])
            ->add('team', ChoiceType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Field required'
                    ]),
                ],
                'label'=>'Team to receive player',
                'mapped'=>false,
                'required'=>false,
                'choices'=>Utils::maybeNullOrEmpty($options['data'], 'teamsSelectChoices', []),
                'attr'=>['class'=>'select2']
            ])
            ->add('description', TextareaType::class, [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Required field',
                    ]),
                    new Length([
                        'max'=>200,
                        'maxMessage'=>'Field must not exceed {{limit}} characters'
                    ])
                    ],
                    'required'=>true,
                    'mapped'=>false,
                    'attr'=>['class'=>'form-control', 'rows'=>'4']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
