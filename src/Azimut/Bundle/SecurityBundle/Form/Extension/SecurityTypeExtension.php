<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-13 16:59:33
 */

namespace Azimut\Bundle\SecurityBundle\Form\Extension;

use Azimut\Bundle\SecurityBundle\Entity\AclField;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

//used for ACL
class SecurityTypeExtension extends AbstractTypeExtension
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
    * {@inheritDoc}
    */
    public function getExtendedType()
    {
        return FormType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // is_granted stuff
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            if (null === $options['is_granted'] || $this->authorizationChecker->isGranted($options['is_granted'])) {
                return;
            }

            $form = $event->getForm();
            if ($form->isRoot()) {
                throw new \RuntimeException('Cannot use is_granted option on a root form.');
            }
            $form->getParent()->remove($form->getName());
        })
        ;

        // acl_field to do check event listener same as above
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            if (false === $options['acl_field']) {
                return;
            }

            $form = $event->getForm();

            $field = $options['acl_field'];

            if (true === $field) {
                $field = $form->getName();
            }

            if ($form->isRoot()) {
                throw new \RuntimeException('Cannot use acl_field option on a root form.');
            }

            // here, we must fetch object and field name and launch an is_granted test here.
            $object = $form->getParent()->getData();
            /*if (!$object) {
            throw new \RuntimeException('Cannot use acl_field without data in form.');
            }*/

            $field = new AclField($object, $field);

            if (!($this->authorizationChecker->isGranted('FIELD_EDIT', $field))) {
                $form->getParent()->remove($form->getName());
            }

            return;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'is_granted' => null,
            'acl_field' => false,
        ));
    }
}
