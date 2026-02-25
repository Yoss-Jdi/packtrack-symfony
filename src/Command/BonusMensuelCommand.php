<?php

namespace App\Command;

use App\Entity\Recompense;
use App\Entity\Role;  // ⬅️ AJOUTER
use App\Repository\UtilisateursRepository;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recompenses:bonus-mensuel',
    description: 'Attribuer automatiquement les bonus mensuels aux livreurs'
)]
class BonusMensuelCommand extends Command
{
    public function __construct(
        private UtilisateursRepository $utilisateursRepository,
        private LivraisonRepository $livraisonRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('🎁 Attribution des bonus mensuels');

        // Récupérer tous les livreurs (avec l'Enum)
        $livreurs = $this->utilisateursRepository->findBy(['role' => Role::LIVREUR]);  // ⬅️ CORRIGÉ
        
        if (empty($livreurs)) {
            $io->warning('Aucun livreur trouvé dans la base de données.');
            return Command::SUCCESS;
        }

        // Mois précédent
        $debutMois = new \DateTime('first day of last month');
        $finMois = new \DateTime('last day of last month');
        
        $io->note(sprintf(
            'Période analysée : %s au %s',
            $debutMois->format('d/m/Y'),
            $finMois->format('d/m/Y')
        ));

        $bonusCrees = 0;
        $resultats = [];

        foreach ($livreurs as $livreur) {
            // Compter les livraisons du mois précédent
            $nombreLivraisons = $this->livraisonRepository->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->where('l.livreur = :livreur')
                ->andWhere('l.statut = :statut')
                ->andWhere('l.dateFin BETWEEN :debut AND :fin')
                ->setParameter('livreur', $livreur)
                ->setParameter('statut', 'termine')
                ->setParameter('debut', $debutMois)
                ->setParameter('fin', $finMois)
                ->getQuery()
                ->getSingleScalarResult();

            // Déterminer le bonus selon les critères
            $montantBonus = 0;
            $description = '';

            if ($nombreLivraisons >= 100) {
                $montantBonus = 200;
                $description = "Bonus mensuel : {$nombreLivraisons} livraisons (≥100)";
            } elseif ($nombreLivraisons >= 50) {
                $montantBonus = 100;
                $description = "Bonus mensuel : {$nombreLivraisons} livraisons (≥50)";
            }

            // Créer la récompense si méritée
            if ($montantBonus > 0) {
                $recompense = new Recompense();
                $recompense->setType('Bonus Mensuel');
                $recompense->setValeur($montantBonus);
                $recompense->setDescription($description);
                $recompense->setLivreur($livreur);
                $recompense->setSeuil($nombreLivraisons);
                $recompense->setDateObtention(new \DateTime());

                $this->entityManager->persist($recompense);
                $bonusCrees++;

                $resultats[] = [
                    'Livreur' => $livreur->getPrenom() . ' ' . $livreur->getNom(),
                    'Livraisons' => $nombreLivraisons,
                    'Bonus' => $montantBonus . ' DT',
                    'Statut' => '✅'
                ];
            } else {
                $resultats[] = [
                    'Livreur' => $livreur->getPrenom() . ' ' . $livreur->getNom(),
                    'Livraisons' => $nombreLivraisons,
                    'Bonus' => '-',
                    'Statut' => '⚪'
                ];
            }
        }

        $this->entityManager->flush();
        
        // Afficher le tableau des résultats
        $io->table(
            ['Livreur', 'Livraisons', 'Bonus', 'Statut'],
            $resultats
        );

        $io->success(sprintf('%d bonus mensuels créés avec succès !', $bonusCrees));

        return Command::SUCCESS;
    }
}