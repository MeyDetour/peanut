<?php

namespace App\Form;

use App\Entity\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayementMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type',

                'choices'=>[
                    'Credit card'=>'card',
                    'Cash'=>'cash',
                ],
                "multiple"=>false,
                "expanded"=>true,
            ])
            ->add('name',TextType::class,[
'label'=>'Payment name',
'placeholder'=>'Credit card 1',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
