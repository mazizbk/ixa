<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-09 10:06:46
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Azimut\Bundle\ShopBundle\Entity\OrderAddress;

/**
 * /!\ This is part of a demo delivery controller, DO NOT USE AS IS
 */
class DeliveryDemoRelayPackageController extends AbstractShopFrontController
{
    public function chooseRelayAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $site = $this->getSite($request);
        $this->get('azimut_shop.basket')->resetBasketStatus();
        $basket = $this->get('azimut_shop.basket')->getBasket();

        $form = $this->createFormBuilder()
            ->add('relay', ChoiceType::class, [
                'choices' => [
                    'Demo relay 1' => 1,
                    'Demo relay 2' => 2,
                ]
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (1 == $form->getData()['relay']) {
                $address = new OrderAddress();
                $address
                    ->setLastName('Demo relay 1')
                    ->setLine1('Lorem Ipsum')
                    ->setPostalCode('99999')
                    ->setCity('Quande')
                ;
            }
            elseif (2 == $form->getData()['relay']) {
                $address = new OrderAddress();
                $address
                    ->setLastName('Demo relay 2')
                    ->setLine1('Epsum factorial')
                    ->setPostalCode('88888')
                    ->setCity('Amet')
                ;
            }


            $basket->setDeliveryAddress($address);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('azimut_shop_summary');
        }

        return $this->render('SiteLayout/shop_delivery_demo_relay_package.html.twig', [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('choose.relay'),
            'pageDescription' => '',
            'site'            => $site,
            'form'            => $form->createView(),
        ]);
    }
}
