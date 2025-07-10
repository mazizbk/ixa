<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-03-13 14:53:59
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Azimut\Bundle\ShopBundle\Entity\DeliveryTracking;

class ApiDeliveryTrackingController extends FOSRestController
{
    /**
     * Get updated delivery tracking
     * @param int $id Id of the delivery tracking
     * @return array
     * @Rest\View(serializerGroups={"detail_order", "detail_address"})
     *
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Get updated delivery tracking"
     * )
     */
    public function getDeliverytrackingupdateAction($id)
    {
        $deliveryTracking = $this->getDeliveryTrackingEntity($id);
        $deliveryProviderId = $deliveryTracking->getOrder()->getDeliveryProviderId();
        $deliveryProvider = $this->get('azimut_shop.delivery_provider_chain')->getProvider($deliveryProviderId);

        $deliveryProvider->updateDeliveryTracking($deliveryTracking);

        return ['deliveryTracking' => $deliveryTracking];
    }

    /**
    * Private : get delivery tracking entity instance
    * @var int $id Id of the delivery tracking
    * @return DeliveryTracking
    */
    protected function getDeliveryTrackingEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $deliveryTracking = $em->getRepository(DeliveryTracking::class)->find($id);

        if (!$deliveryTracking) {
            throw $this->createNotFoundException('Unable to find delivery tracking '.$id);
        }

        return $deliveryTracking;
    }
}
