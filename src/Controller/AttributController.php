<?php

namespace App\Controller;

use App\Entity\Attribut;
use App\Form\AttributType;
use App\Repository\AttributRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attribut")
 */
class AttributController extends AbstractController
{
    /**
     * @Route("/", name="attribut_index", methods={"GET"})
     */
    public function index(AttributRepository $attributRepository): Response
    {
        return $this->render('attribut/index.html.twig', [
            'attributs' => $attributRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="attribut_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $attribut = new Attribut();
        $form = $this->createForm(AttributType::class, $attribut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($attribut);
            $entityManager->flush();

            return $this->redirectToRoute('attribut_index');
        }

        return $this->render('attribut/new.html.twig', [
            'attribut' => $attribut,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="attribut_show", methods={"GET"})
     */
    public function show(Attribut $attribut): Response
    {
        return $this->render('attribut/show.html.twig', [
            'attribut' => $attribut,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="attribut_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Attribut $attribut): Response
    {
        $form = $this->createForm(AttributType::class, $attribut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('attribut_index');
        }

        return $this->render('attribut/edit.html.twig', [
            'attribut' => $attribut,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="attribut_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Attribut $attribut): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attribut->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($attribut);
            $entityManager->flush();
        }

        return $this->redirectToRoute('attribut_index');
    }
}
