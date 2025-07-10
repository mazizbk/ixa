<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-03 10:53:37
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Azimut\Bundle\ShopBundle\Entity\BaseProductItem;
use Azimut\Bundle\ShopBundle\Entity\OrderItem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Azimut\Bundle\ShopBundle\Form\Type\OrderItemQuantityType;
use Azimut\Bundle\ShopBundle\Event as ShopEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ApiBasketController extends FOSRestController
{
    /**
     * Add product to basket
     * @var Request $request
     * @var string $class Class of the product item
     * @var int $id Id of the product item
     * @return array
     * @RequestParam(
     *  name="class",
     *  description="product item class"
     * )
     * @RequestParam(
     *  name="id",
     *  requirements="\d+",
     *  description="id item class"
     * )
     * @ApiDoc(
     *  section="Shop",
     *  description="Basket : Add product to basket",
     * )
     */
    public function postAddbasketitemAction(Request $request)
    {
        $class = $request->get('class');
        $id = $request->get('id');
        if (!class_exists($class)) {
            throw new BadRequestHttpException(sprintf('Provided class "%s" do not exists', $class));
        }
        if (!is_subclass_of($class, BaseProductItem::class)) {
            throw new BadRequestHttpException(sprintf('Provided class "%s" must extends "%s"', $class, BaseProductItem::class));
        }

        $em = $this->getDoctrine()->getManager();
        $basketService = $this->get('azimut_shop.basket');
        $basket = $basketService->getBasket();
        $productItem = $em->getRepository($class)->find($id);
        $hasAddedOrDeletedOrderItems = false;

        // Event : before item added
        $event = new ShopEvent\BasketBeforeItemAddedEvent($basket, $productItem);
        $this->get('event_dispatcher')->dispatch(ShopEvent\BasketBeforeItemAddedEvent::NAME, $event);
        if ($event->isPropagationStopped()) {
            return [
                'status'        => 'blocked',
                'statusMessage' => $event->getPropagationStoppedMessage(),
            ];
        }
        $hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems || $event->hasAddedOrDeletedOrderItems();

        $orderItem = $em->getRepository(OrderItem::class)->findOneBy([
            'order'            => $basket,
            'productItemClass' => $class,
            'productItemId'    => $id,
        ]);
        if (null != $orderItem) {
            $orderItem->setQuantity($orderItem->getQuantity() + 1);
        }
        else {
            $orderItem = OrderItem::createFromProductItem($productItem);
            $basket->addOrderItem($orderItem);
        }

        // Event : after item added
        $event = new ShopEvent\BasketAfterItemAddedEvent($basket, $orderItem);
        $this->get('event_dispatcher')->dispatch(ShopEvent\BasketAfterItemAddedEvent::NAME, $event);
        if ($event->isPropagationStopped()) {
            return [
                'status'        => 'blocked',
                'statusMessage' => $event->getPropagationStoppedMessage(),
            ];
        }
        $hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems || $event->hasAddedOrDeletedOrderItems();

        $basketService->storeBasket();

        return [
            'orderItemId'                 => $orderItem->getId(),
            'basketItemsCount'            => $basket->count(),
            'hasAddedOrDeletedOrderItems' => $hasAddedOrDeletedOrderItems,
        ];
    }

    /**
     * Update basket item quantity
     * @var Request $request
     * @var integer $id Id of the order item
     * @return array
     *
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Update basket item quantity",
     *  input="Azimut\Bundle\ShopBundle\Form\Type\OrderItemQuantityType",
     *  output="Azimut\Bundle\ShopBundle\Entity\OrderItem"
     * )
     */
    public function patchUpdatebasketitemquantityAction(Request $request, $id)
    {
        if (!$request->request->get('quantity')) {
            throw new \InvalidArgumentException("OrderItem data not found in posted datas.");
        }

        $orderItem = $this->getOrderItemEntity($id);
        $hasAddedOrDeletedOrderItems = false;

        // Event : before item quantity changed
        $event = new ShopEvent\BasketBeforeItemQuantityChangedEvent($orderItem->getOrder(), $orderItem);
        $this->get('event_dispatcher')->dispatch(ShopEvent\BasketBeforeItemQuantityChangedEvent::NAME, $event);
        if ($event->isPropagationStopped()) {
            return [
                'status'        => 'blocked',
                'statusMessage' => $event->getPropagationStoppedMessage(),
            ];
        }
        $hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems || $event->hasAddedOrDeletedOrderItems();

        $form = $this->createForm(OrderItemQuantityType::class, $orderItem, [
            'method'          => 'PATCH',
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Event : after item quantity changed
            $event = new ShopEvent\BasketAfterItemQuantityChangedEvent($orderItem->getOrder(), $orderItem);
            $this->get('event_dispatcher')->dispatch(ShopEvent\BasketAfterItemQuantityChangedEvent::NAME, $event);
            if ($event->isPropagationStopped()) {
                return [
                    'status'        => 'blocked',
                    'statusMessage' => $event->getPropagationStoppedMessage(),
                ];
            }
            $hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems || $event->hasAddedOrDeletedOrderItems();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return [
                'quantity'                    => $orderItem->getQuantity(),
                'price'                       => $orderItem->getPrice(),
                'order_total_amount'          => $orderItem->getOrder()->getTotalAmount(),
                'hasAddedOrDeletedOrderItems' => $hasAddedOrDeletedOrderItems,
            ];
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Delete basket item
     * @var int $id Id of the order item
     * @return View
     * @ApiDoc(
     *  section="Shop",
     *  description="Shop : Delete basker item"
     * )
     */
    public function deleteBasketitemAction($id)
    {
        $orderItem = $this->getOrderItemEntity($id);
        $basket = $orderItem->getOrder();
        $hasAddedOrDeletedOrderItems = false;

        if (false === $orderItem->isDeletable()) {
            throw new MethodNotAllowedHttpException([], 'This item cannot be deleted');
        }

        // Event : before item deleted
        $event = new ShopEvent\BasketBeforeItemDeletedEvent($basket, $orderItem);
        $this->get('event_dispatcher')->dispatch(ShopEvent\BasketBeforeItemDeletedEvent::NAME, $event);
        if ($event->isPropagationStopped()) {
            return [
                'status'        => 'blocked',
                'statusMessage' => $event->getPropagationStoppedMessage(),
            ];
        }
        $hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems || $event->hasAddedOrDeletedOrderItems();

        $em = $this->getDoctrine()->getManager();
        $em->remove($orderItem);

        // Event : after item deleted
        $event = new ShopEvent\BasketAfterItemDeletedEvent($basket, $orderItem);
        $this->get('event_dispatcher')->dispatch(ShopEvent\BasketAfterItemDeletedEvent::NAME, $event);
        if ($event->isPropagationStopped()) {
            return [
                'status'        => 'blocked',
                'statusMessage' => $event->getPropagationStoppedMessage(),
            ];
        }
        $hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems || $event->hasAddedOrDeletedOrderItems();

        $em->flush();

        return [
            'order_total_amount'          => $basket->getTotalAmount(),
            'hasAddedOrDeletedOrderItems' => $hasAddedOrDeletedOrderItems,
        ];
    }

    /**
     * Private : get order item entity instance
     * @var integer $id Id of the entity
     * @return OrderItem
     */
    protected function getOrderItemEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $orderItem = $em->getRepository('AzimutShopBundle:OrderItem')->find($id);

        if (!$orderItem) {
            throw $this->createNotFoundException('Unable to find order item '.$id);
        }

        return $orderItem;
    }
}
