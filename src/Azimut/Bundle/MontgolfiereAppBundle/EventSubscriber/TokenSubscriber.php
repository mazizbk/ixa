<?php
/**
 * Created by mikaelp on 28-Sep-18 9:53 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TokenSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if(!$entity instanceof Campaign && !$entity instanceof CampaignParticipation) {
            return;
        }

        $repo = $eventArgs->getObjectManager()->getRepository(get_class($entity));
        if ($entity instanceof Campaign){
            $tokenField = 'questionnaireToken';
            $tokenSetter = 'setQuestionnaireToken';
        }else{
            $tokenField = 'token';
            $tokenSetter = 'setToken';
        }

        do {
            $token = self::generateToken(40);
            $foundToken = $repo->findOneBy([$tokenField => $token,]) === null;
        }
        while($foundToken !== true);

        $entity->$tokenSetter($token);
    }

    protected static function generateToken($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new \Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
}
