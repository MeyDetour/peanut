<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\RecurringOperation;
use App\Form\CategoryType;
use App\Form\OperationType;
use App\Repository\CategoryRepository;
use App\Repository\RecurringOperationRepository;
use App\Repository\TransactionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OperationController extends AbstractController
{
    private RecurringOperationRepository $recurringOperationRepository;
    private TransactionRepository $transactionRepository;
    private TransactionController $transactionController;
    private $manager;

    public function __Construct(RecurringOperationRepository $recurringOperationRepository, TransactionRepository $transactionRepository, EntityManagerInterface $manager, TransactionController $controller)
    {
        $this->recurringOperationRepository = $recurringOperationRepository;
        $this->transactionController = $controller;
        $this->manager = $manager;
        $this->transactionRepository = $transactionRepository;
    }

    #[Route('/operation', name: 'app_operations')]
    public function index(RecurringOperationRepository $repository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('operation/index.html.twig', [
            'operations' => $repository->findBy(['owner' => $user], ['name' => 'ASC'])
        ]);
    }

    #[Route('/operation/new', name: 'add_operation')]
    public function add(Request $request, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $op = new RecurringOperation();
        $form = $this->createForm(OperationType::class, $op);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cat = new Category();
            $cat->setType($op->getType());
            $cat->setMontant($op->getMontant());
            $cat->setName($op->getName());
            $cat->setOwner($this->getUser());

            $op->setOwner($this->getUser());
            $manager->persist($op);
            $manager->persist($cat);
            $manager->flush();


            return $this->redirectToRoute('app_operations');
        }
        return $this->render('operation/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/operation/{id}/edit', name: 'edit_operation')]
    public function edit(Request $request, RecurringOperation $op, CategoryRepository $categoryRepository, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser() != $op->getOwner()) {
            return $this->redirectToRoute('app_operations');
        }
        $name = $op->getName();
        $form = $this->createForm(OperationType::class, $op);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cat = $categoryRepository->findBy(['name' => $name]);
            if (!$cat) {
                $cat = new Category();
                $cat->setType($op->getType());
                $cat->setMontant($op->getMontant());
                $cat->setName($op->getName());
                $cat->setOwner($this->getUser());

                $manager->persist($cat);
            }
            $manager->persist($op);
            $manager->flush();

            return $this->redirectToRoute('app_operations');
        }
        return $this->render('operation/add.html.twig', [
            'form' => $form->createView(),
            'operation' => $op
        ]);
    }

    #[Route('/operation/{id}/generate', name: 'generate_operation')]
    public function generate(RecurringOperation $operation, EntityManagerInterface $manager): Response
    {

        $operation->setWantedToRemoveAll(false);
        $this->removeOccursFromOperation($operation);
        $this->loadOperation($operation);
        $manager->persist($operation);
        $manager->flush();

        return $this->redirectToRoute('app_home');

    }

    #[Route('/operation/{id}/removeTransaction', name: 'remove_operations_transaction')]
    public function removeTransaction(RecurringOperation $operation, EntityManagerInterface $manager): Response
    {
        $this->loadOperation($operation);
         $this->removeOccursFromOperation($operation);
        $operation->setWantedToRemoveAll(true);
        $manager->persist($operation);
        $manager->flush();
         return $this->redirectToRoute('app_home');

    }

    #[Route('/operation/validate/{id}/{date}', name: 'validate_operation')]
    public function validate(RecurringOperation $operation, $date): Response
    {
        $date = date('Y/m/d', $date);
        $date = new \DateTimeImmutable($date);
        $this->transactionController->createTransaction($operation, $date);

        return $this->redirectToRoute('app_home');

    }

    public function removeOccursFromOperation($operation)
    {

        $occurs = explode(';', $operation->getOccurences());

        foreach (

            $this->transactionRepository->findBy(['name' => $operation->getName()]) as $transaction) {
            if (in_array($transaction->getDate()->format('Y-m-d'), $occurs) && $operation->getType() == $transaction->getType() ) {

                $this->manager->remove($transaction);
            }
        }
        $this->manager->flush();
        $this->removeOccursListFromOperation($operation);

    }

    public function removeOccursListFromOperation($operation)
    {
        $operation->setOccurences('');

        $this->manager->persist($operation);
        $this->manager->flush();

    }

    public function loadOperations()
    {

        $operations = $this->recurringOperationRepository->findBy(['owner' => $this->getUser()]);

        foreach ($operations as $operation) {
            if(!$operation->isWantedToRemoveAll()){
                $this->loadOperation($operation);
            }
        }

    }

    public function loadOperation($operation)
    {
        //load recurrent operations that adding itself
        $today = new \DateTimeImmutable();
        //get occurences
        $occurs = explode(';', $operation->getOccurences());

        //date to compare
        $dayOfOperation = $operation->getFirstDate();

        $lastDayOfOperation = $operation->getLastDate();

        //assert that the first operation is today or were beforea
        if ($dayOfOperation->format('Y') <= $today->format('Y') && $operation->getAddingType() == 'automatic') {
            //true if $today is date1 < date2 else false
            $resultat = $this->isDateBeforeDate($dayOfOperation, $today);
            $diffBetweenYear = abs($dayOfOperation->format('Y') - $today->format('Y'));

            if ($resultat) {
                $endDate = $today;
                if ($lastDayOfOperation) {
                    if ($this->isDateBeforeDate($lastDayOfOperation, $today)) {
                        $endDate = $lastDayOfOperation;
                    }
                }

                if ($operation->getRepetition() == 'annualy') {
                    //firstOccur(2024-07-02) - Today(2024-08-03)


                    //if first year(2024) and second (2024) -> ($diffBetweenYear = 0)
                    //if first year(2023) and second (2024) -> ($diffBetweenYear = 1)
                    if ($diffBetweenYear == 0) {
                        //because occurence is annualy we want to verifying if first occurs was created because today is the year of first occurence

                        // assert that this operation was not already create
                        if (!in_array($dayOfOperation->format('Y-m-d'), $occurs)) {
                            $this->transactionController->createTransaction($operation, $dayOfOperation);
                        }
                    }

                    //if $diffBetweenYear = 0 so $i > $diffBetweenYear
                    // but if first(2024) and second(2025) -> ($diffBetweenYear = 1)
                    // $i = difference between years
                    for ($i = 0; $i <= $diffBetweenYear; $i++) {
                        $date = new \DateTimeImmutable();
                        $date = $date->setDate($dayOfOperation->format('Y') + $i, $dayOfOperation->format('m'), $dayOfOperation->format('d'));

                        if ($this->isDateBeforeDate($date, $today) && !in_array($date->format('Y-m-d'), $occurs)) {

                            if ($lastDayOfOperation) {
                                if ($this->isDateBeforeDate($date, $lastDayOfOperation)) {
                                    $this->transactionController->createTransaction($operation, $date);

                                } else {
                                }
                            } else {

                                $this->transactionController->createTransaction($operation, $date);
                            }

                        }
                    }

                } elseif ($operation->getRepetition() == 'monthly') {

                    $date = clone $dayOfOperation;
                    //we want to iterat each month since the start
                    for ($year = 0; $year <= $diffBetweenYear; $year++) {

                        //Set the year
                        $date = $date->setDate($date->format('Y') + $year, $date->format('m'), $date->format('d'));
                        for ($month = $date->format('m'); $month <= 12; $month++) {
                            $date = $date->setDate($date->format('Y'), $month, $dayOfOperation->format('d'));

                            //update month
                            if (!in_array($date->format('Y-m-d'), $occurs)) {
                                if ($lastDayOfOperation) {
                                    if ($this->isDateBeforeDate($date, $lastDayOfOperation)) {
                                        $this->transactionController->createTransaction($operation, $date);
                                    }
                                } else {
                                    if ($this->isDateBeforeDate($date, $today)) {

                                        $this->transactionController->createTransaction($operation, $date);
                                    }


                                }
                            }
                        }
                        $date = $date->setDate($date->format('Y'), 1, $dayOfOperation->format('d'));

                    }


                } elseif ($operation->getRepetition() == 'weekly') {


                    $dayOfWeek = $dayOfOperation->format('w');


                    $i = 0;
                    $date = clone $dayOfOperation;
                    while ($this->isDateBeforeDate($date, $endDate)) {
                        $date = $this->getNextOccurrenceDate($dayOfOperation, $dayOfWeek, $i);

                        if ($this->isDateBeforeDate($date, $endDate)) {
                            if (!in_array($date->format('Y-m-d'), $occurs)) {
                                $this->transactionController->createTransaction($operation, $date);
                            }
                        }
                        $i++;
                    }


                } elseif ($operation->getRepetition() == 'daily') {
                    $date = clone $dayOfOperation;
                    while ($this->isDateBeforeDate($date, $endDate)) {

                        $date->modify('+1 day');
                        if ($this->isDateBeforeDate($date, $endDate)) {

                            if (!in_array($date->format('Y-m-d'), $occurs)) {
                                $this->transactionController->createTransaction($operation, $date);
                            }
                        }

                    }


                }
            }

        }
    }

    public function getUnValidateOperations()
    {
        //get operation witch must be validate
        $today = new \DateTimeImmutable();
        $operations = $this->recurringOperationRepository->findBy(['owner' => $this->getUser()]);
        $unvalidateOperations = [];
        foreach ($operations as $operation) {

            //get occurences
            $occurs = explode(';', $operation->getOccurences());

            //date to compare
            $dayOfOperation = $operation->getFirstDate();

            $lastDayOfOperation = $operation->getLastDate();

            //assert that the first operation is today or were beforea
            if ($dayOfOperation->format('Y') <= $today->format('Y') && $operation->getAddingType() == 'manual') {
                //true if $today is date1 < date2 else false
                $resultat = $this->isDateBeforeDate($dayOfOperation, $today);
                $diffBetweenYear = abs($dayOfOperation->format('Y') - $today->format('Y'));

                if ($resultat) {
                    $endDate = $today;
                    if ($lastDayOfOperation) {
                        if ($this->isDateBeforeDate($lastDayOfOperation, $today)) {
                            $endDate = $lastDayOfOperation;
                        }
                    }

                    if ($operation->getRepetition() == 'annualy') {
                        if ($diffBetweenYear == 0) {
                            if (!in_array($dayOfOperation->format('Y-m-d'), $occurs)) {
                                $unvalidateOperations [] = $this->createTransactionToValidate($operation, $dayOfOperation);
                            }
                        }

                        //if $diffBetweenYear = 0 so $i > $diffBetweenYear
                        // but if first(2024) and second(2025) -> ($diffBetweenYear = 1)
                        // $i = difference between years
                        for ($i = 0; $i <= $diffBetweenYear; $i++) {
                            $date = new \DateTimeImmutable();
                            $date = $date->setDate($dayOfOperation->format('Y') + $i, $dayOfOperation->format('m'), $dayOfOperation->format('d'));

                            if ($this->isDateBeforeDate($date, $today) && !in_array($date->format('Y-m-d'), $occurs)) {

                                if ($lastDayOfOperation) {
                                    if ($this->isDateBeforeDate($date, $lastDayOfOperation)) {
                                        $unvalidateOperations [] = $this->createTransactionToValidate($operation, $date);

                                    }
                                } else {

                                    $unvalidateOperations [] = $this->createTransactionToValidate($operation, $date);
                                }

                            }
                        }

                    } elseif ($operation->getRepetition() == 'monthly') {

                        $date = clone $dayOfOperation;
                        //we want to iterat each month since the start
                        for ($year = 0; $year <= $diffBetweenYear; $year++) {

                            //Set the year
                            $date = $date->setDate($date->format('Y') + $year, $date->format('m'), $date->format('d'));
                            for ($month = $date->format('m'); $month <= 12; $month++) {
                                $date = $date->setDate($date->format('Y'), $month, $dayOfOperation->format('d'));

                                //update month
                                if (!in_array($date->format('Y-m-d'), $occurs)) {
                                    if ($lastDayOfOperation) {
                                        if ($this->isDateBeforeDate($date, $lastDayOfOperation)) {
                                            $unvalidateOperations [] = $this->createTransactionToValidate($operation, $date);
                                        }
                                    } else {
                                        if ($this->isDateBeforeDate($date, $today)) {

                                            $unvalidateOperations [] = $this->createTransactionToValidate($operation, $date);

                                        }


                                    }
                                }
                            }
                            $date = $date->setDate($date->format('Y'), 1, $dayOfOperation->format('d'));

                        }


                    } elseif ($operation->getRepetition() == 'weekly') {


                        $dayOfWeek = $dayOfOperation->format('w');


                        $i = 0;
                        $date = clone $dayOfOperation;
                        while ($this->isDateBeforeDate($date, $endDate)) {
                            $date = $this->getNextOccurrenceDate($dayOfOperation, $dayOfWeek, $i);

                            if ($this->isDateBeforeDate($date, $endDate)) {
                                if (!in_array($date->format('Y-m-d'), $occurs)) {
                                    $unvalidateOperations [] = $this->createTransactionToValidate($operation, $date);
                                }
                            }
                            $i++;
                        }


                    } elseif ($operation->getRepetition() == 'daily') {
                        $date = clone $dayOfOperation;
                        while ($this->isDateBeforeDate($date, $endDate)) {

                            $date->modify('+1 day');
                            if ($this->isDateBeforeDate($date, $endDate)) {

                                if (!in_array($date->format('Y-m-d'), $occurs)) {
                                    $unvalidateOperations[] = $this->createTransactionToValidate($operation, $date);

                                }
                            }

                        }


                    }
                    dump('ni annuel ni mensuel');
                }
                dump('date supérieur');

            }
        }
        dump('aucune opération');
        return ($unvalidateOperations);
    }

    private function isDateBeforeDate($date1, $date2)
    {
        $diffBetweenYear = abs($date2->format('Y') - $date1->format('Y'));

        if ($diffBetweenYear == 0) {
            if ($date1->format('m') == $date2->format('m')) {
                if ($date1->format('d') > $date2->format('d')) {
                    return false;
                }
            }
            if ($date1->format('m') > $date2->format('m')) {
                return false;
            }
        }
        return true;
    }

    private function getNextOccurrenceDate($date, int $dayOfWeek, int $weekOffset): DateTime
    {
        $date = clone $date;
        $date = $date->setISODate($date->format('Y'), $date->format('W') + $weekOffset);

        if ($dayOfWeek == 0) {
            $date = $date->modify('+6 day');
        } else {
            $date = $date->modify('+' . ($dayOfWeek - 1) . ' day');
        }

        return $date;
    }


    private function createTransactionToValidate(RecurringOperation $operation, $date)
    {
        $date = clone $date;
        return [
            'op' => $operation,
            'date' => $date,
            'timestamp' => $date->getTimestamp()

        ];
    }

    #[Route('/operation/{id}/remove', name: 'remove_operation')]
    public function remove(RecurringOperation $operation, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->getUser() != $operation->getOwner()) {
            return $this->redirectToRoute('app_operations');
        }
        $manager->remove($operation);
        $manager->flush();
        return $this->redirectToRoute('app_operations');
    }
}
