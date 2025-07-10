<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-17 15:42:45
 */

namespace Azimut\Bundle\ShopBundle\Service;

use Symfony\Component\Translation\TranslatorInterface;

class OrderStatusProvider
{
    const STATUS_VALIDATED = 100;
    const STATUS_PAID = 200;
    const STATUS_PROCESSED = 300;
    const STATUS_CANCELLED = 400;
    const STATUS_PAIEMENT_REFUSED = 500;

    /**
     * @var [string]
     */
    private $statuses;

    public function __construct(array $shopStatusesAfterValidated, array $shopStatusesAfterPaid, array $shopStatusesAfterProcessed, array $shopStatusesAfterCancelled, TranslatorInterface $translator)
    {
        $this->statuses = [
            static::STATUS_VALIDATED => $translator->trans('order.status.validated'),
            static::STATUS_PAID => $translator->trans('order.status.paid'),
            static::STATUS_PROCESSED => $translator->trans('order.status.processed'),
            static::STATUS_CANCELLED => $translator->trans('order.status.cancelled'),
            static::STATUS_PAIEMENT_REFUSED => $translator->trans('order.status.payment.refused'),
        ];

        foreach ($shopStatusesAfterValidated as $key => $value) {
            if($key > 99) {
                throw new \InvalidArgumentException("Parameter shop_statuses_after_validated cannot contain key greater than 99");
            }
            $this->statuses[static::STATUS_VALIDATED + $key] = $value;
        }

        foreach ($shopStatusesAfterPaid as $key => $value) {
            if($key > 99) {
                throw new \InvalidArgumentException("Parameter shop_statuses_after_paid cannot contain key greater than 99");
            }
            $this->statuses[static::STATUS_PAID + $key] = $value;
        }

        foreach ($shopStatusesAfterProcessed as $key => $value) {
            if($key > 99) {
                throw new \InvalidArgumentException("Parameter shop_statuses_after_processed cannot contain key greater than 99");
            }
            $this->statuses[static::STATUS_PROCESSED + $key] = $value;
        }

        foreach ($shopStatusesAfterCancelled as $key => $value) {
            if($key > 99) {
                throw new \InvalidArgumentException("Parameter shop_statuses_after_cancelled cannot contain key greater than 99");
            }
            $this->statuses[static::STATUS_CANCELLED + $key] = $value;
        }
    }

    /**
     * Get statuses
     *
     * @return [string]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }


}
