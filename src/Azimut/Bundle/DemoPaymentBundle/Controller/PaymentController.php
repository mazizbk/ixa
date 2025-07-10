<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 09:09:14
 */

namespace Azimut\Bundle\DemoPaymentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Azimut\Bundle\DemoPaymentBundle\Form\Type\PaymentDemoType;

class PaymentController extends Controller
{
    public function callPaymentAction(Request $request)
    {
        // Here we should check if this transaction hasn't already been processed,
        // because it's a demo, we don't

        $paymentService = $this->get('azimut_demo_payment.demo_payment_service');
        $orderId = null;
        $amount = null;
        $isPaymentSuccessfull = false;

        $form = $this->createForm(PaymentDemoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $orderId = $form->getData()['order_id'];
            $cardNumber = $form->getData()['cardNumber'];
            $amount = $form->getData()['amount']; // For demo purpose, amount in fetch from form instead of from DB

            // Process payment
            $isPaymentSuccessfull = $paymentService->processPayment($orderId, $cardNumber, $amount);
        }

        // Because this is a demo, we didn't store anything in DB
        // so we simulate a transaction object
        $transaction = [
            'orderId'=> $orderId,
            'status' => $paymentService->getStatus(),
            'isSuccessfull' => $isPaymentSuccessfull,
            'amount' => $amount,
        ];

        return $this->render('AzimutDemoPaymentBundle:Payment:payment.html.twig', [
            'returnToShopUrl' => $paymentService->getReturnUrl($orderId),
            'transaction'     => $transaction,
            'callbackUrl'     => $paymentService->getAutomaticResponseUrl($orderId), // For demo purpose, the callback in done in js, so the view need the URL
        ]);
    }
}
