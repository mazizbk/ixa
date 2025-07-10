<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-26 17:42:05
 */

namespace Azimut\Bundle\FormExtraBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class AngularButtonTypeExtension extends AbstractTypeExtension
{

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $full_name = $view->vars['full_name'];
        $full_name = str_replace("[", "['", $full_name);
        $full_name = str_replace("]", "']", $full_name);

        $root_form_name = explode('[', $full_name)[0];

        $view->vars['root_form_name'] = $root_form_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ButtonType::class;
    }
}
