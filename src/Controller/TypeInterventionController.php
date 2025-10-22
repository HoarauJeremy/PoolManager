<?php

namespace App\Controller;

use App\Entity\TypeIntervention;
use App\Form\TypeInterventionType;
use App\Repository\TypeInterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/intervention')]
final class TypeInterventionController extends AbstractController
{
    #[Route(name: 'app_type_intervention_index', methods: ['GET'])]
    public function index(TypeInterventionRepository $typeInterventionRepository): Response
    {
        return $this->render('type_intervention/index.html.twig', [
            'type_interventions' => $typeInterventionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeIntervention = new TypeIntervention();
        $form = $this->createForm(TypeInterventionType::class, $typeIntervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeIntervention);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_intervention/new.html.twig', [
            'type_intervention' => $typeIntervention,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_intervention_show', methods: ['GET'])]
    public function show(TypeIntervention $typeIntervention): Response
    {
        return $this->render('type_intervention/show.html.twig', [
            'type_intervention' => $typeIntervention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeIntervention $typeIntervention, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeInterventionType::class, $typeIntervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_intervention/edit.html.twig', [
            'type_intervention' => $typeIntervention,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, TypeIntervention $typeIntervention, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        if ($this->isCsrfTokenValid('delete'.$typeIntervention->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeIntervention);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}
