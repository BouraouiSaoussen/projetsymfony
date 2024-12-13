<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'app_front')]
    public function index(CategoryRepository $cr, PostRepository $pd): Response
    {
        // dump die : affiche sur la page puis il tue le processus
        // dd ($cr-> findAll());

        return $this->render('acceuil/index.html.twig', [
          'categories' => $cr-> findAll(),
          'post'=> $pd-> findAll()
   
        ]);
      }
        #[Route('/item/{id}', name: 'app_item')]
    public function item(Post $pd): Response
    {
        // dump die : affiche sur la page puis il tue le processus
        // dd ($cr-> findAll());

        return $this->render('acceuil/item.html.twig', [
          'post'=> $pd
   
        ]);

    }
}
