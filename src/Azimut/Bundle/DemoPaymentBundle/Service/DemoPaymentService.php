<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 09:18:21
 */

namespace Azimut\Bundle\DemoPaymentBundle\Service;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Azimut\Bundle\DemoPaymentBundle\Form\Type\PaymentDemoType;

/**
 * This is a demo payment service made for demonstration only,
 * it does not reflect the real internals of a payment system
 */
class DemoPaymentService
{
    private $formFactory;

    private $templating;

    private $normalReturnUrl;

    private $cancelReturnUrl;

    private $automaticResponseUrl;

    private $paymentSucceeded = false;

    public function __construct(FormFactoryInterface $formFactory, EngineInterface $templating, $normalReturnUrl, $cancelReturnUrl, $automaticResponseUrl)
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->normalReturnUrl = $normalReturnUrl;
        $this->cancelReturnUrl = $cancelReturnUrl;
        $this->automaticResponseUrl = $automaticResponseUrl;
    }

    /**
     * Prepare payment request
     * @param  array  $config Payment config
     * @return string         Raw html response
     */
    public function requestPayment(array $config)
    {
        $form = $this->formFactory->create(PaymentDemoType::class, null, [
            'amount' => $config['amount'], // For demo purpose, this form embed the amount instead of storing it in DB
            'order_id' => $config['order_id'],
        ]);

        return $this->templating->render('AzimutDemoPaymentBundle::payment_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // For demo purpose, amount in passed as parameter instead of fetching it from DB
    public function processPayment($orderId, $cardNumber, $amount)
    {
        if ($cardNumber === '0001000100010001') {
            $this->paymentSucceeded = true;
        }
        else {
            $this->paymentSucceeded = false;
        }

        return $this->paymentSucceeded;
    }

    public function getReturnUrl($orderId)
    {
        if (true === $this->paymentSucceeded) {
            return $this->normalReturnUrl.'?order_id='.$orderId;
        }

        return $this->cancelReturnUrl.'?order_id='.$orderId;
    }

    public function getAutomaticResponseUrl($orderId)
    {
        return $this->automaticResponseUrl;
    }

    public function getStatus()
    {
        if (true === $this->paymentSucceeded) {
            return 'ACCEPTED';
        }

        return 'REFUSED';
    }
}
