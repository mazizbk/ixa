<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-11-03 15:11:07
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;

class EntityPageTreeType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = [];

        foreach ($view->vars['choices'] as $choice) {
            $choices[] = $choice->data;
        }

        usort($choices, function ($a, $b) {
            return strcmp(
                null == $a->getMenu() ?: $a->getMenu()->getId(),
                null == $b->getMenu() ?: $b->getMenu()->getId()
            );
        });

        $choices = $this->buildTreeChoices($choices);

        $view->vars['choices'] = $choices;
    }

    protected function buildTreeChoices($choices)
    {
        $treeChoices = [];
        $previousMenu = null;

        foreach ($choices as $choice) {
            $menu = $choice->getMenu();

            if (null != $menu) {
                if ($menu != $previousMenu) {
                    $groupName = $menu->getSite()->getName() .' / '. $menu->getName();
                    $choiceGroup = new ChoiceGroupView($groupName);
                    $treeChoices[$groupName] = $choiceGroup;
                    $previousMenu = $menu;
                }

                $choiceGroup->choices[] = new ChoiceView(
                    $choice,
                    $choice->getId(),
                    $choice->getName()
                );

                if (!$choice->getChildrenPages()->isEmpty()) {
                    $choiceGroup->choices = array_merge(
                        $choiceGroup->choices,
                        $this->buildChildrenTreeChoices($choice->getChildrenPages(), 1)
                    );
                }
            }
        }

        return $treeChoices;
    }

    protected function buildChildrenTreeChoices($choices, $level = 0)
    {
        $treeChoices = [];

        foreach ($choices as $choice) {
            $treeChoices[] = new ChoiceView(
                $choice,
                $choice->getId(),
                str_repeat("\xC2\xA0", $level*3) .' '. $choice->getName()
            );

            if (!$choice->getChildrenPages()->isEmpty()) {
                $treeChoices = array_merge(
                $treeChoices,
                $this->buildChildrenTreeChoices($choice->getChildrenPages(), $level+1)
            );
            }
        }

        return $treeChoices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => 'AzimutFrontofficeBundle:Page',
            'choice_label' => 'menuTitle'
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
