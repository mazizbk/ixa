<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-01-25 15:03:39
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PageLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('linkType', ChoiceType::class, array(
                'choices' => array(
                    'internal.link' => 'internal',
                    'external.link' => 'external'
                ),
                'data' => 'internal',
                'mapped' => false,
                'expanded' => true,
                'label' => false
            ))
            ->add('url', null, array(
                'label' => 'url'
            ))
            ->add('targetPage', EntityPageTreeType::class, array(
                'label' => 'link.to.page'
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // external or internal link type switch
            if ('internal' == $data['linkType']) {
                $data['url'] = null;
                //$form->remove('url');
            } elseif ('external' == $data['linkType']) {
                $data['targetPage'] = null;
                //$form->remove('targetPage');
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Azimut\Bundle\FrontofficeBundle\Entity\PageLink',
            'error_bubbling' => false
        ));
    }
}
