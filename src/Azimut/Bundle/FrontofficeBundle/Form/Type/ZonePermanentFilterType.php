<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 11:42:48
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\ZonePermanentFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ZonePermanentFilterType extends AbstractType
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $classes = $this->registry->getManager()->getClassMetadata(ZonePermanentFilter::class)->discriminatorMap;

        $builder
            ->add('class', ChoiceType::class, [
                'choices' => $classes,
                'label' => 'type',
                'mapped' => false,
            ])
            ->add('property', null, [
                'label' => 'property',
            ])
            ->add('operation', ChoiceType::class, [
                'choices' => ZonePermanentFilter::buildOperationsChoices(),
                'label'   => 'operation',
            ])
            ->add('value', null, [
                'label' => 'value',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null === $data) {
                return;
            }

            if (!$data instanceof ZonePermanentFilter) {
                throw new \RuntimeException('ZonePermanentFilterType form type only works with a ZonePermanentFilter object');
            }

            // set filter type in form based on existing data
            $form->get('class')->setData(get_class($data));
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // instanciate correct subclass if necessary
            if (!($form->getData() instanceof $data['class'] )) {
                $form->setData(new $data['class']);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ZonePermanentFilter::class
        ]);
    }
}
