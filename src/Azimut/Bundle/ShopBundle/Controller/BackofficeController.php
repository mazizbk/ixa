<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 14:37:14
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Form\Type\OrderType;
use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_SHOP')")
 */
class BackofficeController extends Controller
{
    public function orderFormAction()
    {
        $form = $this->createForm(OrderType::class)
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutShopBundle:Backoffice:order_form.angularjs.twig', [
            'form' => $form->createView(),
        ]);
    }
}
