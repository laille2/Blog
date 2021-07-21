<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    }

    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            "articles" => $articles
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$article) {
            $article = new Article();
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTimeImmutable());
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create.html.twig', [
            'formArticle' =>  $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id_article}", name="blog_show")
     * @Route("/blog/{id_article}/modify_comment/{id_comment}", name="blog_modify_comment")
     */
    public function commentForm($id_article, int $id_comment = null, Request $request, EntityManagerInterface $manager)
    {
        $article = $manager->getRepository(Article::class)->find($id_article);
        $comment = new Comment();
        
        if ($id_comment) {
            $comment = $manager->getRepository(Comment::class)->find($id_comment);
        }

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$comment->getId()) {
                $comment->setCreatedAt(new \DateTimeImmutable());
                $comment->setArticle($article);
            }

            $article->addComment($comment);

            $manager->persist($comment);
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id_article' => $id_article
            ]);
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'formComment' =>  $form->createView(),
            'editMode' => $comment->getId() !== null
        ]);
    }
}
