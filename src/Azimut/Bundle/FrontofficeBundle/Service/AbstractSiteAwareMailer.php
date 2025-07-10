<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 10:55:22
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Azimut\Bundle\BackofficeBundle\Service\AbstractMailer;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;

class AbstractSiteAwareMailer extends AbstractMailer
{
    /**
     * @var Site
     */
    protected $site;

    /**
     * @var string
     */
    protected $locale;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, TranslatorInterface $translator, FrontService $frontService, $sender, $adminRecipient = null)
    {
        parent::__construct($mailer, $templating, $translator, $sender, $adminRecipient);
        $this->site = $frontService->getCurrentSite();
        $this->locale = $frontService->getLocale();
    }
}
