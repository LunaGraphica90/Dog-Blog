<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * La page d'accueil montre la liste des Post
     * 
     * @Route("/", name="post_browse")
     */
    public function browse(PostRepository $postRepository): Response
    {
        return $this->render('post/browse.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    /**
     * On a utilisé en tout premier le PostRepository en paramètre pour lui demander de retrouver
     * l'objet Post grace à l'id dans le chemin
     * Mais on peut faire plus simple. Le ParamConverter de Symfony sait retrouver un objet de la classe Post
     * à partir de l'id. Il va faire le lien entre le paramètre {id} et le fait
     * que notre classe Post a une propriété $id
     * Il va demander à Doctrine de fournir l'objet Post pour exécuter le controleur.
     * 
     * Si l'objet Post avec l'id demandé n'existe pas en BDD, le Kernel va lui-même générer une 404
     * Nous n'avons pas à lever systématiquement une 404 dans nos controleurs,
     * on peut confier cette tache au Kernel
     * 
     * Dans certains cas, peut-être, on souhaite générer nous-même l'erreur car
     * on souhaite intervenir différemment du coomportement normal de Symfony
     * 
     * @Route("/post/{id}", name="post_read", requirements={"id": "\d+"}, methods={"GET"})
     */
    public function read(Post $post)
    {
        return $this->render("post/read.html.twig", [
            'post' => $post,
        ]);
    }

        /**
     * On va ajouter un article à la base de données, sans formulaire
     * Pour bien comprendre le fonctionnement de Doctrine,
     * on crée un objet dont les valeurs sont en dur
     * 
     * @Route("/post/add", name="post_add")
     */
    public function add()
    {
        // Il nous un objet Post à mettre en BDD
        $post = new Post();
        $post->setTitle('Le caca des pigeons c\'est caca ! Faut pas manger !');
        $post->setCreatedAt(new \DateTime());

        // Pour écrire des données en BDD, on utilise l'EntityManager
        // Il contient tout le nécessaire pour savoir si un objet a été modifié, s'il est nouveau
        // ou s'il faut le supprimer
        $em = $this->getDoctrine()->getManager();

        // persist() indique à l'EntityManager qu'on souhaite qu'il prenne en charge cet objet
        // Il va mettre dans une variable l'information comme quoi c'est un nouvel objet à insérer en BDD
        // Aucune requ'ete SQL n'est exécutée à ce stade
        $em->persist($post);
        // flush() va exécuter plusieurs requêtes si nécessaire
        // Cette fonction va regarder toutes les modifications à appliquer en BDD et exécuter les requêtes SQL
        $em->flush();

        return $this->redirectToRoute('post_browse');
    }

    /**
     * @Route("/post/edit/{id}", name="post_edit", requirements={"id": "\d+"})
     */
    public function edit(Post $post)
    {
        $post->setTitle('Yatta !');
        $post->setUpdatedAt(new \DateTime());

        // $em = $this->getDoctrine()->getManager();
        // $em->flush();
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('post_browse');
    }

    /**
     * @Route("/post/delete/{id}", name="post_delete", requirements={"id": "\d+"})
     */
    public function delete(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('post_browse');
    }
}
