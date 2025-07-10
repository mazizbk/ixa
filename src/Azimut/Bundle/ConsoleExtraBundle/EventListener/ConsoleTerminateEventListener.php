<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-04-22 15:45:34
 */

namespace Azimut\Bundle\ConsoleExtraBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;


class ConsoleTerminateEventListener implements CacheWarmerInterface
{
    private $afterConsoleAutoChmod;
    private $cacheDir;
    private $logsDir;
    private $sessionsDir;
    private $uploadsDir;
    /**
     * @var OutputInterface|null
     */
    private $output;

    public function __construct($afterConsoleAutoChmod, $cacheDir, $logsDir, $sessionsDir, $uploadsDir)
    {
        $this->afterConsoleAutoChmod = $afterConsoleAutoChmod;
        $this->cacheDir = $cacheDir;
        $this->logsDir = $logsDir;
        $this->sessionsDir = $sessionsDir;
        $this->uploadsDir = $uploadsDir;
    }

    public function onConsoleTerminateEvent(ConsoleTerminateEvent $event)
    {
        $this->output = $event->getOutput();
        $this->doChmod();
    }

    private function writeOut($str)
    {
        if($this->output) {
            $this->output->writeln($str);
        }
        else {
            echo($str . "\n");
        }
    }

    private function doChmod()
    {
        // execute only if parameter after_console_auto_chmod is true
        if (true === $this->afterConsoleAutoChmod) {

            $this->writeOut('<info>Azimut System auto chmod</info>');

            if (file_exists($this->cacheDir)) {
                $command = sprintf('find %s -user $USER ! -perm -g+w -exec chmod g+w {} \;', $this->cacheDir);
                $this->writeOut('    '.$command);
                exec($command);
            }

            if (file_exists($this->logsDir)) {
                $command = 'chmod -Rf g+rw '.$this->logsDir;
                $this->writeOut('    '.$command);
                exec($command);
            }

            if (file_exists($this->sessionsDir)) {
                $command = 'chmod -Rf g+rw '.$this->sessionsDir;
                $this->writeOut('    '.$command);
                exec($command);
            }

            if (file_exists($this->uploadsDir)) {
                $command = sprintf('find %s -user $USER ! -perm -g+w -exec chmod g+w {} \;', $this->uploadsDir);
                $this->writeOut('    '.$command);
                exec($command);
            }
        }
    }

    public function isOptional()
    {
        return true;
    }

    public function warmUp($cacheDir)
    {
        if(php_sapi_name() === 'cli') {
            $this->doChmod();
        }
    }


}
