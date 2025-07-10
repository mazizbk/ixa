<?php
/**
 * Created by PhpStorm.
 * User: gerdald
 * Date: 09/07/14
 * Time: 10:09
 */

namespace Azimut\Bundle\SecurityBundle\EventListener;

use Azimut\Bundle\SecurityBundle\Form\Type\AccessRightObjectType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAcl;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAppRoles;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightRoles;

class AccessRightTypeEventSubscriber implements EventSubscriberInterface
{
    private $registry;
    private $roleProvider;
    private $accessRightService;
    private $options;

    public function __construct(RegistryInterface $registry, $roleProvider, $accessRightService, array $options)
    {
        $this->registry = $registry;
        $this->roleProvider = $roleProvider;
        $this->accessRightService = $accessRightService;
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit'
        );
    }

    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        if (!$data instanceof AccessRight) {
            throw new \RuntimeException('AccessRight form type only works with a AccessRight object');
        }

        if (!$data instanceof AccessRightRoles && !$data instanceof AccessRightAppRoles && !$data instanceof AccessRightAcl && !$data instanceof AccessRightClass) {
            $type = AccessRightObjectType::class;
            $class = get_class($data->getObject());

            //if object class is with proxy remove it
            if (strpos($class, 'Proxies') !== false) {
                $class = substr($class, 15);
            }

            $form->add('accessRightType', $type, array(
                'inherit_data' => true,
                'label' => false,
                'object_class' => $class
            ));
        } else {
            $form->add('accessRightType', $data->getFormType(), array(
                'inherit_data' => true,
                'label' => false
            ));
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $doctrine = $this->registry;
        $data = $event->getData();
        $form = $event->getForm();

        $objectId = '';
        // fetch access right type from submited data
        $accessRightType = $data['type'];
        if (!empty($data['accessRightType']['objectId'])) {
            $objectId = $data['accessRightType']['objectId'];
        }

        // instanciate the corresponding AccessRight entity
        $accessRight = $doctrine
            ->getRepository(AccessRight::class)
            ->createInstanceFromString($accessRightType)
        ;

        // set the data
        $form->setData($accessRight);

        if ($objectId) {
            $class = $accessRight->getObjectClass();  // returns the class of the object linked to the access right
            $object = $doctrine
                ->getRepository($class)
                ->find($objectId)
            ;
            $accessRight->setObject($object);
        }
        // add the correct form type
        if (!$accessRight instanceof AccessRightRoles && !$accessRight instanceof AccessRightAppRoles && !$accessRight instanceof AccessRightAcl && !$accessRight instanceof AccessRightClass) {
            $type = AccessRightObjectType::class;
            $form->add('accessRightType', $type, array(
                'inherit_data' => true,
                'label' => false,
                'object_class' => $class
            ));
        } else {
            $form->add('accessRightType', $accessRight->getFormType(), array(
                'inherit_data' => true,
                'label' => false
            ));
        }
    }
}
