<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-02-02 12:04:04
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExceptionController extends BaseExceptionController
{
    private $registry;

    public function __construct(\Twig_Environment $twig, $debug, RegistryInterface $registry)
    {
        $this->registry = $registry;

        parent::__construct($twig, $debug);
    }

    protected function findTemplate(Request $request, $format, $code, $showException)
    {
        if (!$showException) {
            $site = $this->registry->getRepository(Site::class)
                ->findOneActiveByDomainName($request->getHost())
            ;

            if ($site) {
                $exceptionTemplatesDir = $site->getLayout()->getExceptionTemplatesDir();

                if (null != $exceptionTemplatesDir) {
                    $template = sprintf('Exception/%s/error%s.%s.twig', $exceptionTemplatesDir, $code, $format);

                    if ($this->templateExists($template)) {
                        return $template;
                    }
                }
            }
        }

        return parent::findTemplate($request, $format, $code, $showException);
    }
}
