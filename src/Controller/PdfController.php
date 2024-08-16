<?php

namespace App\Controller;

use App\Entity\RecurringOperation;
use App\Form\OperationType;
use App\Form\PdfType;
use App\Repository\PaymentRepository;
use App\Repository\PdfRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PdfController extends AbstractController
{
    private $transactionRepository;
    private $months;
    private $paymentsRepository;

    public function __Construct(TransactionRepository $transactionRepository, PaymentRepository $paymentRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->months = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
        $this->paymentsRepository = $paymentRepository;
    }

    #[Route('/pdf', name: 'app_pdf')]
    public function pdf(Pdf $knpSnappyPdf, Request $request, EntityManagerInterface $manager, PdfRepository $repository)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if ($repository->count(['owner'=>$this->getUser()]) == 0) {
            $pdf = new \App\Entity\Pdf();
        } else {
            $pdf = $repository->findBy(['owner'=>$this->getUser()])[0];
        }

        $transactions = $this->getAllTransactions();

        $form = $this->createForm(PdfType::class, $pdf);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->getUser()->getEmail();
            if ($pdf->getEntiteName()) {
                $userName = $pdf->getEntiteName();
            }
            $pdf->setEntiteName($userName);
            $pdf->setOwner($this->getUser());
            $manager->persist($pdf);
            $manager->flush();


            $html = $this->renderView('/component/desktop/pdf.html.twig', [
                'name' => $pdf->getEntiteName(),
                'incomeOutcome' => $pdf->isMensuelDetails(),
                'walletName' => $pdf->isNames(),
                'data' => $transactions,
                'months'=>$this->months,
                'payments'=>$this->paymentsRepository->findBy(['owner'=>$this->getUser()],['type'=>'ASC'])
            ]);
  /*          dd($transactions);*/
            $pdfFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . uniqid() . '.pdf';

            // Générer le PDF
            $knpSnappyPdf->generateFromHtml($html, $pdfFilePath);

            return $this->render('/home/pdf.html.twig', [
                'form' => $form->createView(),
                'pdfPath' => '/uploads/pdf/' . basename($pdfFilePath),
                'pdf' => $pdf,
            ]);
        }

        return $this->render('/home/pdf.html.twig', [
            'form' => $form->createView()
        ]);


    }

    #[Route('/pdf/download/{id}', name: 'download_pdf')]
    public function dowloadPdf(Pdf $knpSnappyPdf, \App\Entity\Pdf $pdf)
    {

        $transactions = $this->getAllTransactions();
        $html = $this->renderView('/component/desktop/pdf.html.twig', [
            'name' => $pdf->getEntiteName(),
            'incomeOutcome' => $pdf->isMensuelDetails(),
            'walletName' => $pdf->isNames(),
            'data' => $transactions,

            'payments'=>$this->paymentsRepository->findBy(['owner'=>$this->getUser()],['type'=>'ASC'])
          ,
            'months'=>$this->months
        ]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    public function getAllTransactions()
    {
        $data = [];
        $transactions = $this->transactionRepository->findBy(['owner'=>$this->getUser()], ['date' => 'ASC']);

        foreach ($transactions as $transaction) {
            $year = $transaction->getDate()->format('Y');
            $month = $transaction->getDate()->format('m');

            if (!isset($data[$year])) {
                $data[$year] = ['total' => 0, 'months' => []];
            }
            if (!isset($data[$year]['months'][$month])) {
                $data[$year]['months'][$month] = [
                    'total' => 0,
                    'incomes' => [
                        'total' => 0,
                        'number'=>0
                    ],
                    'outcomes' => [
                        'total' => 0,
                        'number'=>0
                    ],
                ];
            }
            if($transaction->getType()=='income'){
                $data[$year]['total'] += $transaction->getMontant();
                $data[$year]['months'][$month]['total'] += $transaction->getMontant();
                $data[$year]['months'][$month]['incomes']['total'] += $transaction->getMontant();
                $data[$year]['months'][$month]['incomes']['number'] += 1;

            }
            else{
                $data[$year]['total'] -= $transaction->getMontant();
                $data[$year]['months'][$month]['total'] -= $transaction->getMontant();
                $data[$year]['months'][$month]['outcomes']['total'] += $transaction->getMontant();
                $data[$year]['months'][$month]['outcomes']['number'] += 1;
            }
        }


        return $data;
    }
}
