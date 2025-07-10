<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 09:38:43
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

use Azimut\Bundle\CmsBundle\Entity\Comment;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;

class CommentType extends AbstractType
{
    private $ratings = [];

    public function __construct(array $ratings)
    {
        $this->ratings = $ratings;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userName', null, [
                'label' => 'your.name',
            ])
            ->add('userEmail', null, [
                'label' => 'your.email',
            ])
            ->add('text', null, [
                'label' => 'comment',
            ])
        ;

        if (true === $options['with_rating']) {
            $builder->add('rating', ChoiceType::class, [
                'choices'  => array_flip($this->ratings),
                'required' => false,
                'label'    => 'rating',
            ]);
        }

        if (true === $options['with_is_visible']) {
            $builder->add('isVisible', null, [
                'label' => 'visible',
            ]);
        }

        if (true === $options['with_hidden_cms_file']) {
            $builder->add('cmsFile', EntityHiddenType::class, [
                'class' => CmsFile::class
            ]);
        }

        if (true === $options['with_captcha']) {
            $builder->add('recaptcha', EWZRecaptchaType::class, [
                'mapped'      => false,
                'constraints' => [
                    new RecaptchaTrue()
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'           => Comment::class,
            'with_rating'          => false,
            'with_is_visible'      => false,
            'with_hidden_cms_file' => false,
            'with_captcha'         => true,
        ]);
    }
}
