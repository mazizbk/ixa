<?php
/**
 * Created by mikaelp on 01-Aug-18 2:45 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonLinkType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonsType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\HasTypeOption;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractBackofficeEntityController extends AbstractController
{
    use BackofficeXHRController;

    protected static $entityClass = null;
    protected static $listView = null;
    protected static $readView = null;
    protected static $createView = null;
    protected static $updateView = null;
    protected static $routePrefix = null;
    protected static $routeParameterName = null;
    protected static $routeParameterValue = null;
    protected static $translationPrefix = null;
    protected static $xhrReadSerializationGroups = [];

    /**
     * @var PaginatorInterface
     */
    protected $paginator;
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(PaginatorInterface $paginator, PropertyAccessorInterface $propertyAccessor, TranslatorInterface $translator, SerializerInterface $serializer)
    {
        $this->paginator = $paginator;
        $this->propertyAccessor = $propertyAccessor;

        if(!$this::$entityClass) {
            throw new \RuntimeException(get_class($this).'::$entityClass needs to be overridden');
        }
        if(!$this::$listView) {
            throw new \RuntimeException(get_class($this).'::$listView needs to be overridden');
        }
        if(!$this::$readView) {
            throw new \RuntimeException(get_class($this).'::$readView needs to be overridden');
        }
        if(!$this::$createView) {
            throw new \RuntimeException(get_class($this).'::$createView needs to be overridden');
        }
        if(!$this::$updateView) {
            throw new \RuntimeException(get_class($this).'::$updateView needs to be overridden');
        }
        if(!$this::$routePrefix) {
            throw new \RuntimeException(get_class($this).'::$routePrefix needs to be overridden');
        }
        if(!$this::$routeParameterName) {
            throw new \RuntimeException(get_class($this).'::$routeParameterName needs to be overridden');
        }
        if(!$this::$routeParameterName) {
            throw new \RuntimeException(get_class($this).'::$routeParameterName needs to be overridden');
        }
        if(!$this::$routeParameterValue) {
            throw new \RuntimeException(get_class($this).'::$routeParameterValue needs to be overridden');
        }
        if(!$this::$translationPrefix) {
            throw new \RuntimeException(get_class($this).'::$translationPrefix needs to be overridden');
        }
        $this->translator = $translator;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var FormInterface $filterForm */
        $entities = $this->filterAndPaginate($request, $filterForm, $isFilteredView);

        $deleteForms = [];
        foreach ($entities as $entity) {
            $deleteForms[$this->getIdentifier($entity)] = $this->createDeleteForm($entity)->createView();
        }

        return $this->render($this::$listView, [
            'entities' => $entities,
            'filterForm' => $filterForm->createView(),
            'isFilteredView' => $isFilteredView,
            'deleteForms' => $deleteForms,
        ]);
    }

    /**
     * @param $entity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_entity")
     */
    public function readAction($entity, Request $request)
    {
        if($this::isXmlHttpRequest($request)) {
            return $this->serialize($entity, $this::$xhrReadSerializationGroups);
        }

        return $this->render($this::$readView, [
            'entity' => $entity,
        ]);
    }

    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $class = $this::$entityClass;
        $entity = new $class;

        $formOptions = [];
        if(is_a($this->getFormType(), HasTypeOption::class, true)) {
            $formOptions['type'] = 'create';
        }
        $form = $this->createForm($this->getFormType(), $entity, $formOptions);
        $form->add('buttons', ButtonsType::class);
        $form->get('buttons')
             ->add('cancel', ButtonLinkType::class, [
                 'color' => 'default',
                 'text' => 'montgolfiere.backoffice.common.cancel',
                 'route' => $this::$routePrefix.'_homepage',
             ])
             ->add('submit', SubmitType::class, [
                 'attr' => ['class' => 'btn btn-primary',],
                 'label' => 'montgolfiere.backoffice.common.save',
             ])
        ;

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->addFlash('success', $this->translator->trans($this::$translationPrefix.'.flash.entity_created'));

            if ($this::$readView != 'none') {
                return $this->redirectToRoute($this::$routePrefix.'_read', [
                    $this::$routeParameterName => $this->propertyAccessor->getValue($entity, $this::$routeParameterValue),
                ]);
            }
            return $this->redirectToRoute($this::$routePrefix.'_homepage');
        }

        return $this->render($this::$createView, [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param object  $entity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_entity")
     */
    public function updateAction($entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $formOptions = [];
        if(is_a($this->getFormType(), HasTypeOption::class, true)) {
            $formOptions['type'] = 'update';
        }
        $form = $this->createForm($this->getFormType(), $entity, $formOptions);
        $form->add('buttons', ButtonsType::class);
        $form->get('buttons')
             ->add('cancel', ButtonLinkType::class, [
                 'color' => 'default',
                 'text' => 'montgolfiere.backoffice.common.cancel',
                 'route' => $this::$readView != 'none' ? $this::$routePrefix.'_read' : $this::$routePrefix.'_homepage',
                 'route_params' => [
                     $this::$routeParameterName => $this->propertyAccessor->getValue($entity, $this::$routeParameterValue),
                 ],
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

            $this->addFlash('success', $this->translator->trans($this::$translationPrefix.'.flash.entity_updated'));

            if ($this::$readView != 'none') {
                return $this->redirectToRoute($this::$routePrefix.'_read', [
                    $this::$routeParameterName => $this->propertyAccessor->getValue($entity, $this::$routeParameterValue),
                ]);
            }
            return $this->redirectToRoute($this::$routePrefix.'_homepage');
        }

        return $this->render($this::$updateView, [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param object  $entity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_entity")
     */
    public function deleteAction($entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $form = $this->createDeleteForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', $this->translator->trans($this::$translationPrefix.'.flash.entity_deleted'));
        }

        return $this->redirectToRoute($this::$routePrefix.'_homepage');
    }

    /**
     * @param Request       $request
     * @param FormInterface &$filterForm
     * @param boolean       &$isFilteredView Will be set to true or false indicating if results were filtered
     * @return AbstractPagination
     */
    final protected function filterAndPaginate(Request $request, &$filterForm, &$isFilteredView)
    {
        $isFilteredView = false;
        if($request->query->has('displayAll')) {
            $request->getSession()->remove($this->getClassShortName().'_search');
        }

        $query = $this->getEntityQuery();
        $filterForm = $this->getFilterForm();
        $filterForm->handleRequest($request);
        if(!$filterForm->isSubmitted()) {
            $this->restoreSearch($request, $filterForm);
        }
        if($filterForm->isSubmitted() && $filterForm->isValid()) {
            $this->handleFilterForm($filterForm, $query);
            $isFilteredView = $this->isFiltered($filterForm);
            $this->saveSearchToSession($request);
        }
        if(!$filterForm->isSubmitted()) { // Search restore "submits" the form. If it still has not been submitted, filter with default
            $this->filterDefault($query);
        }

        $perPage = $filterForm->get('perpage')->getData()?$filterForm->get('perpage')->getData():100;
        $paginated = $this->paginator->paginate($query, $request->get('page', 1), $perPage);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $paginated;
    }

    /**
     * @param object $entity
     * @return FormInterface
     */
    protected function createDeleteForm($entity)
    {
        return $this->get('form.factory')->createNamedBuilder('delete_'.$this->getIdentifier($entity))
            ->setAction($this->generateUrl($this::$routePrefix.'_delete', [
                $this::$routeParameterName => $this->propertyAccessor->getValue($entity, $this::$routeParameterValue)
            ]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @return FormInterface
     */
    abstract protected function getFilterForm();

    abstract protected function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder);

    abstract protected function isFiltered(FormInterface $filterForm);

    protected function saveSearchToSession(Request $request)
    {
        if($request->get('filter')) {
            $request->getSession()->set($this->getClassShortName().'_search', [
                'expires' => new \DateTime('+10 minutes'),
                'data' => $request->get('filter')
            ]);
        }
    }

    protected function restoreSearch(Request $request, FormInterface $filterForm)
    {
        $search = $request->getSession()->get($this->getClassShortName().'_search');
        if(!$search) {
            return;
        }
        if($search['expires'] < new \DateTime()) {
            $request->getSession()->remove($this->getClassShortName().'_search');
            return;
        }

        $filterForm->submit($search['data']);
    }

    protected function getIdentifier($entity)
    {
        if(!is_object($entity)) {
            throw new \InvalidArgumentException('$entity must be an object');
        }
        $em = $this->get('doctrine')->getManager();
        $metadata = $em->getClassMetadata(get_class($entity));
        $id = $metadata->getIdentifierValues($entity);
        if(empty($id)) {
            return null;
        }
        $id = implode('_', $id);

        return $id;
    }

    protected function getClassShortName()
    {
        $FQDNParts = explode('\\', $this::$entityClass);

        return strtolower(array_pop($FQDNParts));
    }

    protected function getEntityQuery()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository($this::$entityClass);
        return $repo->createQueryBuilder('e');
    }

    public function getRouteParameterName()
    {
        return $this::$routeParameterName;
    }

    public function getEntity($parameterValue)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        return $em->getRepository($this::$entityClass)->findOneBy([
            $this::$routeParameterValue => $parameterValue,
        ]);
    }

    protected function getFormType()
    {
        $class = $this::$entityClass;

        return str_replace('\\Entity\\', '\\Form\\Type\\', $class).'Type';
    }

    /**
     * @param QueryBuilder $queryBuilder
     * This method is called when no search attributes are set
     */
    protected function filterDefault(QueryBuilder $queryBuilder)
    {

    }
}
