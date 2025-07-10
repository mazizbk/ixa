<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 09:56:06
 */

namespace Azimut\Bundle\ShopBundle\Service\Payment;

use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractPaymentProvider implements PaymentProviderInterface
{
    /**
     * @var string id of the service in container
     */
    protected $id;

    /**
     * Route name to handle action
     * @var string
     */
    protected $route;

    /**
     * Is payment deferred in time (like a check payment)
     * @var boolean
     */
    protected $isDeferred = false;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * {@inheritdoc}
     */
    public function isDeferred()
    {
        return $this->isDeferred;
    }
}
