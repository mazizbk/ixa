<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 18:38:39
 */

namespace Azimut\Bundle\ShopBundle\Service\Delivery;

use Symfony\Component\Translation\TranslatorInterface;
use Azimut\Bundle\ShopBundle\Entity\DeliveryTracking;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractDeliveryProvider implements DeliveryProviderInterface
{
    /**
     * @var string id of the service in container
     */
    protected $id;

    /**
     * Route name to handle specific action before next step
     * @var string|null
     */
    protected $intermediateRoute;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * Get id (service id in container)
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id (service id in container)
     *
     * @param string $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntermediateRoute()
    {
        return $this->intermediateRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIntermediateRoute()
    {
        return null != $this->intermediateRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDeliveryTracking()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDeliveryTracking(DeliveryTracking $deliveryTracking)
    {
        return $this;
    }
}
