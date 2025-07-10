<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-08 11:53:06
 */

namespace Azimut\Bundle\MediacenterBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;

class DisplayFoldersSizeCommand extends AbstractFolderCommand
{
    protected function configure()
    {
        $this
            ->setName('mediacenter:folders:displaySizes')
            ->setDescription('Display each folder size')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootFolders = $this->entityManager->getRepository(Folder::class)->findRootFolders();

        // Display folders size
        foreach ($rootFolders as $folder) {
            $this->displayFolder($folder, $output, 0);
        }
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
