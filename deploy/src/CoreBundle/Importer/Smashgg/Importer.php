<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg;

use CoreBundle\Entity\Country;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\AbstractImporter;
use CoreBundle\Service\Smashgg\Smashgg;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer extends AbstractImporter
{
    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @param SymfonyStyle  $io
     * @param EntityManager $entityManager
     * @param Smashgg       $smashgg
     */
    public function __construct(SymfonyStyle $io, EntityManager $entityManager, Smashgg $smashgg)
    {
        $this->setIo($io);
        $this->setEntityManager($entityManager);
        $this->smashgg = $smashgg;
    }

    /**
     * @param string $smashggId
     * @param array  $eventIds
     */
    public function import($smashggId, $eventIds)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $tournament = $this->getTournament($smashggId);

        $this->entityManager->flush();
    }

    /**
     * @param string $slug
     * @return Tournament
     */
    protected function getTournament($slug)
    {
        $smashggTournament = $this->smashgg->getTournament($slug);
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'smashggSlug' => $slug,
        ]);

        if (!$tournament instanceof Tournament) {
            $tournament = new Tournament();
            $tournament->setSmashggSlug($slug);
            $tournament->setIsActive(true);
            $tournament->setIsComplete(true);

            $this->entityManager->persist($tournament);
        }

        $dateStart = new \DateTime();
        $dateStart->setTimestamp($smashggTournament['startAt']);

        $tournament->setName($smashggTournament['name']);
        $tournament->setCity($smashggTournament['city']);
        $tournament->setCountry($this->findCountry(null, $smashggTournament['countryCode']));
        $tournament->setDateStart($dateStart);

        return $tournament;
    }

    /**
     * @param string $name
     * @param string $code
     * @return Country|null
     */
    protected function findCountry($name, $code = null)
    {
        $countryRepository = $this->getRepository('CoreBundle:Country');

        if ($code) {
            $country = $countryRepository->findOneBy([
                'code' => $code,
            ]);

            if ($country instanceof Country) {
                return $country;
            }
        }

        $country = $countryRepository->findOneBy([
            'name' => $name,
        ]);

        if ($country instanceof Country) {
            return $country;
        }

        $name = 'The '.$name;

        return $countryRepository->findOneBy([
            'name' => $name,
        ]);
    }
}
