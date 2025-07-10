<?php

declare(strict_types=1);

namespace Azimut\Bundle\MontgolfiereAppBundle\Command;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAnalysisVersionCommand extends ContainerAwareCommand
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
            ->setName('montgolfiere:analysis-version:create')
            ->addArgument('sourceVersion', InputArgument::OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $version = $input->getArgument('sourceVersion');
        $repository = $this->entityManager->getRepository(AnalysisVersion::class);
        $analysisVersion = $version ? $repository->find($version) : $repository->getLastVersion();
        if(!$analysisVersion) {
            $io->error('Unable to find version '.$version);

            return 1;
        }

        $newVersion = clone $analysisVersion;
        $this->entityManager->persist($newVersion);
        $this->entityManager->flush();

        $themes = $this->entityManager->getRepository(Theme::class)->findBy(['analysisVersion' => $analysisVersion]);
        foreach ($themes as $theme) {
            $newTheme = clone $theme;
            $newTheme->setAnalysisVersion($newVersion);
            $this->entityManager->persist($newTheme);

            foreach ($theme->getRestitutionItems() as $restitutionItem) {
                $newRestitutionItem = clone $restitutionItem;
                $newRestitutionItem->setTheme($newTheme);
                $this->entityManager->persist($newRestitutionItem);
            }

            foreach ($theme->getItems() as $item) {
                $newItem = clone $item;
                $newItem->setTheme($newTheme)->setAnalysisVersion($newVersion);
                $this->entityManager->persist($newItem);

                foreach ($item->getRestitution() as $restitution) {
                    $newRestitution = clone $restitution;
                    $newRestitution->setItem($newItem);
                    $this->entityManager->persist($newRestitution);
                }

                foreach ($item->getTooltips() as $tooltip) {
                    $newTooltip = clone $tooltip;
                    $newTooltip->setItem($newItem);
                    $this->entityManager->persist($newTooltip);
                }

                foreach ($item->getQuestions() as $question) {
                    $newQuestion = clone $question;
                    $newQuestion->setItem($newItem)->setAnalysisVersion($newVersion);
                    $this->entityManager->persist($newQuestion);
                }
            }
        }

        $itemlessQuestions = $this->entityManager->getRepository(Question::class)->findBy(['analysisVersion' => $analysisVersion, 'item' => null]);
        foreach ($itemlessQuestions as $question) {
            $newQuestion = clone $question;
            $newQuestion->setAnalysisVersion($newVersion);
            $this->entityManager->persist($newQuestion);
        }

        $this->entityManager->flush();

        $io->success('Created version '.$newVersion->getId().' (from version '.$analysisVersion->getId().')');

        return 0;
    }
}
