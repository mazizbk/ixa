<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-11-18 14:14:25
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Transformer\EntityToIntegerTransformer;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityHiddenType extends AbstractType
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
    * {@inheritdoc}
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $class = $options['class'];
        if (!$class) {
            throw new \InvalidArgumentException('No class set in form type.');
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $em = $this->registry->getManagerForClass($class);

        $builder
            ->addModelTransformer(new EntityToIntegerTransformer($em, $class))
        ;
    }

    /**
    * {@inheritdoc}
    */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => null,
            'data_class' => null,
            'error_bubbling' => false
        ));

        $resolver->setRequired(array('class'));
    }

    /**
    * {@inheritdoc}
    */
    public function getParent()
    {
        return HiddenType::class;
    }
}
