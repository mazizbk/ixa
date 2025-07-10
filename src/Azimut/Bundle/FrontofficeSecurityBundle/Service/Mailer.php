<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-18 10:22:45
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Service;

use Azimut\Bundle\BackofficeBundle\Service\AbstractMailer;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\EmailCSSInliner;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Mailer extends AbstractMailer
{
    /**
     * @var EmailCSSInliner
     */
    protected $emailCSSInliner;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, TranslatorInterface $translator, $sender, EmailCSSInliner $emailCSSInliner)
    {
        parent::__construct($mailer, $templating, $translator, $sender);
        $this->emailCSSInliner = $emailCSSInliner;
    }

    public function sendUserCredentialsMail(FrontofficeUser $user, $domain, $pending = false)
    {
        $subject = $this->translator->trans('email.your.account.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/new_account.html.twig',
            [
                'user' => $user,
                'domain' => $domain,
                'pending' => $pending,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/new_account.txt.twig',
            [
                'user' => $user,
                'domain' => $domain,
                'pending' => $pending,
            ]
        );

        $this->sendMessage($user->getEmail(), $subject, $htmlBody, $textBody);
    }

    public function sendPasswordResetEmail(FrontofficeUser $user, $domain, $token)
    {
        $subject = $this->translator->trans('montgolfiere.emails.password_reset.subject');

        $htmlBody = $this->emailCSSInliner->render(
            '@AzimutMontgolfiereApp/Email/lost_password.html.twig',
            [
                'domain' => $domain,
                'user' => $user,
                'token' => $token
            ]
        );

        $textBody = $this->templating->render(
            '@AzimutMontgolfiereApp/Email/lost_password.txt.twig',
            [
                'domain' => $domain,
                'user' => $user,
                'token' => $token
            ]
        );

        $this->sendMessage($user->getEmail(), $subject, $htmlBody, $textBody);
    }

    public function sendConfirmEmailAddressMail(FrontofficeUser $user, $domain, $token)
    {
        $subject = $this->translator->trans('email.confirm.email.address.for.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/confirm_email_address.html.twig',
            [
                'domain' => $domain,
                'token' => $token
            ]
        );

        $textBody = $this->templating->render(
            'Emails/confirm_email_address.txt.twig',
            [
                'domain' => $domain,
                'token' => $token
            ]
        );

        $this->sendMessage($user->getEmail(), $subject, $htmlBody, $textBody);
    }

    public function sendUserActivatedMail(FrontofficeUser $user, $domain)
    {
        $subject = $this->translator->trans('email.your.account.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/account_activated.html.twig',
            [
                'user' => $user,
                'domain' => $domain,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/account_activated.txt.twig',
            [
                'user' => $user,
                'domain' => $domain,
            ]
        );

        $this->sendMessage($user->getEmail(), $subject, $htmlBody, $textBody);
    }
}
