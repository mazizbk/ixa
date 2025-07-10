<?php

declare(strict_types=1);

namespace Azimut\Bundle\MontgolfiereAppBundle\Command;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItem;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItemTableText;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Tooltip;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteAnalysisVersionCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('montgolfiere:analysis-version:delete')
            ->addArgument('version', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $version = $input->getArgument('version');
        $analysisVersion = $this->entityManager->getRepository(AnalysisVersion::class)->find($version);
        if(!$analysisVersion) {
            $io->error('Unable to find version '.$version);

            return 1;
        }

        if (!$io->confirm('Are you sure you want to delete version '.$version.' and all its associated data. THIS INCLUDES CAMPAIGNS AND ANSWERS!', false)) {
            return 1;
        }

        $deleteByItem = $this->entityManager->createQueryBuilder()
            ->select('i')
            ->from(Item::class, 'i')
            ->where('i.analysisVersion = :analysisVersion')
            ->getQuery()
            ->getDQL()
        ;
        $deleteByTheme = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Theme::class, 't')
            ->where('t.analysisVersion = :analysisVersion')
            ->getQuery()
            ->getDQL()
        ;

        $this->entityManager->createQueryBuilder()
            ->delete(Tooltip::class, 'e')
            ->where('e.item IN('.$deleteByItem.')')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;
        $this->entityManager->createQueryBuilder()
            ->delete(RestitutionItemTableText::class, 'e')
            ->where('e.item IN('.$deleteByItem.')')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;
        $this->entityManager->createQueryBuilder()
            ->delete(RestitutionItem::class, 'e')
            ->where('e.theme IN('.$deleteByTheme.')')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;
        $this->entityManager->createQueryBuilder()
            ->delete(Campaign::class, 'e')
            ->where('e.analysisVersion = :analysisVersion')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;
        $this->entityManager->createQueryBuilder()
            ->delete(Question::class, 'e')
            ->where('e.analysisVersion = :analysisVersion')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;
        $this->entityManager->createQueryBuilder()
            ->delete(Item::class, 'e')
            ->where('e.analysisVersion = :analysisVersion')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;
        $this->entityManager->createQueryBuilder()
            ->delete(Theme::class, 'e')
            ->where('e.analysisVersion = :analysisVersion')
            ->setParameter('analysisVersion', $analysisVersion)
            ->getQuery()->execute()
        ;

        $this->entityManager->remove($analysisVersion);
        $this->entityManager->flush();

        return 0;
    }
}
