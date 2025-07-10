<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Extension;


use Azimut\Bundle\MontgolfiereAppBundle\Traits\UploadableEntity;
use Azimut\Component\PHPExtra\TraitHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UploadableEntityExtension extends AbstractTypeExtension
{

    public function getExtendedType()
    {
        return FormType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();
            if(!$form->has('uploadedFile') || !$form->get('uploadedFile')->getData()) {
                return;
            }

            $data = $event->getData();
            if(!TraitHelper::isClassUsing(get_class($data), UploadableEntity::class)) {
                return;
            }

            // Force Doctrine to register an update event
            $data->setOriginalName($data->getOriginalName()?null:'');
        });
    }

}
