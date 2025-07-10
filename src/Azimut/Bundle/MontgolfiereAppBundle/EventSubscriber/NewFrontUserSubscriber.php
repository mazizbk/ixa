<?php
/**
 * Created by mikaelp on 2018-11-12 11:47 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Azimut\Bundle\MontgolfiereAppBundle\Util\EmailCSSInliner;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class NewFrontUserSubscriber implements EventSubscriber
{

    /**
     * @var EngineInterface
     */
    protected $engine;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    protected $fromAddress;
    protected $fromName;
    protected $sender;
    protected $replyTo;
    /**
     * @var EmailCSSInliner
     */
    protected $emailCSSInliner;


    public function __construct(EngineInterface $engine, EmailCSSInliner $emailCSSInliner, TranslatorInterface $translator, \Swift_Mailer $mailer, $fromAddress, $fromName, $sender, $replyTo)
    {
        $this->engine = $engine;
        $this->emailCSSInliner = $emailCSSInliner;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->sender = $sender;
        $this->replyTo = $replyTo;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if(!$entity instanceof FrontofficeUser) {
                continue;
            }
            if($entity instanceof Consultant) {
                continue;
            }

            $this->sendEmail($entity);
        }
    }

    protected function sendEmail(FrontofficeUser $user)
    {
        $message = (new \Swift_Message())
            ->setSubject($this->translator->trans('montgolfiere.emails.account_creation.subject'))
            ->setTo($user->getEmail())
            ->setFrom($this->sender, $this->fromName)
            ->setSender($this->sender)
            ->setReplyTo($this->replyTo)
        ;

        $context = [
            'account' => $user,
            'reply_to' => $this->replyTo,
        ];

        $message->addPart($this->emailCSSInliner->render('@AzimutMontgolfiereApp/Email/account_creation.html.twig', $context), 'text/html');
        $message->addPart($this->engine->render('@AzimutMontgolfiereApp/Email/account_creation.txt.twig', $context), 'text/plain');

        $this->mailer->send($message);
    }

}
