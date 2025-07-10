<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-06 17:06:44
 */

namespace Azimut\Bundle\FrontofficeBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\ORM\EntityManagerInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;
use Azimut\Bundle\CmsBundle\Entity\CmsFileAttachment;

class reindexZoneCmsFileAttachmentDisplayOrderCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('frontoffice:reindexZoneAttachmentsDisplayOrder')
            ->setDescription('Rebuild the display order sequence of zone attachments')
        ;
    }

    protected function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getQuestionHelper();
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion('Confirm reindexation of all zone attachments ?');

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        $connection = $this->entityManager->getConnection();

        $zones = $this->entityManager->getRepository(Zone::class)->findAll();

        $reaffectedCount = 0;
        foreach ($zones as $zone) {
            $output->writeln('Zone '.$zone->getId());

            $connection->exec("SET @row_number = 0");
            $tableName = $this->entityManager->getClassMetadata(ZoneCmsFileAttachment::class)->getTableName();
            $rootTableName = $this->entityManager->getClassMetadata(CmsFileAttachment::class)->getTableName();


            $sql =
            'UPDATE '.$rootTableName.' ua LEFT JOIN '.$tableName.' uza ON ua.id=uza.id SET ua.display_order = (
               SELECT row_number FROM (
                   SELECT @row_number:=@row_number+1 as row_number, sub.id
                   FROM(
                       SELECT a.id, a.display_order
                       FROM '.$tableName.' za
                       INNER JOIN '.$rootTableName.' a ON za.id = a.id
                       WHERE za.zone_id = :zone
                       ORDER BY a.display_order
                   ) as sub
               ) as sub2
               WHERE id = ua.id
            ) WHERE uza.zone_id = :zone';

            $statement = $connection->prepare($sql);
            $statement->bindValue('zone', $zone->getId());

            $statement->execute();
            $output->writeln('    Reaffected rows : '.$statement->rowCount());
            $reaffectedCount += $statement->rowCount();
        }
        $output->writeln("\nTotal reaffected rows : $reaffectedCount");
    }
}
