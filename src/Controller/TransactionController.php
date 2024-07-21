<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\RecurringOperation;
use App\Entity\Transaction;
use App\Form\CategoryType;
use App\Form\IncomeTransactionType;
use App\Form\TransactionType;
use App\Repository\CategoryRepository;
use App\Repository\RecurringOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{ private EntityManagerInterface $manager;
    private CategoryRepository $categoryRepository;

    public function __Construct(RecurringOperationRepository $recurringOperationRepository, CategoryRepository $categoryRepository, EntityManagerInterface $manager)
    {
        $this->manager = $manager;
       $this->categoryRepository = $categoryRepository;
    }

    #[Route('/transaction/outcome/new', name: 'new_outcome')]
    #[Route('/transaction/income/new', name: 'new_income')]
    public function add(Request $request, EntityManagerInterface $manager, CategoryRepository $repository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if ($repository->count(['type' => 'income']) == 0 || $repository->count(['type' => 'outcome']) == 0) {
            return $this->redirectToRoute('app_category');
        }
        $route = $request->attributes->get('_route');
        $transaction = new Transaction();
        if ($route == 'new_outcome') {
            $form = $this->createForm(TransactionType::class, $transaction, ['user' => $user]);
            $defaultCategory = $repository->findBy(['type' => 'outcome', 'owner' => $user])[0];
            $transaction->setCategory($defaultCategory);


        } else {
            $form = $this->createForm(IncomeTransactionType::class, $transaction, ['user' => $user]);
            $defaultCategory = $repository->findBy(['type' => 'income', 'owner' => $user])[0];
            $transaction->setCategory($defaultCategory);
        }


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($route == 'new_outcome') {
                $transaction->setType('outcome');
            }
            if ($route == 'new_income') {
                $transaction->setType('income');
            }

            $transaction->setOwner($user);
            $transaction->setDate(new \DateTimeImmutable());
            $manager->persist($transaction);
            $manager->flush();
            return $this->redirectToRoute('app_home');
        }
        if ($route == 'new_outcome') {
            return $this->render('transaction/index.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        return $this->render('transaction/inCome.html.twig', [
            'form' => $form->createView(),
        ]);
    }
#[Route('/transaction/{id}/edit', name: 'edit_transaction')]
public function edit(Request $request, EntityManagerInterface $manager, CategoryRepository $repository, Transaction $transaction): Response
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
    if ($repository->count(['type' => 'income']) == 0 || $repository->count(['type' => 'outcome']) == 0) {
        return $this->redirectToRoute('app_category');
    }
    if ($transaction->getType() == 'outcome') {
        $form = $this->createForm(TransactionType::class, $transaction, ['user' => $user]);


    } else {
        $form = $this->createForm(IncomeTransactionType::class, $transaction, ['user' => $user]);

    }


    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {

        $manager->persist($transaction);
        $manager->flush();
        return $this->redirectToRoute('app_home');
    }
    if ($transaction->getType() == 'outcome') {
        return $this->render('transaction/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    return $this->render('transaction/inCome.html.twig', [
        'form' => $form->createView(),
    ]);
}
    public function createTransaction(RecurringOperation $operation, $date)
    {
        $transaction = new Transaction();
        $transaction->setType($operation->getType());
        $transaction->setMontant($operation->getMontant());
        $transaction->setDate($date);
        $transaction->setOwner($this->getUser());
        $transaction->setName($operation->getName());

        $cat = $this->categoryRepository->findOneBy(['name' => $operation->getName()]);
        if (!$cat) {
            $cat = new Category();
            $cat->setType($operation->getType());
            $cat->setMontant($operation->getMontant());
            $cat->setName($operation->getName());
            $cat->setOwner($this->getUser());

            $this->manager->persist($cat);
            $this->manager->flush();
        }

        $transaction->setCategory($cat);
        //add occurence to the list of occurs
        if ($operation->getOccurences() == '') {
            $operation->setOccurences($date->format('Y-m-d'));
        } else {
            $operation->setOccurences($operation->getOccurences() . ';' . $date->format("Y-m-d"));
        }

        $this->manager->persist($transaction);
        $this->manager->flush();
        dump($transaction);
        return $transaction;
    }

    #[Route('/transaction/{id}/remove', name: 'remove_transaction')]
    public function remove(EntityManagerInterface $manager, Transaction $transaction): Response
    {
        if(!$transaction){

            return $this->json(['message'=>'pas ok']);
        }
        $manager->remove($transaction);
        $manager->flush();
        return $this->json(['message'=>'ok']);
    }
}
