<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-08 17:32:23
 */

namespace Azimut\Bundle\MediacenterBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\ORM\EntityManagerInterface;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use Azimut\Bundle\MediacenterBundle\Entity\Media;

class AbstractFolderCommand extends Command
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

    protected function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }

    protected function displayFolder(Folder $folder, OutputInterface $output, $level)
    {
        $output->writeln(str_repeat(' ', $level*2) . ($level > 0 ? ' └─ ':'─ ') . $folder->getName() . ' [' . $this->formatSize($folder->getSize()) . ']');

        foreach ($folder->getSubfolders() as $subfolder) {
            $this->displayFolder($subfolder, $output, $level+1);
        }
    }

    protected function formatSize($size)
    {
        $unit = 'B';
        if ($size > 1000) {
            $size = $size / 1000;
            $unit = 'KB';
        }
        if ($size > 1000) {
            $size = $size / 1000;
            $unit = 'MB';
        }
        if ($size > 1000) {
            $size = $size / 1000;
            $unit = 'GB';
        }
        if ($size > 1000) {
            $size = $size / 1000;
            $unit = 'TB';
        }

        return number_format($size, 2) . $unit;
    }
}
