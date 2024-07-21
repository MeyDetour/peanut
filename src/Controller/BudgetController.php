<?php

namespace App\Controller;

use App\Entity\Budget;
use App\Entity\RecurringOperation;
use App\Form\BudgetType;
use App\Form\OperationType;
use App\Repository\BudgetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BudgetController extends AbstractController
{
    #[Route('/budget', name: 'app_budget')]
    public function index(Request $request, EntityManagerInterface $manager, BudgetRepository $repository): Response
    {
        $user = $this->getUser();
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($repository->count(['owner'=>$user]) == 1) {
        return $this->redirectToRoute('edit_budget',['id'=>$repository->findAll()[0]->getId()]);
        }
        $budget = new Budget();
        $form = $this->createForm(BudgetType::class, $budget);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $budget->setOwner($user);
            $manager->persist($budget);
            $manager->flush();
        }
        return $this->render('budget/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/budget/{id}/edit', name: 'edit_budget')]
    public function edit(Request $request, EntityManagerInterface $manager, Budget $budget): Response
    {
        $form = $this->createForm(BudgetType::class, $budget);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($budget);
            $manager->flush();
        }
        return $this->render('budget/add.html.twig', [
            'form' => $form->createView(),
            'budget' => $budget
        ]);
    }
}