<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\ImpersonatedFrontofficeUserToken;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\FilterConsultantsType;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BackofficeConsultantsController extends AbstractBackofficeEntityController
{
    protected static $entityClass = Consultant::class;
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Consultants/index.html.twig';
    protected static $readView = 'none';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Consultants/new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Consultants/edit.html.twig';
    protected static $routePrefix = 'azimut_montgolfiere_app_backoffice_consultants';
    protected static $routeParameterName = 'id';
    protected static $routeParameterValue = 'id';
    protected static $translationPrefix = 'montgolfiere.backoffice.consultants';

    /**
     * @var bool
     */
    protected $allowFrontUserImpersonation;

    public function __construct(PaginatorInterface $paginator, PropertyAccessorInterface $propertyAccessor, TranslatorInterface $translator, SerializerInterface $serializer, bool $allowFrontUserImpersonation)
    {
        parent::__construct($paginator, $propertyAccessor,$translator, $serializer);
        $this->allowFrontUserImpersonation = $allowFrontUserImpersonation;
    }

    protected function getFilterForm(array $defaultData = [], array $options = [])
    {
        return $this->createForm(FilterConsultantsType::class, $defaultData, $options);
    }

    protected function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();
        if ($name = $filterForm->get('name')->getData()) {
            $queryBuilder
                ->andWhere(
                    $expr->like('e.lastName', ':name')
                )
                ->setParameter(':name', '%'.$name.'%');
        }
    }

    protected function isFiltered(FormInterface $filterForm)
    {
        return $filterForm->get('name')->getData();
    }

    protected function getEntityQuery()
    {
        return parent::getEntityQuery()->orderBy('e.lastName');
    }

    public function impersonateAction(Consultant $consultant, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!($this->isGranted('SUPER_ADMIN') || $this->allowFrontUserImpersonation && $this->isGranted('GLOBAL_IMPERSONATE_USER'))) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $impersonateToken = new ImpersonatedFrontofficeUserToken();
        $token = bin2hex(random_bytes(10));
        $impersonateToken
            ->setToken($token)
            ->setCreationDateTime(new \DateTime())
            ->setIp($request->getClientIp())
            ->setLoggedUser($this->getUser())
            ->setImpersonatedUser($consultant)
        ;
        $em->persist($impersonateToken);
        $em->flush();

        return $this->redirectToRoute('azimut_frontofficesecurity_impersonate', ['token' => $impersonateToken->getToken(),]);
    }
}