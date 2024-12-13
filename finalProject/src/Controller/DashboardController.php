<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Repository\PostRepository;


class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
   
    public function index(PostRepository $postRepository): Response
    {
        // Récupérer tous les posts avec leurs auteurs
        $posts = $postRepository->findAll();

        return $this->render('dashboard/panel.html.twig', [
            'posts' => $posts,
        ]);
}
}