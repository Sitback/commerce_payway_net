<?php

namespace Drupal\commerce_payway_net\PluginForm;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Provide the PayWayNet Form class.
 *
 * Class PayWayNetForm.
 *
 * @package Drupal\commerce_payway_net\PluginForm
 */
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
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
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;

    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();

    // 1. Generate PayWay Token.
    $this->generatePayWayNetToken();

    // 2. Generate the link to post the user to PayWayNet.
    // https://www.payway.com.au/MakePayment?BillerCode=XXXXXX&token=TTTTT
    $token = explode('=', $this->token);
    $data = [
      'BillerCode' => $configuration['commerce_payway_net_biller_code'],
      'token' => $token[1],
    ];
    $redirectUrl = $configuration['commerce_payway_net_payway_baseUrl'] . 'MakePayment';

    // Redirect the user.
    $this->buildRedirectForm($form, $form_state, $redirectUrl, $data, 'POST');
  }

  /**
   * Generate PayWayNet Token.
   */
  protected function generatePayWayNetToken() {
    global $base_url;

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_order\Entity\Order $order */
    $order = $payment->get('order_id')->first()->get('entity')->getValue();

    /** @var \Drupal\commerce_payway_net\Plugin\Commerce\PaymentGateway\PayWayNetGateway $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
    $configuration = $payment_gateway_plugin->getConfiguration();

    $pwNetBaseUrl = $configuration['commerce_payway_net_payway_baseUrl'];

    // 1. Generate token.
    // www.payway.com.au/RequestToken.
    try {
      $client = $payment_gateway_plugin->getClient();
      $response = $client->request('POST', $pwNetBaseUrl . 'RequestToken', [
        'form_params' => [
          'biller_code' => $configuration['commerce_payway_net_biller_code'],
          'username' => $configuration['commerce_payway_net_username'],
          'password' => $configuration['commerce_payway_net_password'],
          'payment_reference' => $order->id(),
          'payment_amount' => $order->getTotalPrice()->getNumber(),
          'return_link_url' => $base_url . '/payment/notify/payway_net',
          'merchant_id' => $configuration['commerce_payway_net_merchand_id'],
          'paypal_email' => $configuration['commerce_payway_net_paypal_email'],
        ],
      ]);
    }
    catch (RequestException $e) {
      $errorMessage = $e->getMessage();
      \Drupal::logger('commerce_payway_net')->error($errorMessage);
      header("HTTP/1.1 403 " . $errorMessage);
    }

    if ($response->getStatusCode() !== 200) {
      $errorMessage = $response->getReasonPhrase();
      \Drupal::logger('commerce_payway_net')->error($errorMessage);
      header("HTTP/1.1 403 " . $errorMessage);
    }
    else {
      $length = $response->getBody()->getSize();
      $this->token = $response->getBody()->read($length);
    }
  }

}
