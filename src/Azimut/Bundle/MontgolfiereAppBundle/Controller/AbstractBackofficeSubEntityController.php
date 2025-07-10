<?php
/**
 * Created by mikaelp on 31-Jul-18 2:25 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonLinkType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonsType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\HasTypeOption;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractBackofficeSubEntityController extends AbstractController
{
    use BackofficeXHRController;

    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Class name of owning parent
     * @var string
     */
    protected static $parentClass = null;

    /**
     * Name of the property, on the parent, that refers to the sub entity
     * @var string
     */
    protected static $parentPropertyName = null;

    /**
     * Class name of the sub entity
     * @var string
     */
    protected static $subEntityClass = null;

    /**
     * Name of the property, on the sub entity, that refers to its parent
     * @var string
     */
    protected static $subEntityPropertyName = null;

    /**
     * Path name to the Twig template that will be rendered to list the sub entities
     * @var string
     */
    protected static $listView = null;

    /**
     * Path name to the Twig template that will be rendered when creating a new sub entity
     * @var string
     */
    protected static $createView = null;

    /**
     * Path name to the Twig template that will be rendered when updating an existing sub entity
     * @var string
     */
    protected static $updateView = null;

    /**
     * Route prefix for generating URLs
     * @var string
     */
    protected static $routesPrefix = null;

    /**
     * Translation prefix for messages
     * @var string
     */
    protected static $translationPrefix = null;

    /**
     * The name of the route's parameter used to get the parent entity
     * @var string
     */
    protected static $parentRouteParamName = 'slug';

    /**
     * The name of the parent's attribute that is the value of the route parameter
     * @var string
     */
    protected static $parentRouteParamValue = 'slug';

    /**
     * The name of the route's parameter used to get the sub entity
     * @var string
     */
    protected static $subEntityRouteParamName = 'id';

    /**
     * The name of the sub entity's attribute that is the value of the route parameter
     * @var string
     */
    protected static $subEntityRouteParamValue = 'id';

    /**
     * When true, the actions accept XMLHTTPRequests, the views do not need to be set and the listAction does not load entities from the database
     * @var bool
     */
    protected static $xhrOnly = false;

    /**
     * List of serialization groups to use when serializing the sub-entities list
     * @var string[]
     */
    protected static $xhrListSerializationGroups = [];

    protected static $disableSoftdeleteable = false;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(RouterInterface $router, TranslatorInterface $translator, PropertyAccessorInterface $propertyAccessor, PaginatorInterface $paginator, SerializerInterface $serializer)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->propertyAccessor = $propertyAccessor;
        $this->paginator = $paginator;
        $this->serializer = $serializer;

        if(!$this::$parentClass) {
            throw new \RuntimeException(get_class($this).'::$parentClass needs to be overridden');
        }
        if(!$this::$parentPropertyName) {
            throw new \RuntimeException(get_class($this).'::$parentPropertyName needs to be overridden');
        }
        if(!$this::$subEntityClass) {
            throw new \RuntimeException(get_class($this).'::$subEntityClass needs to be overridden');
        }
        if(!$this::$subEntityPropertyName) {
            throw new \RuntimeException(get_class($this).'::$subEntityPropertyName needs to be overridden');
        }
        if(!$this::$listView) {
            throw new \RuntimeException(get_class($this).'::$listView needs to be overridden');
        }
        if(false === $this::$xhrOnly) {
            if(!$this::$createView) {
                throw new \RuntimeException(get_class($this).'::$createView needs to be overridden');
            }
            if(!$this::$updateView) {
                throw new \RuntimeException(get_class($this).'::$updateView needs to be overridden');
            }
        }
        if(!$this::$routesPrefix) {
            throw new \RuntimeException(get_class($this).'::$routesPrefix needs to be overridden');
        }
        if(!$this::$translationPrefix) {
            throw new \RuntimeException(get_class($this).'::$translationPrefix needs to be overridden');
        }
    }

    /**
     * @param         $entity
     * @param Request $request
     * @return Response|View
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     */
    public function listAction($entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->disableSoftDeleteableIfConfigured();

        $isXmlHttpRequest = $this::isXmlHttpRequest($request);
        if(!$isXmlHttpRequest && true === $this::$xhrOnly) {
            $subEntities = $deleteForms = [];
        }
        else {
            $query = $this->getEntityQuery();
            $query
                ->andWhere('e.'.$this::$subEntityPropertyName.' = :parent')
                ->setParameter(':parent', $entity)
            ;
            if($this instanceof SupportsFilteringEntityController) {
                /** @var FormInterface $filterForm */
                $subEntities = $this->filterAndPaginate($entity, $query, $request, $filterForm, $isFilteredView);
            }
            else {
                $subEntities = $query->getQuery()->getResult();
            }

            $deleteForms = [];

            if(!$isXmlHttpRequest) {
                foreach ($subEntities as $subEntity) {
                    $deleteForms[$this->getIdentifier($subEntity)] = $this->createDeleteForm($entity, $subEntity)->createView();
                }
            }
        }

        if($isXmlHttpRequest) {
            return $this->serialize($subEntities, $this::$xhrListSerializationGroups);
        }

        return $this->render($this::$listView, array_merge([
            'entity' => $entity,
            'subEntities' => $subEntities,
            'deleteForms' => $deleteForms,
            'isFilteredView' => isset($isFilteredView)?$isFilteredView:null,
            'filterForm' => isset($filterForm)?$filterForm->createView():null,
        ], $this->getListAdditionalViewParameters($entity)));
    }

    protected function getListAdditionalViewParameters($entity)
    {
        return [];
    }

    /**
     * @param         $entity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     */
    public function createAction($entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $subEntityClass = $this::$subEntityClass;
        $subEntity = new $subEntityClass;
        $this->propertyAccessor->setValue($subEntity, $this::$subEntityPropertyName, $entity);

        $isXMLHTTPRequest = self::isXMLHTTPRequest($request);
        $form = $this->createEditForm($subEntity, $entity, 'create', $isXMLHTTPRequest);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subEntity);
            $em->flush();

            if($isXMLHTTPRequest) {
                return $this->serialize($subEntity, $this::$xhrListSerializationGroups);
            }

            $this->addFlash('success', $this->translator->trans($this::$translationPrefix.'.flash.subentity_created'));

            return $this->redirect($this->getListUrl($entity));
        }

        if($isXMLHTTPRequest) {
            throw new BadRequestHttpException();
        }

        return $this->render($this::$createView, [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param         $entity
     * @param         $subEntity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     * @ParamConverter("subEntity", converter="azimut_backoffice_subentity")
     */
    public function updateAction($entity, $subEntity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$this->subEntityBelongsToEntity($subEntity, $entity)) {
            throw new BadRequestHttpException('$subEntity does not belong to $entity');
        }

        $isXMLHTTPRequest = self::isXMLHTTPRequest($request);
        $form = $this->createEditForm($subEntity, $entity, 'update', $isXMLHTTPRequest);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            if($isXMLHTTPRequest) {
                return $this->serialize($subEntity, $this::$xhrListSerializationGroups);
            }

            $this->addFlash('success', $this->translator->trans($this::$translationPrefix.'.flash.subentity_updated'));

            return $this->redirect($this->getListUrl($entity));
        }

        if($isXMLHTTPRequest) {
            throw new BadRequestHttpException();
        }

        return $this->render($this::$updateView, [
            'entity' => $entity,
            'subEntity' => $subEntity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param $entity
     * @param $subEntity
     * @param Request $request
     * @return Response
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     * @ParamConverter("subEntity", converter="azimut_backoffice_subentity")
     */
    public function deleteAction($entity, $subEntity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$this->subEntityBelongsToEntity($subEntity, $entity)) {
            throw new BadRequestHttpException('$subEntity does not belong to $entity');
        }

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $em->remove($subEntity);
        $em->flush();

        if($this::isXMLHTTPRequest($request)) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->addFlash('success', $this->translator->trans($this::$translationPrefix.'.flash.subentity_deleted'));

        return $this->redirect($this->getListUrl($entity));
    }

    protected function createDeleteForm($entity, $subEntity)
    {
        if(!is_a($entity, $this::$parentClass)) {
            throw new \InvalidArgumentException('$entity must be an instance of '.$this::$parentClass);
        }
        if(!is_a($subEntity, $this::$subEntityClass)) {
            throw new \InvalidArgumentException('$subEntity must be an instance of '.$this::$subEntityClass);
        }

        $id = $this->getIdentifier($subEntity);
        if(is_null($id)) {
            throw new \InvalidArgumentException('Class '.$this::$subEntityClass.' must have an identifier to be deletable');
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->get('form.factory')->createNamedBuilder('delete_'.(new \ReflectionClass($this::$subEntityClass))->getShortName().'_'.$id)
            ->setAction($this->getDeleteUrl($entity, $subEntity))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    protected function createEditForm($subEntity, $entity, string $type, bool $isXHR): FormInterface
    {
        $formOptions = $this->getEditFormOptions($subEntity, $entity, $type);
        if(is_a($this->getFormType(), HasTypeOption::class, true)) {
            $formOptions['type'] = $type;
        }
        if($isXHR) {
            $formOptions['csrf_protection'] = false;
        }
        $form = $this->createForm($this->getFormType(), $subEntity, $formOptions);
        $form->add('buttons', ButtonsType::class);
        $form->get('buttons')
             ->add('goback', ButtonLinkType::class, [
                 'route' => $this::$routesPrefix,
                 'route_params' => [
                     $this::$parentRouteParamName => $this->propertyAccessor->getValue($entity, $this::$parentRouteParamValue),
                 ],
                 'text' => $this::$translationPrefix.'.back_to_list',
             ])
             ->add('submit', SubmitType::class, [
                 'label' => 'montgolfiere.backoffice.common.save',
                 'attr' => [
                     'class' => 'btn btn-primary',
                 ]
             ])
        ;

        return $form;
    }

    protected function getEditFormOptions($subEntity, $entity, string $type): array
    {
        return [];
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

    protected function getDeleteUrl($entity, $subEntity)
    {
        return $this->router->generate($this::$routesPrefix.'_delete', [
            $this::$parentRouteParamName => $this->propertyAccessor->getValue($entity, $this::$parentRouteParamValue),
            $this::$subEntityRouteParamName => $this->propertyAccessor->getValue($subEntity, $this::$subEntityRouteParamValue),
        ]);
    }

    protected function getListUrl($entity)
    {
        return $this->router->generate($this::$routesPrefix, [
            $this::$parentRouteParamValue => $this->propertyAccessor->getValue($entity, $this::$parentRouteParamValue),
        ]);
    }

    protected function getFormType()
    {
        $class = $this::$subEntityClass;

        return str_replace('\\Entity\\', '\\Form\\Type\\', $class).'Type';
    }

    /**
     * @param $subEntity
     * @param $entity
     * @return bool
     */
    abstract protected function subEntityBelongsToEntity($subEntity, $entity);

    public function getEntity($slug)
    {
        $this->disableSoftDeleteableIfConfigured();
        $em = $this->getDoctrine();

        return $em->getRepository($this::$parentClass)->findOneBy([$this::$parentRouteParamValue => $slug,]);
    }

    public function getSubEntity($id)
    {
        $this->disableSoftDeleteableIfConfigured();
        $em = $this->getDoctrine();

        return $em->getRepository($this::$subEntityClass)->findOneBy([$this::$subEntityRouteParamValue => $id,]);
    }

    /**
     * @return string
     */
    public function getParentRouteParamName()
    {
        return $this::$parentRouteParamName;
    }

    /**
     * @return string
     */
    public function getSubEntityRouteParamName()
    {
        return $this::$subEntityRouteParamName;
    }

    protected function getEntityQuery()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getDoctrine()->getRepository($this::$subEntityClass);

        return $repo->createQueryBuilder('e');
    }

    /**
     * @param               $entity
     * @param QueryBuilder  $query
     * @param Request       $request
     * @param FormInterface &$filterForm
     * @param boolean       &$isFilteredView Will be set to true or false indicating if results were filtered
     * @return AbstractPagination
     */
    final protected function filterAndPaginate($entity, QueryBuilder $query, Request $request, &$filterForm, &$isFilteredView)
    {
        if(!$this instanceof SupportsFilteringEntityController) {
            throw new \LogicException('Cannot filter if controller does not implement SupportsFilteringEntityController interface');
        }
        $isFilteredView = false;

        /* PHPDoc won't handle typing $this, so we use $that... :) */
        /** @var AbstractBackofficeSubEntityController|SupportsFilteringEntityController $that */
        $that = $this;

        $filterForm = $that->getFilterForm($entity);
        $filterForm->handleRequest($request);
        if($filterForm->isSubmitted() && $filterForm->isValid()) {
            $that->handleFilterForm($filterForm, $query, $entity);
            $isFilteredView = $that->isFiltered($filterForm);
        }

        $perPage = $filterForm->get('perpage')->getData()?$filterForm->get('perpage')->getData():100;
        $paginated = $that->paginator->paginate($query, $request->get('page', 1), $perPage);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $paginated;
    }

    protected function disableSoftDeleteableIfConfigured(): void
    {
        if(!$this::$disableSoftdeleteable) {
            return;
        }

        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        if($em->getFilters()->isEnabled('softdeleteable')) {
            $em->getFilters()->disable('softdeleteable');
        }
    }

}
