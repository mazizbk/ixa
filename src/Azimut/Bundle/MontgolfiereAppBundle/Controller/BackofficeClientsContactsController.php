<?php
/**
 * Created by mikaelp on 31-Jul-18 2:32 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\FrontofficeSecurityBundle\Entity\ImpersonatedFrontofficeUserToken;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Client;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BackofficeClientsContactsController extends AbstractBackofficeSubEntityController
{
    protected static $parentClass = Client::class;
    protected static $parentPropertyName = 'contacts';
    protected static $subEntityClass = ClientContact::class;
    protected static $subEntityPropertyName = 'client';
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Clients/contacts.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Clients/contacts_new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Clients/contacts_new.html.twig';
    protected static $routesPrefix = 'azimut_montgolfiere_app_backoffice_clients_contacts';
    protected static $translationPrefix = 'montgolfiere.backoffice.clients.contacts';
    protected static $parentRouteParamName = 'slug';
    protected static $parentRouteParamValue = 'slug';
    protected static $subEntityRouteParamName = 'contact';
    protected static $subEntityRouteParamValue = 'id';

    /**
     * @var bool
     */
    protected $allowFrontUserImpersonation;

    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        PropertyAccessorInterface $propertyAccessor,
        PaginatorInterface $paginator,
        SerializerInterface $serializer,
        bool $allowFrontUserImpersonation
    ) {
        parent::__construct($router, $translator, $propertyAccessor, $paginator, $serializer);
        $this->allowFrontUserImpersonation = $allowFrontUserImpersonation;
    }


    /**
     * @param Client        $entity
     * @param ClientContact $subEntity
     * @return bool
     */
    protected function subEntityBelongsToEntity($subEntity, $entity)
    {
        return $entity->getId() === $subEntity->getClient()->getId();
    }

    public function impersonateAction(Client $client, ClientContact $contact, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$this->subEntityBelongsToEntity($contact, $client)) {
            throw new BadRequestHttpException('$subEntity does not belong to $entity');
        }

        if (!($this->isGranted('SUPER_ADMIN') || $this->allowFrontUserImpersonation && $this->isGranted('GLOBAL_IMPERSONATE_USER'))) {
            throw $this->createAccessDeniedException();
        }

        if(!$contact->getIsHeadOfHumanResources() || !$contact->getFrontUser()) {
            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_clients_contacts', ['slug' => $client->getSlug(),]);
        }

        $em = $this->getDoctrine()->getManager();

        $impersonateToken = new ImpersonatedFrontofficeUserToken();
        $token = bin2hex(random_bytes(10));
        $impersonateToken
            ->setToken($token)
            ->setCreationDateTime(new \DateTime())
            ->setIp($request->getClientIp())
            ->setLoggedUser($this->getUser())
            ->setImpersonatedUser($contact->getFrontUser())
        ;
        $em->persist($impersonateToken);
        $em->flush();

        return $this->redirectToRoute('azimut_frontofficesecurity_impersonate', ['token' => $impersonateToken->getToken(),]);
    }

}
