<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Command;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SendWBEEmailsCommand extends ContainerAwareCommand
{
    /**
     * @var RegistryInterface
     */
    protected $em;
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var EngineInterface
     */
    protected $engine;

    public function __construct(RegistryInterface $em, \Swift_Mailer $mailer, TranslatorInterface $translator, EngineInterface $engine, $name = null)
    {
        parent::__construct($name);
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('montgolfiere:send-wbe-emails')
            ->addArgument('file', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = $reader->load($filename);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $repo = $this->em->getRepository(CampaignParticipation::class);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
        for ($row = 2; $row <= $highestRow; ++$row) {
            $rowData = [];
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                $rowData[] = $sheet->getCellByColumnAndRow($col, $row)->getValue();
            }

            $participation = $repo->find($rowData[1]);
            if(!$participation) {
                $output->writeln('<warning>Participation #'.$rowData[1].' was not found</warning>');
                continue;
            }
            $emailAddress = $rowData[0];

            $message = (new \Swift_Message())
                ->setSubject($this->translator->trans('montgolfiere.emails.participation.subject'))
                ->setTo($emailAddress)
                ->setFrom($this->getContainer()->getParameter('sender_address'), 'Workcare')
                ->setSender($this->getContainer()->getParameter('sender_address'))
                ->setReplyTo($this->getContainer()->getParameter('contact_form_recipient'))
            ;
            $message->setBody($this->engine->render('@AzimutMontgolfiereApp/Email/participation.txt.twig', ['participation' => $participation]), 'text/plain');

            $this->mailer->send($message);
        }

        return 0;


    }
}
