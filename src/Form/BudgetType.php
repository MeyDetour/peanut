<?php

namespace App\Form;

use App\Entity\Budget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', MoneyType::class, [
                'label' => 'Monthly budget not to exceed',
                'attr'=>[
                    'placeholder'=>'Enter amount'
                ]
            ])

            ->add('montant1', MoneyType::class, [
                'label'=>"Amount for the first threshold reminder.",
                'attr'=>[
                    'placeholder'=>'Enter amount'
                ]
            ])

            ->add('montant2', MoneyType::class, [
                'label' => 'Amount for the second threshold reminder.',
                'required' => false,
                'attr'=>[
                    'placeholder'=>'Enter amount'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Budget::class,
        ]);
    }
}
