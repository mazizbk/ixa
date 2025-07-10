<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 16:17:55
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Form\Type\OrderType;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_SHOP')")
 */
class ApiOrderController extends FOSRestController
{
    /**
     * Get action
     * @return array
     *
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Get available order statuses"
     * )
     */
    public function getOrdersAvailablestatusesAction()
    {
        return [
            'statuses' => $this->get('azimut_shop.order_status_provider')->getStatuses(),
            'defaultFilterStatus' => OrderStatusProvider::STATUS_PAID,
        ];
    }

    /**
      * Get all action
      * @return array
      *
      * @Rest\View(serializerGroups={"list_orders"})
      *
      * @ApiDoc(
      *  section="Shop",
      *  resource=true,
      *  description="Shop : Get all orders"
      * )
      */
    public function getOrdersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $orders = $em->getRepository(Order::class)->findAllHavingNumber();

        return [
            'orders' => $this->get('azimut_security.filter')->serializeGroup($orders, ['list_orders']),
        ];
    }

    /**
     * Get action
     * @param int  $id
     * @param null $locale
     * @return array
     * @internal param int $id Id of the order
     * @Rest\View(serializerGroups={"detail_order", "detail_address"})
     *
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Get an order by Id"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getOrderAction($id, $locale=null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $order = $this->getOrderEntity($id);
        if (!$this->isGranted('VIEW', $order)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return [
            'order' => $order,
        ];
    }

    /**
     * Put action
     * @var Request $request
     * @var int $id Id of the order
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_order", "detail_address"})
     *
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Update order",
     *  input="Azimut\Bundle\ShopBundle\Form\Type\OrderType",
     *  output="Azimut\Bundle\ShopBundle\Entity\Order"
     * )
     */
    public function putOrderAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');
        $order = $this->getOrderEntity($id);
        if (!$this->isGranted('EDIT', Order::class)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(OrderType::class, $order, [
            'method' => 'PUT',
            'csrf_protection' => false
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                'order' => $order,
            ];
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Delete action
     * @var int $id Id of the order
     * @return View
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Delete order"
     * )
     */
    public function deleteOrderAction($id)
    {
        $order = $this->getOrderEntity($id);
        if (!$this->isGranted('EDIT', Order::class)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($order);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * Private : get order entity instance
    * @var int $id Id of the order
    * @return Order
    */
    protected function getOrderEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $order = $em->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Unable to find order '.$id);
        }

        return $order;
    }
}
