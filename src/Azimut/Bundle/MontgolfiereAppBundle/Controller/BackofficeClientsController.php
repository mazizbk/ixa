<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Client;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber\UploadSubscriber;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\FilterClientsType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class BackofficeClientsController extends AbstractBackofficeEntityController
{
    protected static $entityClass = Client::class;
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Clients/index.html.twig';
    protected static $readView = '@AzimutMontgolfiereApp/Backoffice/Clients/read.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Clients/new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Clients/edit.html.twig';
    protected static $routePrefix = 'azimut_montgolfiere_app_backoffice_clients';
    protected static $routeParameterName = 'slug';
    protected static $routeParameterValue = 'slug';
    protected static $translationPrefix = 'montgolfiere.backoffice.clients';

    public function logoAction(Client $client, UploadSubscriber $uploadSubscriber)
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$client->getFilename()) {
            throw $this->createNotFoundException();
        }

        return $this->file($uploadSubscriber->getUploadsDir().DIRECTORY_SEPARATOR.$uploadSubscriber->getTargetDir().DIRECTORY_SEPARATOR.$client->getFilename());
    }

    protected function getFilterForm(array $defaultData = [], array $options = [])
    {
        return $this->createForm(FilterClientsType::class, $defaultData, $options);
    }

    protected function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();
        if ($order = $filterForm->get('orderBy')->getData()) {
            switch ($order) {
                case 'name':
                    $queryBuilder->orderBy('e.corporateName');
                    break;
                case 'workforce':
                    $queryBuilder->orderBy('e.workforce', 'desc');
                    break;
                case 'turnover':
                    $queryBuilder->orderBy('e.turnover', 'DESC');
                    break;
            }
        }
        if ($name = $filterForm->get('name')->getData()) {
            $queryBuilder
                ->andWhere($expr->orX(
                    $expr->like('e.corporateName', ':name'),
                    $expr->like('e.tradingName', ':name')
                ))
                ->setParameter(':name', '%'.$name.'%');
        }
        if ($type = $filterForm->get('type')->getData()) {
            $queryBuilder
                ->andWhere('e.clientStatus = :type')
                ->setParameter(':type', $type)
            ;
        }
    }

    protected function isFiltered(FormInterface $filterForm)
    {
        return $filterForm->get('name')->getData() || $filterForm->get('type')->getData();
    }

    protected function getEntityQuery()
    {
        return parent::getEntityQuery()->orderBy('e.corporateName');
    }

    public function iframeSelectorAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $query = $this->getEntityQuery();
        $filterForm = $this->getFilterForm([
            'type' => Client::STATUS_CLIENT,
        ], [
            'display_all_route' => 'azimut_montgolfiere_app_backoffice_clients_iframe_selector',
        ]);
        $filterForm->handleRequest($request);
        $this->handleFilterForm($filterForm, $query);
        $isFilteredView = $this->isFiltered($filterForm);
        $perPage = $filterForm->get('perpage')->getData()?$filterForm->get('perpage')->getData():20;
        $entities = $this->paginator->paginate($query, $request->get('page', 1), $perPage);

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Clients/iframeSelector.html.twig', [
            'entities' => $entities,
            'filterForm' => $filterForm->createView(),
            'isFilteredView' => $isFilteredView,
        ]);
    }

    public function impersonateAction(Client $client)
    {
        $hrUsers = $client->getContacts()->filter(function(ClientContact $contact): bool {return $contact->getIsHeadOfHumanResources();});
        if(count($hrUsers) === 0) {
            $this->addFlash('warning', $this->translator->trans('montgolfiere.backoffice.clients.contacts.flash.no_hr_contact_exists'));

            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_clients_contacts', ['slug' => $client->getSlug(),]);
        }

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_clients_contacts_impersonate', ['slug' => $client->getSlug(), 'contact' => $hrUsers->first()->getId(),]);
    }

}
