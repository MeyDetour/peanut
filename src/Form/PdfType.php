<?php

namespace App\Form;

use App\Entity\Pdf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PdfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mensuelDetails', CheckboxType::class, [
                'label' => 'Monthly incomes/outcomes details',
                'data' => true,

                'required'=>false
            ])
            ->add('names', CheckboxType::class, [
                'label' => 'Show account and wallet display names',
                'data' => false,
                'required'=>false
            ])
            ->add('EntiteName', TextType::class, [
                'label' => 'Full name to display',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pdf::class,
        ]);
    }
}
