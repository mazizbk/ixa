<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-11 17:40:04
 */

namespace Azimut\Bundle\BackofficeBundle\Service;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractMailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $sender;

    /**
     * @var string|null
     */
    protected $adminRecipient;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, TranslatorInterface $translator, $sender, $adminRecipient = null)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->sender = $sender;
        $this->adminRecipient = $adminRecipient;
    }

    /**
     * Send an email message
     * @param  string $to       recipient email address
     * @param  string $subject  Email subject
     * @param  string $htmlBody Message html body
     * @param  string $textBody Message plain text body
     * @return int The number of successful recipients. Can be 0 which indicates failure
     */
    protected function sendMessage($to, $subject, $htmlBody, $textBody)
    {
        $message = \Swift_Message::newInstance()
            ->setFrom($this->sender)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($htmlBody, 'text/html')
            ->addPart($textBody, 'text/plain')
        ;

        return $this->mailer->send($message);
    }
}
