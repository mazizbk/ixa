<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 10:19:18
 */

namespace Azimut\Bundle\ModerationBundle\Service;

use Azimut\Bundle\BackofficeBundle\Service\AbstractMailer;
use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Mailer extends AbstractMailer
{
    /** @var string moderation recipient */
    private $moderationRecipient;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, TranslatorInterface $translator, $sender, $moderationRecipient)
    {
        parent::__construct($mailer, $templating, $translator, $sender);
        $this->moderationRecipient = $moderationRecipient;
    }

    public function sendUserCmsFileBufferCreated($userEmail, $locale, $domain, CmsFileBuffer $cmsFileBuffer)
    {
        $subject = $this->translator->trans('email.your.content.suggestion');

        $htmlBody = $this->templating->render(
            'Emails/Moderation/user_cmsfile_buffer_created.html.twig',
            [
                'domain'        => $domain,
                'cmsFileBuffer' => $cmsFileBuffer,
                'locale'        => $locale,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Moderation/user_cmsfile_buffer_created.txt.twig',
            [
                'domain'        => $domain,
                'cmsFileBuffer' => $cmsFileBuffer,
                'locale'        => $locale,
            ]
        );

        $this->sendMessage($userEmail, $subject, $htmlBody, $textBody);
    }

    public function sendAdminCmsFileBufferCreated($domain, $locale, CmsFileBuffer $cmsFileBuffer)
    {
        $subject = $this->translator->trans('email.new.content.suggestion');

        $htmlBody = $this->templating->render(
            'Emails/Moderation/admin_cmsfile_buffer_created.html.twig',
            [
                'domain'        => $domain,
                'cmsFileBuffer' => $cmsFileBuffer,
                'locale'        => $locale,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Moderation/admin_cmsfile_buffer_created.txt.twig',
            [
                'domain'        => $domain,
                'cmsFileBuffer' => $cmsFileBuffer,
                'locale'        => $locale,
            ]
        );

        $this->sendMessage($this->moderationRecipient, $subject, $htmlBody, $textBody);
    }

    public function sendUserCmsFileBufferValidated($userEmail, $locale, $domain, CmsFile $cmsFile, CmsFileBuffer $cmsFileBuffer, $contentUrl)
    {
        $subject = $this->translator->trans('email.your.content.suggestion.has.been.validated');

        $htmlBody = $this->templating->render(
            'Emails/Moderation/user_cmsfile_buffer_validated.html.twig',
            [
                'domain'     => $domain,
                'cmsFile'    => $cmsFile,
                'locale'     => $locale,
                'title'       => $cmsFileBuffer->getName(),
                'contentUrl' => $contentUrl,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Moderation/user_cmsfile_buffer_validated.txt.twig',
            [
                'domain'     => $domain,
                'cmsFile'    => $cmsFile,
                'locale'     => $locale,
                'title'       => $cmsFileBuffer->getName(),
                'contentUrl' => $contentUrl,
            ]
        );

        $this->sendMessage($userEmail, $subject, $htmlBody, $textBody);
    }
}
