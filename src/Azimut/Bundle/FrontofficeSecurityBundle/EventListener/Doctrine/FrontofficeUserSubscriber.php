<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 10:49:35
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Doctrine\ORM\Events;

class FrontofficeUserSubscriber implements EventSubscriber
{
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoder $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof FrontofficeUser) {
            return;
        }
        $this->encodePassword($entity);
        $entity->setConfirmEmailToken(bin2hex(random_bytes(10)));
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof FrontofficeUser) {
            return;
        }
        $this->encodePassword($entity);

        $em = $args->getEntityManager();
        $meta = $em->getClassMetadata(get_class($entity));
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $entity);
    }

    /**
     * @param FrontofficeUser $entity
     */
    private function encodePassword(FrontofficeUser $entity)
    {
        if (!$entity->getPlainPassword()) {
            return;
        }
        $encoded = $this->passwordEncoder->encodePassword(
            $entity,
            $entity->getPlainPassword()
        );
        $entity->setPassword($encoded);
    }
}
