<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-03 09:50:17
 */

namespace Azimut\Bundle\ShopBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Azimut\Bundle\FrontofficeBundle\Service\FrontService;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Exception\WrongFirewallException;

class BasketService
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Order
     */
    private $basket;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var FrontService
     */
    private $frontService;

    /**
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager, FrontService $frontService, FirewallMapInterface $firewallMap, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->frontService = $frontService;

        $request = $requestStack->getCurrentRequest();
        if (null != $request) {
            $this->firewallName = $firewallMap->getFirewallConfig($request)
                ->getName()
            ;
        }

        // Caution : initialization of basket has been removed from constructor because it depends on request locale, which is set in a kernel.request listener, not yet executed when constructing this service
    }

    /**
     * Get basket
     *
     * @return Order
     */
    public function getBasket()
    {
        if ('frontoffice' != $this->firewallName) {
            throw new WrongFirewallException("Basket is only availlable inside frontoffice firewall");

        }

        if (null == $this->basket) {
            $this->initBasket();
        }

        return $this->basket;
    }

    /**
     * Store basket in DB and session
     *
     * @return self
     */
    public function storeBasket()
    {
        if (null === $this->basket) {
            throw new \Exception("Cannot store null basket");
        }
        $this->entityManager->flush();
        $this->session->set('basketId', $this->basket->getId());

        return $this;
    }

    /**
     * Close basket
     *
     * @return self
     */
    public function closeBasket()
    {
        $this->session->set('basketId', null);

        return $this;
    }

    /**
     * Initiliase basket (create or retrieve Order object)
     *
     * @return self
     */
    private function initBasket()
    {
        // Create basket only inside frontoffice firewall
        if ('frontoffice' != $this->firewallName) {
            return;
        }

        $site = $this->frontService->getCurrentSite();

        if (null != $this->session->get('basketId')) {
            $this->basket = $this->entityManager->getRepository(Order::class)->find($this->session->get('basketId'));
        }

        //If there is no order in state basket, or if the order found is already paid
        if (null === $this->basket || $this->basket->getStatus() >= OrderStatusProvider::STATUS_PAID) {
            $this->basket = new Order();
            $this->basket
                ->setSite($site)
            ;
            $this->entityManager->persist($this->basket);
            // NB: we don't flush basket yet, because it is empty and we don't want to create a new DB
            // item on each visit. It will be stored by calling storeBasket method when the first product
            // will be inserted (see ApiBasketController)
        }

        // Set or update basket locale based on requested user locale
        $this->basket
            ->setLocale($this->frontService->getLocale())
        ;

        return $this;
    }

    /**
     * Reset the basket status
     * Meant to be used in case a validated order is unvalidated (ex: a user selected online paiement, then went back in purchase tunnel)
     */
    public function resetBasketStatus()
    {
        if (null == $this->basket) {
            $this->initBasket();
        }

        if (null != $this->basket->getStatus()) {
            $this->basket->setStatus(null);
            $this->entityManager->flush();
        }
    }
}
