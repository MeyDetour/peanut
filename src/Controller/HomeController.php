<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\RecurringOperation;
use App\Entity\Transaction;
use App\Repository\CategoryRepository;
use App\Repository\RecurringOperationRepository;
use App\Repository\TransactionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class HomeController extends AbstractController
{


    #[Route('/home', name: 'app_home')]
    public function index(TransactionRepository $transactionRepository, OperationController $operationController, ChartBuilderInterface $chartBuilder, CategoryRepository $categoryRepository, RecurringOperationRepository $recurringOperationRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        //get operation witch must be validate
        $operationsToValidate = $operationController->getUnValidateOperations();

        //load recurrent operations that adding itself
        $operationController->loadOperations();

        //get date
        $today = new \DateTimeImmutable();
        $today = $today->setTime(0, 0);
        $firstDayInYear = $today->setDate($today->format('Y'), 1, 1);
        $firstDayInMonth = $today->setDate($today->format('Y'), $today->format('m'), 1);
        $lastDayInMonth = $today->setDate($today->format('Y'), $today->format('m') + 1, 0);
        $lastDayInYear = $firstDayInYear->setDate($firstDayInYear->format('Y'), 13, 0);

        //calcul totaux of outcomes and incomes
        $total = $this->countTotal($transactionRepository->findBy(['type' => 'income', 'owner' => $user])) - $this->countTotal($transactionRepository->findBy(['type' => 'outcome', 'owner' => $user]));
        $mensuelOutcomes = $this->countTotal($transactionRepository->findBetweenDate($firstDayInMonth, $lastDayInMonth, 'outcome', $user));
        $mensuelIncomes = $this->countTotal($transactionRepository->findBetweenDate($firstDayInMonth, $lastDayInMonth, 'income', $user));
        $totalMensuel = $mensuelIncomes - $mensuelOutcomes;

        //monthlyBudget
        $toastMessage = function ($user, $totalMensuel) {
            $budget = $user->getBudget();
            if ($budget) {
                $montant1 = -$budget->getMontant1();
                $montant2 = -$budget->getMontant2();
                $montant = -$budget->getMontant();/*
dd($totalMensuel,$montant,$montant1,$montant2);*/
                if ($montant && $montant >= $totalMensuel) {
                    return "You have reached or exceeded your monthly budget of " . $montant . "€";
                }
                if (!$montant2 && $montant1 >= $totalMensuel) {
                    return "You have exceeded the threshold of " . $montant1 . "€";
                }
                if ($montant1 >= $totalMensuel) {
                    return "You have exceeded the threshold of " . $montant1 . "€";
                }

                if ($montant2 && $montant2 >= $totalMensuel) {
                    return "You have exceeded the threshold of " . $montant2 . "€";
                }
            }
            return null;
        };

        $toastMessageResult = $toastMessage($user, $totalMensuel);

        return $this->render('home/index.html.twig', [
            'total' => $total,
            'categories' => $categoryRepository->findBy(['owner' => $user]),
            'annuelOutcome' => $this->countTotal($transactionRepository->findBetweenDate($firstDayInYear, $lastDayInYear, 'outcome', $user)),
            'mensuelOutcome' => $mensuelOutcomes,
            'totalOutcome' => $this->countTotal($transactionRepository->findBy(['type' => 'outcome', 'owner' => $user])),
            'annuelIncome' => $this->countTotal($transactionRepository->findBetweenDate($firstDayInYear, $lastDayInYear, 'income', $user)),
            'mensuelIncome' => $mensuelIncomes,
            'totalIncome' => $this->countTotal($transactionRepository->findBy(['type' => 'income', 'owner' => $user])),
            'operationsToValidate' => $operationsToValidate,
            'toastMessage' => $toastMessageResult
        ]);
    }

    #[
        Route('/other', name: 'app_other')]
    public function other(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('/home/other.html.twig', []);
    }

    #[Route('/settings', name: 'app_settings')]
    public function settings(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('/home/settings.html.twig', []);
    }

    private function countTotal($liste)
    {
        $count = 0;
        foreach ($liste as $elt) {
            $count += $elt->getMontant();
        }
        return $count;
    }
}
