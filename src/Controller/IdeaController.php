<?php

namespace App\Controller;

use App\Entity\Idea;
use App\Form\IdeaType;
use App\Repository\IdeaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IdeaController extends Controller
{
    /**
     * @Route("/admin")
     */
    public function admin()
    {
        return $this->redirectToRoute('idea_index');
    }

    /**
     * @Route("/", name="idea_index", methods="GET|POST")
     */
    public function index(Request $request, IdeaRepository $ideaRepository): Response
    {
        $idea = new Idea();
        $form = $this->createForm(IdeaType::class, $idea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $idea->setDate(new \DateTime());
            $idea->setUpvote(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($idea);
            $em->flush();
        }

        return $this->render('idea/index.html.twig', [
            'ideas' => $ideaRepository->findBy(array(), array('id' => 'DESC')),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/upvote", name="idea_upvote", methods="GET")
     */
    public function upvote(Idea $idea): Response
    {
        $idea->setUpvote($idea->getUpvote() + 1);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('idea_index');
    }

    /**
     * @Route("/{id}/unvote", name="idea_unvote", methods="GET")
     */
    public function unvote(Idea $idea): Response
    {
        if ($idea->getUpvote() > 0) {
            $idea->setUpvote($idea->getUpvote() - 1);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('idea_index');
    }

    /**
     * @Route("admin/{id}/edit", name="idea_edit", methods="GET|POST")
     */
    public function edit(Request $request, Idea $idea): Response
    {
        $form = $this->createForm(IdeaType::class, $idea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('idea_index');
        }

        return $this->render('idea/edit.html.twig', [
            'idea' => $idea,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("admin/{id}", name="idea_delete", methods="DELETE")
     */
    public function delete(Request $request, Idea $idea): Response
    {
        if ($this->isCsrfTokenValid('delete'.$idea->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($idea);
            $em->flush();
        }

        return $this->redirectToRoute('idea_index');
    }
}
