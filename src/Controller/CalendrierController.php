<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CalendrierController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendrier')]
    public function index(Request $request, InterventionRepository $interventionRepository): Response
    {
        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        // Récupérer le mois et l'année depuis la requête (ou utiliser le mois actuel)
        $month = $request->query->getInt('month', (int)date('m'));
        $year = $request->query->getInt('year', (int)date('Y'));

        // Récupérer toutes les interventions du mois
        if ($isAdmin) {
            $interventions = $interventionRepository->findInterventionsByMonth($year, $month);
        } else {
            $interventions = $interventionRepository->findInterventionsByMonthAndUser($year, $month, $user->getId());
        }

        // Formater les interventions pour le calendrier
        $events = [];
        foreach ($interventions as $intervention) {
            $events[] = [
                'id' => $intervention->getId(),
                'title' => $intervention->getLibelle(),
                'start' => $intervention->getDateDebut()->format('Y-m-d H:i:s'),
                'end' => $intervention->getDateFin() ? $intervention->getDateFin()->format('Y-m-d H:i:s') : null,
                'backgroundColor' => $this->getStatusColor($intervention->getStatus()),
                'client' => $intervention->getClient() ? $intervention->getClient()->getNom() : 'Non défini',
                'status' => $intervention->getStatus()->label(),
            ];
        }

        return $this->render('calendrier/index.html.twig', [
            'events' => $events,
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function getStatusColor($status): string
    {
        return match($status->value) {
            'Planifer' => '#3b82f6', // blue
            'En cours' => '#f59e0b', // yellow
            'Terminer' => '#10b981', // green
            'Annuler' => '#ef4444', // red
            default => '#6b7280', // gray
        };
    }
}
