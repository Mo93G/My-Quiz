<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\QuizzRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(CategorieRepository $categorieRepository, QuizzRepository $quizzRepository, Request $request): Response
    {   
        // $request->get('m')
        return $this->render('accueil/index.html.twig', [

            'controller_name' => 'AccueilController',
            'categories' => $categorieRepository->findBy([], ["id" => "asc"]),
            'request' => $request,
            'quiz' => $quizzRepository->findBy([], ["id" => "asc"]),
        ]);
    }
}
