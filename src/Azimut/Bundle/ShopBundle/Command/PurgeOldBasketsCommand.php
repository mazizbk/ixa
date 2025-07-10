<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-08-22 16:59:11
 */

namespace Azimut\Bundle\ShopBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Azimut\Bundle\ShopBundle\Entity\Order;

class PurgeOldBasketsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('shop:purge_old_baskets')
            ->setDescription('Delete all baskets older than 30 days')
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
            $question = new ConfirmationQuestion('Confirm deletion of all baskets older than 30 days ?', true);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');
                return 1;
            }
        }

        $em = $this->getContainer()->get('doctrine')->getManager();

        $qb = $em->createQueryBuilder();
        $query = $qb->delete(Order::class, 'o')
            ->where('o.number is null')
            ->andWhere('DATE_DIFF(CURRENT_DATE(), o.createdAt) > 30')
            ->getQuery()
        ;

        $deleteCount = $query->execute();

        $output->writeln(sprintf('<info>%s basket(s) deleted</info>', $deleteCount));
    }
}
