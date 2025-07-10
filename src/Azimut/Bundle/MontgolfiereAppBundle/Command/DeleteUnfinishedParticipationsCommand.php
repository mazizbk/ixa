<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Command;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUnfinishedParticipationsCommand extends ContainerAwareCommand
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
            ->setName('montgolfiere:delete-unfinished-participations')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete(CampaignParticipation::class, 'cp')
            ->where('cp.finished = false')
            ->andWhere('cp.updatedAt < :date')
            ->setParameter(':date', new \DateTime('2 days ago'), Type::DATETIME)
            ->getQuery()
            ->execute()
        ;

        return 0;
    }
}
