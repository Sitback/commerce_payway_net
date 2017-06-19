<?php

namespace Drupal\commerce_payway_net\PluginForm ;


use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\Entity\Payment;
use Drupal\commerce_payment\PluginForm\PaymentGatewayFormBase;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormStateInterface;

class PayWayNetForm extends PaymentOffsiteForm {

    private $token;

    /**
     * Form constructor.
     *
     * Plugin forms are embedded in other forms. In order to know where the plugin
     * form is located in the parent form, #parents and #array_parents must be
     * known, but these are not available during the initial build phase. In order
     * to have these properties available when building the plugin form's
     * elements, let this method return a form element that has a #process
     * callback and build the rest of the form in the callback. By the time the
     * callback is executed, the element's #parents and #array_parents properties
     * will have been set by the form API. For more documentation on #parents and
     * #array_parents, see \Drupal\Core\Render\Element\FormElement.
     *
     * @param array $form
     *   An associative array containing the initial structure of the plugin form.
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form. Calling code should pass on a subform
     *   state created through
     *   \Drupal\Core\Form\SubformState::createForSubform().
     *
     * @return array
     *   The form structure.
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {
        $form['commerce_message'] = [
            '#markup' => '<div class="checkout-help">' . t('Please wait while you are redirected to the payment server. If nothing happens within 10 seconds, please click on the button below.') . '</div>',
            '#weight' => -10,
        ];

        return $form;

    }

    /**
     * Form submission handler.
     *
     * @param array $form
     *   An associative array containing the structure of the plugin form as built
     *   by static::buildConfigurationForm().
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form. Calling code should pass on a subform
     *   state created through
     *   \Drupal\Core\Form\SubformState::createForSubform().
     */
    public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entity;

        /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
        $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
        $configuration = $payment_gateway_plugin->getConfiguration();

        // 1. Generate PayWay Token.
        $this->generatePayWayNetToken();

        // 2. Generate the link to post the user to PayWayNet.
        // https://www.payway.com.au/MakePayment?BillerCode=XXXXXX&token=TTTTT
        $token = explode('=',$this->token);
        $data = [
            'BillerCode' => $configuration['commerce_payway_net_billerCode'],
            'token' => $token[1],
        ];
        $redirectUrl = $configuration['commerce_payway_net_payWayBaseUrl'] . 'MakePayment';
        $redirectMethod = 'POST';

        // Redirect the user.
        $this->buildRedirectForm($form, $form_state, $redirectUrl, $data, $redirectMethod);
    }

    /**
     * Generate PayWayNet Token.
     */
    protected function generatePayWayNetToken() {
        global $base_url;

        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entity;
        /** @var Order $order */
        $order = $payment->get('order_id')->first()->get('entity')->getValue();

        $orderId = $order->id();
        $amount = $order->getTotalPrice()->getNumber();

        /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
        $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
        $configuration = $payment_gateway_plugin->getConfiguration();

        $pwNetBaseUrl = $configuration['commerce_payway_net_payWayBaseUrl'];

        // 1. Generate token.
        // www.payway.com.au/RequestToken
        $params = [
            'biller_code' => $configuration['commerce_payway_net_billerCode'],
            'username' => $configuration['commerce_payway_net_username'],
            'password' => $configuration['commerce_payway_net_password'],
            'payment_reference' => $order->id(),
            'payment_amount' => $order->getTotalPrice()->getNumber(),
            'return_link_url' => $base_url. '/checkout/' . $orderId . '/payment/return',
            'merchant_id' => $configuration['commerce_payway_net_merchandId'],
            'paypal_email' => $configuration['commerce_payway_net_paypalEmail'],
        ] ;

        $ch = curl_init($pwNetBaseUrl . 'RequestToken');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => http_build_query($params),
        ));

        // Make the request.
        // $this->token = token=xxxxxx.
        $this->token = curl_exec($ch);

        // Check the response for errors.
        $errorNumber = curl_errno($ch);
        if ($errorNumber !== 0) {
            $errorMessage = curl_error($ch);
            \Drupal::logger('commerce_payway_net')->error($errorMessage);
            header("HTTP/1.1 403 " . $errorMessage);
            exit;
        }
        curl_close($ch);
    }
}