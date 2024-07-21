<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    private $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;

    }

    #[Route('/category', name: 'app_category')]
    public function index(Request $request, EntityManagerInterface $manager, CategoryRepository $repository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setOwner($this->getUser());
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/index.html.twig', [
            'form' => $form->createView(),
            "categoriesIncome" => $repository->findBy(['type' => 'income', 'owner' => $user]),
            "categoriesOutcome" => $repository->findBy(['type' => 'outcome', 'owner' => $user])
        ]);
    }

    #[Route('/category/{id}/remove', name: 'remove_category')]
    public function remove(EntityManagerInterface $manager, Category $category): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if (count($category->getTransactions()) == 0 && $category->getOwner() == $this->getUser()) {
            $manager->remove($category);
            $manager->flush();
        }
        return $this->redirectToRoute('app_category');
    }

    #[Route('/category/{id}/edit', name: 'edit_category')]
    public function edit(Category $category, Request $request, EntityManagerInterface $manager, CategoryRepository $repository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if ($category->getOwner() != $this->getUser()) {
            return $this->redirectToRoute('app_category');
        }
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/index.html.twig', [
            'form' => $form->createView(),
            'category' => $category,

            "categoriesIncome" => $repository->findBy(['type' => 'income', 'owner' => $user]),
            "categoriesOutcome" => $repository->findBy(['type' => 'outcome', 'owner' => $user])
        ]);
    }

    #[Route('/category/default-montant/{id}', name: 'get_default_montant')]
    public function defaultMontant(Category $category): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->json([
            'montant' => $category->getMontant()
        ]);
    }

    public function total($id)
    {
        $cat = $this->repository->find($id);
        $count = 0;
        foreach ($cat->getTransactions() as $transaction) {
            $count += $transaction->getMontant();
        }
        return $count;
    }
}
