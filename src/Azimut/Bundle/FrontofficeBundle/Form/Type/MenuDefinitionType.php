<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 16:05:47
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use Azimut\Bundle\FrontofficeBundle\Entity\MenuDefinition;

class MenuDefinitionType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'name',
            ])
            ->add('placeholder', null, [
                'label' => 'placeholder',
            ])
        ;
        if ($this->authorizationChecker->isGranted('SUPER_ADMIN')) {
            $builder
                ->add('isFirstPageLevelLocked', CheckboxType::class, [
                    'label'    => 'lock.first.page.level',
                    'hint'     => 'only.super.admin.user.will.be.able.to.insert.move.or.delete.first.level.pages',
                    'required' => false,
                ])
                ->add('maxPagesCount', IntegerType::class, [
                    'label'    => 'max.pages.count',
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MenuDefinition::class
        ]);
    }
}
