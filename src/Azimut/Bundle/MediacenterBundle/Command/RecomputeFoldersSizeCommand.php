<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-08 11:53:06
 */

namespace Azimut\Bundle\MediacenterBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;

class RecomputeFoldersSizeCommand extends AbstractFolderCommand
{
    protected function configure()
    {
        $this
            ->setName('mediacenter:folders:recomputeSizes')
            ->setDescription('Recompute each folder size')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getQuestionHelper();
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion('Confirm recomputation of each folder size ?');

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        $rootFolders = $this->entityManager->getRepository(Folder::class)->findRootFolders();
        $totalSize = 0;

        // Compute folders size
        foreach ($rootFolders as $folder) {
            $totalSize += $this->computeFolderSize($folder, 0, $output);
        }

        // Display folders size
        foreach ($rootFolders as $folder) {
            $this->displayFolder($folder, $output, 0);
        }

        $this->entityManager->flush();

        $output->writeln("\nTotal size : ". $this->formatSize($totalSize));
    }

    private function computeFolderSize(Folder $folder)
    {
        $folderSize = 0;

        foreach ($folder->getSubfolders() as $subfolder) {
            $folderSize += $this->computeFolderSize($subfolder);
        }

        foreach ($folder->getMedias() as $media) {
            $folderSize += $media->getSize();
        }

        $folder->setSize($folderSize);

        return $folderSize;
    }
}
