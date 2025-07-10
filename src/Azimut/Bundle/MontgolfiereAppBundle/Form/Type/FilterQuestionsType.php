<?php
/**
 * Created by mikaelp on 01-Aug-18 3:27 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\QuestionTag;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterQuestionsType extends AbstractType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $request = $this->requestStack->getCurrentRequest();
        $builder
            ->add('question', TextType::class, [
                'label' => 'montgolfiere.backoffice.questions.fields.question',
                'required' => false,
            ])
            ->add('item', EntityType::class, [
                'class' => Item::class,
                'choice_label' => function(Item $item) use ($request): string {return $item->getName()[$request->getLocale()];},
                'group_by' => function(Item $item) use ($request): string {return $item->getTheme()->getName()[$request->getLocale()].' (V'.$item->getTheme()->getAnalysisVersion()->getId().')';},
                'required' => false,
                'query_builder' => function(EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('i')
                        ->leftJoin('i.theme', 't')
                        ->leftJoin('t.analysisVersion', 'a')
                        ->orderBy('a.id', 'DESC')
                        ->addOrderBy('t.position', 'ASC')
                        ->addOrderBy('i.position', 'ASC')
                    ;
                },
            ])
            ->add('tag', EntityType::class, [
                'class' => QuestionTag::class,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choice_label' => 'name',
                'label' => 'montgolfiere.backoffice.questions.fields.tag',
            ])
            ->add('perpage', IntegerType::class, [
                'label' => 'montgolfiere.backoffice.common.filter_form.per_page',
                'required' => false,
                'data' => 100,
            ])
        ;
        if($options['allow_archived']) {
            $builder
                ->add('show_archived', CheckboxType::class, [
                    'label' => 'montgolfiere.backoffice.questions.list.show_archived',
                    'required' => false,
                ])
            ;
        }
        $builder
            ->add('detailed_mode', CheckboxType::class, [
                'label' => 'montgolfiere.backoffice.questions.list.detailed_mode',
                'required' => false,
            ])
        ;
        $buttons = $builder->add('buttons', ButtonsType::class)->get('buttons');
        $buttons
            ->add('submit', SubmitType::class, ['label' => 'montgolfiere.backoffice.common.filter_form.search', 'attr' => ['class' => 'btn-primary']])
            ->add('viewall', ButtonLinkType::class, [
                'route' => $options['display_all_route'],
                'route_params' => array_merge(['displayAll' => true], $options['display_all_route_params']),
                'text' => 'montgolfiere.backoffice.common.filter_form.show_all',
                'color' => 'default'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
                'method' => 'GET',
                'display_all_route' => 'azimut_montgolfiere_app_backoffice_questions_homepage',
                'display_all_route_params' => [],
                'allow_archived' => true,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }

}
