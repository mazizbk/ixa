<?php

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextareaType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLink;
use Azimut\Bundle\FrontofficeBundle\Entity\PagePlaceholder;

class PageType extends AbstractType
{
    private $registry;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(RegistryInterface $registry, array $userRoles, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->registry = $registry;
        $this->userRoles = $userRoles;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('menuTitle', I18nTextType::class, array(
                'label' => 'title'
            ))
            ->add('pageTitle', I18nTextType::class, array(
                'label' => 'title.in.page'
            ))
            ->add('differentPageTitle', CheckboxType::class, array(
                'label' => 'use.different.menu.and.page.titles',
                'required' => false
            ))
            ->add('autoSlug', CheckboxType::class, array(
                'label' => 'automatic.slug',
                'required' => false
            ))
            ->add('slug', I18nTextType::class, array(
                'label' => 'slug'
            ))
            ->add('showInMenu', CheckboxType::class, array(
                'label' => 'show.in.menu',
                'required' => false
            ))
            ->add('active', CheckboxType::class, array(
                'label' => 'active',
                'required' => false
            ))
            ->add('userRoles', ChoiceType::class, [
                'label' => 'restrict.access.to.user.roles',
                'choices'  => $this->userRoles,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'choice_label' => function ($value) {
                    return $value;
                },
            ])
            ->add('uniquePasswordAccess', TextType::class, [
                'label' => 'restrict.access.to.unique.password',
                'required' => false,
            ])
            ->add('parentPage', EntityHiddenType::class, array(
                'class' => 'Azimut\Bundle\FrontofficeBundle\Entity\Page'
            ))
            ->add('menu', EntityHiddenType::class, array(
                'class' => 'Azimut\Bundle\FrontofficeBundle\Entity\Menu'
            ))
            ->add('displayOrder', HiddenType::class)
            //needed by the api to instanciate the page object
            ->add('type', HiddenType::class, array(
                'mapped' => false
            ))
        ;

        if ($this->authorizationChecker->isGranted('SUPER_ADMIN')) {
            $builder->add('isPageParametersLocked', CheckboxType::class, [
                'label'    => 'lock.page.parameters',
                'hint'     => 'only.super.admin.user.will.be.able.to.edit.page.parameters',
                'required' => false,
            ]);
        }

        $doctrine = $this->registry;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($doctrine, $options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null === $data) {
                return;
            }

            if (!$data instanceof Page) {
                throw new \RuntimeException('Page form type only works with a Page object');
            }

            if (!$data instanceof PagePlaceholder && !$data instanceof PageLink) {
                $form
                    ->add('pageSubtitle', I18nTextType::class, array(
                        'label' => 'subtitle'
                    ))
                    ->add('autoMetas', CheckboxType::class, array(
                        'label' => 'automatic.metas',
                        'required' => false
                    ))
                    ->add('metaTitle', I18nTextType::class, array(
                        'label' => 'meta.title'
                    ))
                    ->add('metaDescription', I18nTextareaType::class, array(
                        'label' => 'meta.description'
                    ))
                    ->add('redirections', CollectionType::class, array(
                        'label' => 'redirections',
                        'entry_type' => RedirectionType::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false, // disable replacement of collection (setDomainNames) and uses add/remove methods
                        'required' => false,
                        'entry_options' => array(
                            'label' => false,
                            'subfields_label' => false,
                            'include_page_field' => false
                        )
                    ))
                    ->add('metaNoIndex', CheckboxType::class, array(
                        'label' => 'site.meta.no.index',
                        'required' => false
                    ))
                ;
            } else {
                $form
                    ->remove('pageTitle')
                    ->remove('differentPageTitle')
                ;
            }

            $form->add('pageType', $data->getFormType(), array(
                'label' => false,
                'inherit_data' => true
            ));
        });


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // remove slug field if autoSlug or homePage is checked
            if ((isset($data['autoSlug']) && true === $data['autoSlug']) || (isset($data['homePage']) && true === $data['homePage'])) {
                unset($data['slug']);
                $form->remove('slug');
            }

            // remove metaTitle and metaDescription fields if autoMetas or homePage is checked
            if ((isset($data['autoMetas']) && true === $data['autoMetas'])) {
                unset($data['metaTitle']);
                unset($data['metaDescription']);
                $form->remove('metaTitle');
                $form->remove('metaDescription');
            }

            // if differentPageTitle is not checked, copy menu title in page title
            if (isset($data['differentPageTitle']) && false === $data['differentPageTitle']) {
                unset($data['pageTitle']);
                $form->remove('pageTitle');
            }

            if (!$this->authorizationChecker->isGranted('SUPER_ADMIN')) {
                unset($data['isPageParametersLocked']);
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Azimut\Bundle\FrontofficeBundle\Entity\Page'
        ));
    }
}
