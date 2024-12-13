<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
 

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère l'image  uploadé
            

            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();

            if ($file) {
                // Obtient le nom original du fichier
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // Crée un nom sécurisé pour le fichier
                $safeFilename = $slugger->slug($originalFilename);
                // Génère un nouveau nom de fichier unique
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                // Tente de déplacer le fichier dans le dossier de destination
                try {
                    $file->move(
                        $this->getParameter('upload_directory'), // Utilise le paramètre défini dans config/services.yaml
                        $newFilename
                    );
                } catch (NoFileException $e) {
                    // Gère l'exception si une erreur survient
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image : ' . $e->getMessage());
                }
                // Sauvegarde le nom du fichier dans l'entité
                $post->setImage($newFilename);
            }
              // Après avoir déplacé l'image
               $this->addFlash('success', 'Article ajouté/modifié avec succès!');
             // Enregistre le nouveau post dans la base de données
              $author = $form->get('author')->getData();
              $post->setAuthor($author); // Set the author based on the selected user       


            $entityManager->persist($post);
            $entityManager->flush();
            // Redirection vers la liste des articles
            return $this->redirectToRoute('app_post_index');
        }

        // Retourne la vue avec le formulaire pour créer un article
        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);

           
        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
