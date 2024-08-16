<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Payment;
use App\Entity\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\PaymentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('category', EntityType::class, [
                'label' => 'Category',
                'expanded' => false,
                'multiple' => false,
                'class' => Category::class,
                'query_builder' => function (CategoryRepository $repository) use ($user) {

                    return $repository->createQueryBuilder('u')
                        ->andWhere('u.type = :type')
                        ->andWhere('u.owner = :owner')
                        ->setParameter('type', 'outcome')
                        ->setParameter('owner', $user)
                        ->orderBy('u.name', 'ASC');
                },

                'choice_label' => 'name',


            ])->add('name', TextType::class, [
                'label' => 'Name'
            ])
            ->add('montant', NumberType::class, [
                'label' => "Montant"
            ])
            ->add('date', DateType::class, [
                'label' => "Date"
            ])
            ->add('payment', EntityType::class, [
                'label' => 'Payment method',
                'expanded' => false,
                'multiple' => false,
                'class' => Payment::class,
                'query_builder' => function (PaymentRepository $repository) use ($user) {

                    return $repository->createQueryBuilder('u')
                        ->andWhere('u.owner = :owner')
                        ->setParameter('owner',$user)
                        ->orderBy('u.name', 'ASC');
                },

                'choice_label' => function (Payment $payment) {
                    return $payment->getName() . ' (' . $payment->getType() . ')';
                },

            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
        ]);
    }
}
