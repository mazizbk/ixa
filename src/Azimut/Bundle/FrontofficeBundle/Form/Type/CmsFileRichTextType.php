<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-13 10:57:46
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileRichText;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileRichTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', I18nTinymceConfigType::class, array(
                'label' => false,
                'i18n_childen_options' => array(
                    'attr' => array('rows' => '25'),
                    /*'configs' => array(
                        'toolbar1' => "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
                        'toolbar2' => "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink mediacenter | inserttime preview | forecolor backcolor",
                        'toolbar3' => "table | hr removeformat | subscript superscript | charmap emoticons | print | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",
                        'plugins' => array(
                            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                            "table contextmenu directionality emoticons template textcolor paste fullpage textcolor"
                        )
                    )*/
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileRichText::class,
            'error_bubbling' => false
        ));
    }
}
