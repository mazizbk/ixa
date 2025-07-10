<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 10:55:22
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Azimut\Bundle\BackofficeBundle\Service\AbstractMailer;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AbstractRequestLocaleAwareMailer extends AbstractMailer
{
    /**
     * @var string|null
     */
    protected $locale;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, TranslatorInterface $translator, RequestStack $requestStack, $sender, $adminRecipient = null)
    {
        parent::__construct($mailer, $templating, $translator, $sender, $adminRecipient);

        $request = $requestStack->getCurrentRequest();
        if (null == $request) {
            return;
        }

        $this->locale = $request->getLocale();
    }
}
