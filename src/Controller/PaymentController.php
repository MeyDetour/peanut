<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Transaction;
use App\Form\CategoryType;
use App\Form\PayementMethodType;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    private $repository;

    public function __construct(PaymentRepository $repository)
    {
        $this->repository = $repository;
    }

 #[Route('/payment/new', name: 'new_payment')]
    public function newPayement(Request $request, EntityManagerInterface $manager, PaymentRepository $repository): Response
    {
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

         $payment = new Payment();
        $paymentForm = $this->createForm(PaymentType::class, $payment);
        $paymentForm->handleRequest($request);


        if ($paymentForm->isSubmitted() && $paymentForm->isValid()) {
            $payment->setOwner($this->getUser());
            $manager->persist($payment);
            $manager->flush();
            return $this->redirectToRoute('new_payment');
        }
        return $this->render('payment/index.html.twig', [
            'cards' => $repository->findBy(['type' => 'card','owner'=>$user]),
            'wallets' => $repository->findBy(['type' => 'cash','owner'=>$user]),
            'form' => $paymentForm->createView()
        ]);
    }
    #[Route('/payment/{id}/edit', name: 'edit_payment')]
    public function edit(Request $request,Payment $payment ,EntityManagerInterface $manager, PaymentRepository $repository): Response
    {
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }
        if($payment->getOwner() != $user){
            return $this->redirectToRoute('new_payment');
        }
        $payementForm = $this->createForm(PayementMethodType::class, $payment);
        $payementForm->handleRequest($request);


        if ($payementForm->isSubmitted() && $payementForm->isValid()) {
            $manager->persist($payment);
            $manager->flush();
            return $this->redirectToRoute('new_payment');
        }
        return $this->render('payment/edit.html.twig', [
            'cards' => $repository->findBy(['type' => 'card','owner'=>$user]),
            'wallets' => $repository->findBy(['type' => 'cash','owner'=>$user]),
            'form'=>$payementForm->createView()
        ]);
    }
    #[Route('/payment/{id}/remove', name: 'remove_payment')]
    public function remove(Payment $payment,   EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }
        if($payment->getOwner() != $user){
            return $this->redirectToRoute('new_payment');
        }
        $manager->remove($payment);
        $manager->flush();
        return  $this->redirectToRoute('new_payment');
    }
    public function stat($id)
    {
        $payment = $this->repository->find($id);
        $countIncome = 0;
        $countOutcome = 0;
        $nb_income = 0;
        $nb_outcome = 0;
        foreach ($payment->getTransactions() as $transaction) {
            if ($transaction->getType() == 'income') {
                $nb_income += 1;
                $countIncome += $transaction->getMontant();
            } else {
                $nb_outcome += 1;
                $countOutcome += $transaction->getMontant();
            }

        }
        return [
            'nb_income' => $nb_income,
            'nb_outcome' => $nb_outcome,
            'incomes' => $countIncome,
            'outcomes' => $countOutcome,
        ];
    }
}
