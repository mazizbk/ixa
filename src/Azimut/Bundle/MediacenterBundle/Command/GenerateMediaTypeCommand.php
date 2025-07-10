<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-31 15:10:18
 */

namespace Azimut\Bundle\MediacenterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class GenerateMediaTypeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mediacenter:generate:mediatype')
            ->setDescription('Generate skeleton for a new type of media')
            ->addOption('media_type_name', null, InputOption::VALUE_REQUIRED, 'Media type name (ex: image for MediaImage)')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getQuestionHelper();
        $mediaTypeName = $input->getOption('media_type_name');

        if (!$mediaTypeName) {
            $question = new Question('Media type name (ex: "image")');
            $mediaTypeName = $helper->ask($input, $output, $question);

            $input->setOption('media_type_name', $mediaTypeName);
        }
    }

    protected function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getQuestionHelper();
        $mediaTypeName = strtolower($input->getOption('media_type_name'));

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion('Confirm generation of new media type "'.$mediaTypeName.'" (entity class Media'.ucfirst($mediaTypeName).')', true);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        $kernel = $this->getContainer()->get('kernel');
        $bundle = $kernel->getBundle('AzimutMediacenterBundle');

        $templateEntityPath = $bundle->getPath().'/Command/TemplateEntity/';
        $templateFormTypePath = $bundle->getPath().'/Command/TemplateFormType/';
        $entityPath = $bundle->getPath().'/Entity/';
        $formTypePath = $bundle->getPath().'/Form/Type/';

        // Media class
        $this->generateClassFile($templateEntityPath, 'MediaTemplate.php', $entityPath, $mediaTypeName, $output);

        // Media translation class
        $this->generateClassFile($templateEntityPath, 'MediaTemplateTranslation.php', $entityPath, $mediaTypeName, $output);

        // Media declination class
        $this->generateClassFile($templateEntityPath, 'MediaDeclinationTemplate.php', $entityPath, $mediaTypeName, $output);

        // Media declination translation class
        $this->generateClassFile($templateEntityPath, 'MediaDeclinationTemplateTranslation.php', $entityPath, $mediaTypeName, $output);

        // Media FormType class
        $this->generateClassFile($templateFormTypePath, 'MediaTemplateType.php', $formTypePath, $mediaTypeName, $output);

        // MediaDeclination FormType class
        $this->generateClassFile($templateFormTypePath, 'MediaDeclinationTemplateType.php', $formTypePath, $mediaTypeName, $output);
    }

    private function generateClassFile($templateEntityPath, $templateFileName, $entityPath, $mediaTypeName, $output)
    {
        $media_class_content = file_get_contents($templateEntityPath.$templateFileName.'.tmpl');

        $media_class_content = str_replace('%media_type_lowercase%', $mediaTypeName, $media_class_content);
        $media_class_content = str_replace('%media_type_capitalize%', ucfirst($mediaTypeName), $media_class_content);

        $fileName = str_replace('Template', ucfirst($mediaTypeName), $templateFileName);

        $output->writeln('> Generating file: '.$fileName);

        if (file_exists($entityPath.$fileName)) {
            $output->writeln('  File already exists, skipping.');
        } else {
            file_put_contents($entityPath.$fileName, $media_class_content);
        }
    }
}
