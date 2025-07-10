<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-09-17 14:40:52
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This class makes sure that a form related to a translatable entity will always have
 * its data set to the required entity class instance.
 * This is mandatory when adding a translatable entity form type as a dynamic collection. Because the controller will not set its data, and it is needed for the children i18n fields (they are calling parent entity setters on their setData event)
 *
 * Usage :
 *     class MyEntityType extends AbstractType
 *     {
 *         ...
 *         public function getParent()
 *         {
 *             return TranslatableEntityType::class;
 *         }
 *     }
 */
class TranslatableEntityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            if (null === $event->getData()) {
                $event->setData(new $options['data_class']());
            }
        });
    }

    /**
    * {@inheritdoc}
    */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['data_class']);
    }
}
