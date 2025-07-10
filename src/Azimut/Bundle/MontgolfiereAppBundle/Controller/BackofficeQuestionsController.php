<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\QuestionTag;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonLinkType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonsType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\FilterQuestionsType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\QuestionTagType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BackofficeQuestionsController extends AbstractBackofficeEntityController
{
    protected static $entityClass = Question::class;
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Questions/index.html.twig';
    protected static $readView = '@AzimutMontgolfiereApp/Backoffice/Questions/read.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Questions/new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Questions/edit.html.twig';
    protected static $routePrefix = 'azimut_montgolfiere_app_backoffice_questions';
    protected static $routeParameterName = 'id';
    protected static $routeParameterValue = 'id';
    protected static $translationPrefix = 'montgolfiere.backoffice.questions';

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(PaginatorInterface $paginator, PropertyAccessorInterface $propertyAccessor, TranslatorInterface $translator, SerializerInterface $serializer,
        EntityManager $entityManager
    )
    {
        parent::__construct($paginator, $propertyAccessor, $translator, $serializer);
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $options
     * @return FormInterface
     */
    protected function getFilterForm(array $options = [])
    {
        return $this->createForm(FilterQuestionsType::class, ['detailed_mode' => true, 'analysisVersion' => $this->entityManager->getRepository(AnalysisVersion::class)->getLastVersion()], $options);
    }

    protected function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();

        if ($question = $filterForm->get('question')->getData()) {
            $queryBuilder
                ->andWhere($expr->orX(
                    $expr->like('e.question', ':question')
                ))
                ->setParameter(':question', '%'.$question.'%');
        }
        if ($filterForm->has('item') && $item = $filterForm->get('item')->getData()) {
            $queryBuilder
                ->andWhere('e.item = :item')
                ->setParameter(':item', $item)
            ;
        }
        if ($tag = $filterForm->get('tag')->getData()) {
            $queryBuilder
                ->andWhere(':tag MEMBER OF e.tags')
                ->setParameter(':tag', $tag)
            ;
        }
        if($filterForm->has('show_archived') && $filterForm->get('show_archived')->getData()) {
            $queryBuilder->getEntityManager()->getFilters()->disable('softdeleteable');
        }
    }

    protected function isFiltered(FormInterface $filterForm)
    {
        return $filterForm->get('question')->getData()
            || ($filterForm->has('theme') && $filterForm->get('theme')->getData())
            || ($filterForm->has('position') && $filterForm->get('position')->getData())
            || $filterForm->get('tag')->getData();
    }

    public function tagsAction()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $tags = $this->getDoctrine()->getRepository(QuestionTag::class)->findBy([], ['name' => 'ASC']);
        $deleteForms = [];
        foreach ($tags as $tag) {
            $deleteForms[$tag->getId()] = $this->createTagDeleteForm($tag)->createView();
        }

        return $this->render('AzimutMontgolfiereAppBundle:Backoffice/Questions/Tags:index.html.twig', [
            'tags' => $tags,
            'deleteForms' => $deleteForms,
        ]);
    }

    public function tagCreateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $tag = new QuestionTag();

        $form = $this->createForm(QuestionTagType::class, $tag);
        $form->add('buttons', ButtonsType::class);
        $form->get('buttons')
             ->add('cancel', ButtonLinkType::class, [
                 'color' => 'default',
                 'text' => 'montgolfiere.backoffice.common.cancel',
                 'route' => 'azimut_montgolfiere_app_backoffice_questions_tags',
             ])
             ->add('submit', SubmitType::class, [
                 'attr' => ['class' => 'btn btn-primary',],
                 'label' => 'montgolfiere.backoffice.common.save',
             ])
        ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.questions.tags.flash.tag_created'));

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_questions_tags');
        }

        return $this->render('AzimutMontgolfiereAppBundle:Backoffice/Questions/Tags:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function tagUpdateAction(QuestionTag $tag, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $form = $this->createForm(QuestionTagType::class, $tag);
        $form->add('buttons', ButtonsType::class);
        $form->get('buttons')
             ->add('cancel', ButtonLinkType::class, [
                 'color' => 'default',
                 'text' => 'montgolfiere.backoffice.common.cancel',
                 'route' => 'azimut_montgolfiere_app_backoffice_questions_tags',
             ])
             ->add('submit', SubmitType::class, [
                 'attr' => ['class' => 'btn btn-primary',],
                 'label' => 'montgolfiere.backoffice.common.save',
             ])
        ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.questions.tags.flash.tag_updated'));

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_questions_tags');
        }

        return $this->render('AzimutMontgolfiereAppBundle:Backoffice/Questions/Tags:update.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    private function createTagDeleteForm(QuestionTag $tag)
    {
        return $this->get('form.factory')->createNamedBuilder('delete_tag_'.$tag->getId())
            ->setAction($this->generateUrl('azimut_montgolfiere_app_backoffice_questions_tags_delete', [
                'id' => $tag->getId(),
            ]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    public function tagDeleteAction(QuestionTag $tag, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $form = $this->createTagDeleteForm($tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tag);
            $em->flush();

            $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.questions.tags.flash.tag_deleted'));
        }

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_questions_tags');
    }

    /**
     * @param Question $question
     * @ParamConverter("entity", converter="azimut_backoffice_entity")
     * @return RedirectResponse
     */
    public function unarchiveAction(Question $question)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $question->setArchivedAt(null);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.questions.flash.question_unarchived'));

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_questions_homepage');
    }

    /**
     * @param Question $question
     * @ParamConverter("entity", converter="azimut_backoffice_entity")
     * @return RedirectResponse
     */
    public function copyAction(Question $question)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $copyNumber = 1;
        $existingLabel = true;
        while($existingLabel){
            $labelSuffix= ' (copie '. $copyNumber .')';
            $questions = $this->getDoctrine()->getManager()->getRepository(Question::class)->findBy(['label' => $question->getLabel() . $labelSuffix]);
            if(count($questions) == 0){
                $existingLabel = false;
            }else{
                $copyNumber++;
            }
        }
        $newQuestion = clone($question);
        $newQuestion->setLabel($question->getLabel() .$labelSuffix);
        $newQuestion->setId(null);
        $this->getDoctrine()->getManager()->persist($newQuestion);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.questions.flash.question_copied'));

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_questions_read', ['id' => $newQuestion->getId()]);
    }
}
