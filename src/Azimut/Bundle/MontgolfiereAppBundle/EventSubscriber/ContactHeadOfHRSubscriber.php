<?php
/**
 * Created by mikaelp on 2018-11-09 3:45 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class ContactHeadOfHRSubscriber implements EventSubscriber
{

    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if(!$entity instanceof ClientContact) {
                continue;
            }

            if(!$entity->getIsHeadOfHumanResources()) {
                continue;
            }

            $session = $this->requestStack->getMasterRequest()->getSession();
            if($session instanceof Session) {
                $session->getFlashBag()->add('success', $this->translator->trans('montgolfiere.backoffice.clients.contacts.flash.email_sent'));
            }
            $this->createUser($entity, $em);
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if(!$entity instanceof ClientContact) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($entity);
            if(!array_key_exists('isHeadOfHumanResources', $changeSet)) {
                continue;
            }

            $newValue = $changeSet['isHeadOfHumanResources'][1];
            $hasUser = null!==$entity->getFrontUser();
            if($newValue===true) {
                if($hasUser) {
                    $this->enableOrDisableUser($entity, true, $em);
                }
                else {
                    $session = $this->requestStack->getMasterRequest()->getSession();
                    if($session instanceof Session) {
                        $session->getFlashBag()->add('success', $this->translator->trans('montgolfiere.backoffice.clients.contacts.flash.email_sent'));
                    }
                    $this->createUser($entity, $em);
                }
            }
            else {
                // A head of HR should always have a front user, but just in case
                if($hasUser) {
                    $this->enableOrDisableUser($entity, false, $em);
                }
            }
        }
    }

    protected function enableOrDisableUser(ClientContact $contact, $enable, EntityManagerInterface $em)
    {
        if(!$user = $contact->getFrontUser()) {
            throw new \InvalidArgumentException('Can\'t enable user for contact as it has not user associated');
        }

        $user->isActive($enable);
        $uow = $em->getUnitOfWork();
        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($user)), $user);
    }

    protected function createUser(ClientContact $contact, EntityManagerInterface $em)
    {
        if(!$contact->getEmailAddress()) {
            throw new \InvalidArgumentException('Can\'t create a user for contact as it has no email address');
        }
        $frontUser = $em->getRepository(FrontofficeUser::class)->findOneBy(['email' => $contact->getEmailAddress(),]);
        if(!$frontUser) {
            $frontUser = new FrontofficeUser();
            $frontUser->setPlainPassword(bin2hex(random_bytes(10))); // User will set its own password, but it cannot be null
        }
        $frontUser
            ->isActive(true)
            ->setEmail($contact->getEmailAddress())
            ->setFirstName($contact->getFirstName())
            ->setLastName($contact->getLastName())
        ;
        $contact->setFrontUser($frontUser);
        $em->getUnitOfWork()->persist($frontUser);
        $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(get_class($frontUser)), $frontUser);
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($contact)), $contact);
    }
}
