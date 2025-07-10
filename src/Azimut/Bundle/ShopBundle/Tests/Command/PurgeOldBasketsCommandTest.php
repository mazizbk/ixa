<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-03-22 17:01:02
 */

namespace Azimut\Bundle\ShopBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Azimut\Bundle\ShopBundle\Command\PurgeOldBasketsCommand;
use Azimut\Bundle\ShopBundle\Entity\Order;

class PurgeOldBasketsCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $application = new Application($kernel);

        $em = $kernel->getContainer()->get('doctrine')->getEntityManager();
        $qb = $em->createQueryBuilder();
        $oldBasketCount = $qb->select('COUNT(o)')
            ->from(Order::class, 'o')
            ->where('o.number is null')
            ->andWhere('DATE_DIFF(CURRENT_DATE(), o.createdAt) > 30')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $command = $application->find('shop:purge_old_baskets');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);

        $commandTester->execute([
            'command'  => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains(sprintf('%s basket(s) deleted', $oldBasketCount), $output);
    }
}
