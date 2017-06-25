<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\Post;

/**
 * blog controller.
 *
 * @Route("blog")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="blog_index")
     */
    public function indexAction()
    {
    	$repository = $this->getDoctrine()->getRepository('BlogBundle:Post');
    	$posts = $repository->findAll();

        return $this->render('BlogBundle::index.html.twig',array(
            'posts' => $posts,
        ));
    }

    /**
     * @Route("/{slug}", name="blog_show")
     */
    public function showAction($slug, Request $request)
    {
    	$repository = $this->getDoctrine()->getRepository('BlogBundle:Post');
    	$post = $repository->findBy(array("slug" => $slug));

    	$repositoryComment = $this->getDoctrine()->getRepository('BlogBundle:Comment');
    	$commentsByPost = $repositoryComment->findBy(array("post" => $post[0]));

    	$comment = new Comment();
        $form = $this->createForm('BlogBundle\Form\CommentType', $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        	$comment->setPost($post[0]);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('blog_show', array("slug" => $slug));
        }

        return $this->render('BlogBundle:Post:post.html.twig',array(
            'post' => $post,
            'comment' => $comment,
            'commentsByPost' => $commentsByPost,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing post entity.
     *
     * @Route("/{slug}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);
        $editForm = $this->createForm('BlogBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_edit', array('slug' => $post->getSlug()));
        }

        return $this->render('post/edit.html.twig', array(
            'post' => $post,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/category/{slug}", name="blog_show_by_category")
     */
    public function showByCategoryAction($slug)
    {
    	$repository = $this->getDoctrine()->getRepository('BlogBundle:Post');
    	$posts = $repository->findByCategory($slug);

        return $this->render('BlogBundle::index.html.twig',array(
            'posts' => $posts,
        ));
    }

    public function deleteAction(Request $request, Post $post)
    {
        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        $repositoryComment = $this->getDoctrine()->getRepository('BlogBundle:Comment');
        $commentsByPost = $repositoryComment->findBy(array("post" => $post[0]));

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($commentsByPost);
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('post_index');
    }

    /**
     * Creates a form to delete a post entity.
     *
     * @param Post $post The post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
