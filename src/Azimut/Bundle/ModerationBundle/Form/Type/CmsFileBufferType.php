<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 16:47:30
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;

class CmsFileBufferType extends AbstractType
{
    private $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['with_user_email']) {
            $builder
                ->add('userEmail', EmailType::class, [
                    'label' => 'your.email',
                ])
            ;
        }

        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->locales,
                'label' => 'language',
                'choice_label' => function ($value, $key, $index) {
                    return $value;
                },
            ])
            ->add('type', HiddenType::class, [
                'mapped' => false
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null === $data) {
                return;
            }

            if ($data && !$data instanceof CmsFileBuffer) {
                throw new \RuntimeException('CmsFileBuffer form type only works with a CmsFileBuffer object');
            }

            $type = $data->getFormType();

            $form->add('cmsFileBufferType', $type, [
                'inherit_data' => true,
                'label' => false,
                'allow_extra_fields' => $options['allow_extra_fields']
            ]);

            if ($data::hasFile() && $options['with_file']) {
                $form->add('file', FileType::class, [
                    'label' => 'file',
                    'required' => false,
                ]);
            }

            if ($options['with_captcha']) {
                $form->add('recaptcha', EWZRecaptchaType::class, [
                    'mapped'      => false,
                    'constraints' => [
                        new RecaptchaTrue()
                    ]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileBuffer::class,
            'with_user_email' => true,
            'with_file' => true,
            'with_captcha' => true,
            'allow_extra_fields' => true,
        ]);
    }
}
