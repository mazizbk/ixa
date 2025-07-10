<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-27 17:38:07
 */

namespace Azimut\Bundle\CmsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\ORM\EntityManagerInterface;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment1Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment2Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment3Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment4Trait;
use Azimut\Component\PHPExtra\TraitHelper;

class RepairCmsFileMediaDeclinationAttachmentLink extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('cms:repairCmsFileMediaDeclinationAttachmentLink')
            ->setDescription('Set relation from CmsFileMediaDeclinationAttachment to CmsFile')
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
            $question = new ConfirmationQuestion('Confirm repairing of all CmsFileMediaDeclinationAttachment relations to CmsFile ?');

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        $cmsFiles = $this->entityManager->getRepository(CmsFile::class)->findAll();

        foreach ($cmsFiles as $cmsFile) {
            $output->writeln($cmsFile->getId().' - "'.$cmsFile->getName() .'" <comment>['. get_class($cmsFile) .']</comment>');
            if (TraitHelper::isClassUsing(get_class($cmsFile), CmsFileMainAttachmentTrait::class)) {
                if ($cmsFile->getMainAttachment()) {
                    $this->repairRelation($cmsFile->getMainAttachment(), $cmsFile, 'main attachment', $output);
                }
            }
            if (TraitHelper::isClassUsing(get_class($cmsFile), CmsFileComplementaryAttachment1Trait::class)) {
                if ($cmsFile->getComplementaryAttachment1()) {
                    $this->repairRelation($cmsFile->getComplementaryAttachment1(), $cmsFile, 'complementary attachment 1', $output);
                }
            }
            if (TraitHelper::isClassUsing(get_class($cmsFile), CmsFileComplementaryAttachment1Trait::class)) {
                if ($cmsFile->getComplementaryAttachment2()) {
                    $this->repairRelation($cmsFile->getComplementaryAttachment2(), $cmsFile, 'complementary attachment 2', $output);
                }
            }
            if (TraitHelper::isClassUsing(get_class($cmsFile), CmsFileComplementaryAttachment1Trait::class)) {
                if ($cmsFile->getComplementaryAttachment3()) {
                    $this->repairRelation($cmsFile->getComplementaryAttachment3(), $cmsFile, 'complementary attachment 3', $output);
                }
            }
            if (TraitHelper::isClassUsing(get_class($cmsFile), CmsFileComplementaryAttachment1Trait::class)) {
                if ($cmsFile->getComplementaryAttachment4()) {
                    $this->repairRelation($cmsFile->getComplementaryAttachment4(), $cmsFile, 'complementary attachment 4', $output);
                }
            }
            if (TraitHelper::isClassUsing(get_class($cmsFile), CmsFileSecondaryAttachmentsTrait::class)) {
                foreach ($cmsFile->getSecondaryAttachments() as $attachment) {
                    $this->repairRelation($attachment, $cmsFile, 'secondary attachment', $output);
                }
            }
        }

        $this->entityManager->flush();
    }

    private function repairRelation(CmsFileMediaDeclinationAttachment $attachment, CmsFile $cmsFile, $relationName, OutputInterface $output)
    {
        if (null == $attachment->getCmsFile() || $attachment->getCmsFile()->getId() != $cmsFile->getId()) {
            $output->writeln('<info>   Repairing '. $relationName .' relation : '. $attachment->getMediadeclination()->getMedia()->getName() .'</info>');
            $attachment->setCmsFile($cmsFile);
        }
    }
}
