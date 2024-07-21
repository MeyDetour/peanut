<?php

namespace App\Form;

use App\Entity\Payment;
use App\Entity\RecurringOperation;
use App\Repository\PaymentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('repetition', ChoiceType::class, [
                'label' => 'Repetition',
                'expanded' => false,
                'multiple' => false,
                'choices' => [
                    "Annualy" => 'annualy',
                    "Monthly" => 'monthly',
                    "Weekly" => 'weekly',
                    "Daily" => 'daily',
                ]


            ])
            ->add('addingType', ChoiceType::class, [
                    'label' => 'Adding',
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => [
                        "Manually confirm receipt" => 'manual',
                        "Add automatically" => 'automatic',

                    ]]
            )
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Outcome' => 'outcome',
                    'Income' => 'income',
                ],
                "multiple" => false,
                "expanded" => true,
            ])
            ->add('firstDate', DateType::class, [
                'label' => 'First date (first occurrence)',
                'widget' => 'single_text',
            ])
            ->add('lastDate', DateType::class, [
'required'=>false,
                'label' => 'Last date (optional)',
                'widget' => 'single_text',
            ])
            ->add('name', TextType::class, [
                'label' => 'Name'
            ])
            ->add('montant', NumberType::class, [
                'label' => 'Montant'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecurringOperation::class,
        ]);
    }
}
