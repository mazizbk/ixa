<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28 14:53:52
 */

namespace Azimut\Bundle\CmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class GenerateCmsFileTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cms:generate:cmsfiletype')
            ->setDescription('Generate skeleton for a new type of cms file')
            ->addOption('cms_file_type_name', null, InputOption::VALUE_REQUIRED, 'CmsFile type name (ex: article for CmsFileArticle)')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getQuestionHelper();
        $cmsFileTypeName = $input->getOption('cms_file_type_name');

        if (!$cmsFileTypeName) {
            $question = new Question('CmsFile type name (ex: "article")');

            $cmsFileTypeName = $helper->ask($input, $output, $question);

            $input->setOption('cms_file_type_name', $cmsFileTypeName);
        }
    }

    protected function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getQuestionHelper();
        $cmsFileTypeName = strtolower($input->getOption('cms_file_type_name'));

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion('Confirm generation of new cms file type "'.$cmsFileTypeName.'" (entity class CmsFile'.ucfirst($cmsFileTypeName).')', true);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        $kernel = $this->getContainer()->get('kernel');
        $bundle = $kernel->getBundle('AzimutCmsBundle');

        $templateEntityPath = $bundle->getPath().'/Command/TemplateEntity/';
        $templateFormTypePath = $bundle->getPath().'/Command/TemplateFormType/';
        $entityPath = $bundle->getPath().'/Entity/';
        $formTypePath = $bundle->getPath().'/Form/Type/';

        // CmsFile class
        $this->generateClassFile($templateEntityPath, 'CmsFileTemplate.php', $entityPath, $cmsFileTypeName, $output);

        // CmsFile translation class
        $this->generateClassFile($templateEntityPath, 'CmsFileTemplateTranslation.php', $entityPath, $cmsFileTypeName, $output);

        // CmsFile FormType class
        $this->generateClassFile($templateFormTypePath, 'CmsFileTemplateType.php', $formTypePath, $cmsFileTypeName, $output);

        // CmsFile AccessRight class
        $this->generateClassFile($templateEntityPath, 'AccessRightCmsFileTemplate.php', $entityPath, $cmsFileTypeName, $output);
    }

    private function generateClassFile($templateEntityPath, $templateFileName, $entityPath, $cmsFileTypeName, $output)
    {
        $cmsFileClassContent = file_get_contents($templateEntityPath.$templateFileName.'.tmpl');

        $cmsFileClassContent = str_replace('%cms_file_type_lowercase%', $cmsFileTypeName, $cmsFileClassContent);
        $cmsFileClassContent = str_replace('%cms_file_type_capitalize%', ucfirst($cmsFileTypeName), $cmsFileClassContent);

        $fileName = str_replace('Template', ucfirst($cmsFileTypeName), $templateFileName);

        $output->writeln('> Generating file: '.$fileName);

        if (file_exists($entityPath.$fileName)) {
            $output->writeln('  File already exists, skipping.');
        } else {
            file_put_contents($entityPath.$fileName, $cmsFileClassContent);
        }
    }
}
