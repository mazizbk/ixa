<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-26 17:42:05
 */

namespace Azimut\Bundle\FormExtraBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class AngularTypeExtension extends AbstractTypeExtension
{

    /**
     * {@inheritdoc}
     *
     * Prepare required model names for AngularJS form binding
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $name = $form->getName();
        $ngModel = 'forms.data.' . $name;
        $ngModelInfos = 'forms.infos.' . $name;
        $ngModelErrors = 'forms.errors.' . $name . '.errors';
        $grandParentId = null;

        if ($view->parent) {
            $parentNgModel = $view->parent->vars['ng_model'];
            $parentNgModelInfos = $view->parent->vars['model_infos'];
            $parentNgModelErrors = $view->parent->vars['model_errors'];

            if ('' !== $parentNgModel) {

                /*
                // if inherit data flag is on, then real sub properties are directly on the parent model
                if(true === $options['inherit_data']) {
                    $ngModel = $parentNgModel;
                    $ngModelInfos = $parentNgModelInfos;
                    $ngModelErrors = $parentNgModelErrors;
                }
                else {*/
                    $ngModel = sprintf("%s['%s']", $parentNgModel, $name);
                $ngModelInfos = sprintf("%s['%s']", $parentNgModelInfos, $name);
                $ngModelErrors = sprintf("%s.children['%s'].errors", substr($parentNgModelErrors, 0, strlen($parentNgModelErrors) - strlen('.errors')), $name);
                //}
            }

            if ($view->parent->parent) {
                $grandParentId = $view->parent->parent->vars['id'];
            }

            // radio elements share the same model, so drop the last index (id:mydata.mychoice[1] => model:mydata.mychoice)
            if ('radio' == $form->getConfig()->getType()->getBlockPrefix()) {
                $ngModel = $parentNgModel;
            }
        }

        $rootFormName = explode('[', $view->vars['full_name'])[0];

        $view->vars = array_merge($view->vars, [
            'ng_model' => $ngModel,
            'root_form_name' => $rootFormName,
            'model_infos' => $ngModelInfos,
            'model_errors' => $ngModelErrors,
            'grand_parent_id' => $grandParentId
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
