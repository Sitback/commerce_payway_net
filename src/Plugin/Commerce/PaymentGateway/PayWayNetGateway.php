<?php

namespace Drupal\commerce_payway_net\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the PayWay Frame payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "paywaynet_gateway",
 *   label = "PayWayNet Gateway",
 *   display_label = "PayWayNet",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_payway_net\PluginForm\PayWayNetForm",
 *   },
 *   credit_card_types = {
 *     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa",
 *   },
 * )
 */
class PayWayNetGateway extends OffsitePaymentGatewayBase implements ContainerFactoryPluginInterface {

  private $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PaymentTypeManager $payment_type_manager, PaymentMethodTypeManager $payment_method_type_manager, Client $client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $payment_type_manager, $payment_method_type_manager);

    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('http_client')
    );

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'commerce_payway_net_encryption_key' => '',
      'commerce_payway_net_biller_code' => '',
      'commerce_payway_net_username' => '',
      'commerce_payway_net_password' => '',
      'commerce_payway_net_caCertsFile' => '',
      'commerce_payway_net_merchant_id' => '',
      'commerce_payway_net_paypal_email' => '',
      'commerce_payway_net_payway_baseUrl' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * Gets the supported modes.
   *
   * @return string[]
   *   The mode labels keyed by machine name.
   */
  public function getSupportedModes() {
    return [
      'test' => 'test',
      'live' => 'live',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $settings = $this->configuration;

    // TODO: Delete non mandatory fields.
    $form['commerce_payway_net_encryption_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Encryption Key'),
      '#size' => 80,
      '#description' => t('eg. 123456789'),
      '#default_value' => $settings['commerce_payway_net_encryption_key'],
      '#required' => TRUE,
    );
    $form['commerce_payway_net_biller_code'] = array(
      '#type' => 'textfield',
      '#title' => t('Biller Code'),
      '#size' => 80,
      '#description' => t('eg. 123456'),
      '#default_value' => $settings['commerce_payway_net_biller_code'],
      '#required' => TRUE,
    );
    $form['commerce_payway_net_username'] = array(
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#size' => 80,
      '#description' => t('eg. K12345'),
      '#default_value' => $settings['commerce_payway_net_username'],
      '#required' => TRUE,
    );
    $form['commerce_payway_net_password'] = array(
      '#type' => 'textfield',
      '#title' => t('Password'),
      '#size' => 80,
      '#description' => t('eg. abcdefghijk'),
      '#default_value' => $settings['commerce_payway_net_password'],
      '#required' => TRUE,
    );
    /*$form['commerce_payway_net_caCertsFile'] = array(
    '#type' => 'textfield',
    '#title' => t('Path to cacerts.crt file'),
    '#size' => 80,
    '#description' => t('eg. /home/username/dev/certs/cacerts.crt'),
    '#default_value' => $settings['commerce_payway_net_caCertsFile'],
    // '#required' => TRUE,.
    );*/
    $form['commerce_payway_net_merchant_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Merchant Id'),
      '#size' => 80,
      '#description' => t('eg. TEST'),
      '#default_value' => $settings['commerce_payway_net_merchant_id'],
      '#required' => TRUE,
    );
    $form['commerce_payway_net_paypal_email'] = array(
      '#type' => 'textfield',
      '#title' => t('PayPal email address'),
      '#size' => 80,
      '#description' => t('eg. test@example.com'),
      '#default_value' => $settings['commerce_payway_net_paypal_email'],
      '#required' => TRUE,
    );
    $form['commerce_payway_net_payway_baseUrl'] = array(
      '#type' => 'textfield',
      '#title' => t('PayWay Base URL'),
      '#size' => 80,
      '#description' => t('eg. https://www.payway.com.au/'),
      '#default_value' => $settings['commerce_payway_net_payway_baseUrl'],
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      $this->configuration['commerce_payway_net_encryption_key'] = $values['commerce_payway_net_encryption_key'];
      $this->configuration['commerce_payway_net_biller_code'] = $values['commerce_payway_net_biller_code'];
      $this->configuration['commerce_payway_net_username'] = $values['commerce_payway_net_username'];
      $this->configuration['commerce_payway_net_password'] = $values['commerce_payway_net_password'];
      $this->configuration['commerce_payway_net_merchant_id'] = $values['commerce_payway_net_merchant_id'];
      $this->configuration['commerce_payway_net_paypal_email'] = $values['commerce_payway_net_paypal_email'];
      $this->configuration['commerce_payway_net_payway_baseUrl'] = $values['commerce_payway_net_payway_baseUrl'];
      $this->configuration['display_label'] = $values['display_label'];
      $this->configuration['mode'] = $values['mode'];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws HardDeclineException
   * @throws \InvalidArgumentException
   */
  public function onNotify(Request $request) {
    parent::onNotify($request);

    $configuration = $this->configuration;

    // Process params returned by the bank.
    if (isset($_REQUEST['EncryptedParameters'])) {

      $key = $configuration['commerce_payway_net_encryption_key'];
      $encryptedParameters = $_REQUEST['EncryptedParameters'];
      $signature = $_REQUEST['Signature'];

      $result = $this->payWayDecryptParameters($key, $encryptedParameters, $signature);

      /* @var OrderInterface */
      $order = Order::load($result['payment_reference']);

      switch ($result['payment_status']) {
        case 'approved':
          // Store the payment.
          $this->updatePayment($result, $order, 'capture_completed');
          // Update the order.
          $this->updateOrderToComplete($order);

          // This response will call onReturn().
          $params = [
            'commerce_order' => $order->id(),
            'step' => 'payment',
          ];
          $url = Url::fromRoute('commerce_payment.checkout.return', $params);

          break;

        case 'declined':
          // This response will call onCancel() from the parent class.
          $params = [
            'commerce_order' => $order->id(),
            'step' => 'payment',
          ];
          $url = Url::fromRoute('commerce_payment.checkout.cancel', $params);

          break;

        default:
          // This case is not supposed to happen.
          break;
      }
      return new RedirectResponse($url->toString());

    }
    return NULL;
  }

  /**
   * Update the Order to complete.
   *
   * @param Order $order
   *   The order which needs to be modified.
   */
  private function updateOrderToComplete(Order $order) {
    $order->set('state', 'completed');
    $order->set('placed', \Drupal::time()->getRequestTime());
    $order->set('completed', \Drupal::time()->getRequestTime());
    $order->set('checkout_step', 'complete');
    $order->set('cart', FALSE);
    $order->save();
  }

  /**
   * Update the payment.
   *
   * @param array $result
   *   The decrypted query string.
   * @param Order $order
   *   The order attached to the payment.
   * @param string $status
   *   The status of the payment.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updatePayment(array $result, Order $order, $status) {
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $amountPaid = new Price($result['payment_amount'], 'AUD');

    $payment = $payment_storage->create([
      'state' => $status,
      'amount' => $amountPaid,
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'remote_id' => $result['payment_number'],
      'remote_state' => $result['payment_status'],
      'authorized' => \Drupal::time()->getRequestTime(),
    ]);
    $payment->save();

  }

  /**
   * Unpad text.
   *
   * @param string $text
   *   Test to unpad.
   *
   * @return bool|string
   *   Unpaded text.
   */
  private function payWayPkcs5Unpad($text) {
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text)) {
      return FALSE;
    }
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
      return FALSE;
    }

    return substr($text, 0, -1 * $pad);
  }

  /**
   * Decrypt the query string returned by the bank.
   *
   * @param string $encryption_key
   *   The encryption key to use to decrypt the query string.
   * @param string $encrypted_text
   *   The query strong to decrypt.
   * @param string $signature
   *   The signature to decrypt. It's used to ensure the  integrity of the data.
   *
   * @return array
   *   The decoded query string.
   */
  private function payWayDecryptParameters($encryption_key, $encrypted_text, $signature) {
    $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    $text = openssl_decrypt(
        base64_decode($encrypted_text),
        'AES-128-CBC',
        base64_decode($encryption_key),
        OPENSSL_NO_PADDING,
        $iv);
    $text = $this->payWayPkcs5Unpad($text);

    $hash = openssl_decrypt(
        base64_decode($signature),
        'AES-128-CBC',
        base64_decode($encryption_key),
        OPENSSL_NO_PADDING,
        $iv);
    $hash = bin2hex($this->payWayPkcs5Unpad($hash));

    if ($hash !== md5($text)) {
      trigger_error('Invalid parameters signature');
    }

    $params = [];
    parse_str($text, $params);
    return $params;

  }

  /**
   * Getter for client.
   *
   * @return \GuzzleHttp\Client
   *   Guzzle client.
   */
  public function getClient() {
    return $this->client;
  }

}
