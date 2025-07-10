<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-19 10:07:24
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;

use Azimut\Bundle\FrontofficeBundle\Entity\Zone;

class EntityZoneTreeType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = [];

        foreach ($view->vars['choices'] as $choice) {
            $choices[] = $choice->data;
        }

        usort($choices, function ($a, $b) {
            return strcmp(
                null == $a->getPage() ?: $a->getPage()->getId(),
                null == $b->getPage() ?: $b->getPage()->getId()
            );
        });

        $choices = $this->buildTreeChoices($choices);

        $view->vars['choices'] = $choices;
    }

    protected function buildTreeChoices($choices)
    {
        $treeChoices = [];
        $previousPage = null;

        foreach ($choices as $choice) {
            $page = $choice->getPage();

            if ($page != $previousPage) {
                $groupName = '';
                $parentPage = $page;
                $rootPage = $page;

                while (null != $parentPage = $parentPage->getParentPage()) {
                    $groupName .= $parentPage->getName() . ' / ';
                    $rootPage = $parentPage;
                }

                $groupName = $rootPage->getMenu()->getSite()->getName() . ' / ' . $rootPage->getMenu()->getName() . ' / ' . $groupName . $page->getName();
                $choiceGroup = new ChoiceGroupView($groupName);
                $treeChoices[$groupName] = $choiceGroup;
                $previousPage = $page;
            }

            $choiceGroup->choices[] = new ChoiceView(
                $choice,
                $choice->getId(),
                $choice->getName()
            );
        }

        return $treeChoices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Zone::class,
            'choice_label' => 'name'
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
