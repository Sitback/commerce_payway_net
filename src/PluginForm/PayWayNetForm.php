<?php

namespace Drupal\commerce_payway_net\PluginForm ;


use Drupal\commerce_payment\PluginForm\PaymentGatewayFormBase;
use Drupal\Core\Form\FormStateInterface;

class PayWayNetForm extends PaymentGatewayFormBase {

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
        // TODO: Implement buildConfigurationForm() method.

        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entity;
        /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
        $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
        $configuration = $payment_gateway_plugin->getConfiguration();
        $baseUrl = $configuration['commerce_payway_net_payWayBaseUrl'];

        // 1. Generate token.
        // www.payway.com.au/RequestToken
        $param1 = "biller_code=" . $configuration['commerce_payway_net_billerCode'];
        $param2 = "&username=" . $configuration['commerce_payway_net_username'];
        $param3 = "&password=" . $configuration['commerce_payway_net_password'];
        $params = $param1 . $param2 . $param3;

        $ch = curl_init($baseUrl . "RequestToken");
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            CURLOPT_POSTFIELDS => $params,
        ));

        // Make the request.
        // token=xxxxxx.
        $token = curl_exec($ch);

        // Check the response for errors.
        $errorNumber = curl_errno($ch);
        if ($errorNumber !== 0) {
            $errorMessage = curl_error($ch);
            \Drupal::logger('commerce_payway_net')->error($errorMessage);
            header("HTTP/1.1 403 " . $errorMessage);
            exit;
        }
        curl_close($ch);


        $a = 1;
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
        // TODO: Implement submitConfigurationForm() method.
        $a=1;
    }
}