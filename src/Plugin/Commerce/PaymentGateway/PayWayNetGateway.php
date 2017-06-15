<?php

namespace Drupal\commerce_payway_net\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface;
use Drupal\commerce_payway_frame\Plugin\Commerce\PaymentGateway\PayWayFrameInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the PayWay Frame payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "paywaynet_gateway",
 *   label = "PayWayNet Gateway",
 *   display_label = "PayWayNet Gateway",
 *   payment_method_types = {"paywaynet"},
 *   credit_card_types = {
 *     "amex", "dinersclub", "discover", "jcb", "maestro", "mastercard", "visa",
 *   },
 * )
 */
class PayWayNetGateway extends OffsitePaymentGatewayBase {

    /**
     * Gets default configuration for this plugin.
     *
     * @return array
     *   An associative array with the default configuration.
     */
    public function defaultConfiguration()
    {
        return [
            'commerce_payway_net_encryptionKey' => '',
            'commerce_payway_net_billerCode' => '',
            'commerce_payway_net_username' => '',
            'commerce_payway_net_password' => '',
            'commerce_payway_net_caCertsFile' => '',
            'commerce_payway_net_merchantId' => '',
            'commerce_payway_net_paypalEmail' => '',
            'commerce_payway_net_payWayBaseUrl' => '',
        ] + parent::defaultConfiguration();
    }

    /**
     * Gets the supported modes.
     *
     * @return string[]
     *   The mode labels keyed by machine name.
     */
    public function getSupportedModes()
    {
        return [
            'test' => 'test',
            'live' => 'live'
        ];
    }

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
        $form = parent::buildConfigurationForm($form, $form_state);

        // TODO: Delete non mandatory fields.
        $form['commerce_payway_net_encryptionKey'] = array(
            '#type' => 'textfield',
            '#title' => t('Encryption Key'),
            '#size' => 80,
            '#description' => t('eg. 123456789'),
            '#default_value' => $settings['commerce_payway_net_encryptionKey'],
            '#required' => TRUE,
        );
        $form['commerce_payway_net_billerCode'] = array(
            '#type' => 'textfield',
            '#title' => t('Biller Code'),
            '#size' => 80,
            '#description' => t('eg. 123456'),
            '#default_value' => $settings['commerce_payway_net_billerCode'],
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
        $form['commerce_payway_net_caCertsFile'] = array(
            '#type' => 'textfield',
            '#title' => t('Path to cacerts.crt file'),
            '#size' => 80,
            '#description' => t('eg. /home/username/dev/certs/cacerts.crt'),
            '#default_value' => $settings['commerce_payway_net_caCertsFile'],
            '#required' => TRUE,
        );
        $form['commerce_payway_net_merchantId'] = array(
            '#type' => 'textfield',
            '#title' => t('Merchant Id'),
            '#size' => 80,
            '#description' => t('eg. TEST'),
            '#default_value' => $settings['commerce_payway_net_merchantId'],
            '#required' => TRUE,
        );
        $form['commerce_payway_net_paypalEmail'] = array(
            '#type' => 'textfield',
            '#title' => t('PayPal email address'),
            '#size' => 80,
            '#description' => t('eg. test@example.com'),
            '#default_value' => $settings['commerce_payway_net_paypalEmail'],
            '#required' => TRUE,
        );
        $form['commerce_payway_net_payWayBaseUrl'] = array(
            '#type' => 'textfield',
            '#title' => t('PayWay Base URL'),
            '#size' => 80,
            '#description' => t('eg. https://www.payway.com.au/'),
            '#default_value' => $settings['commerce_payway_net_payWayBaseUrl'],
            '#required' => TRUE,
        );


        return $form;
    }

    /**
     * Form validation handler.
     *
     * @param array $form
     *   An associative array containing the structure of the plugin form as built
     *   by static::buildConfigurationForm().
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     *   The current state of the form. Calling code should pass on a subform
     *   state created through
     *   \Drupal\Core\Form\SubformState::createForSubform().
     */
    public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateConfigurationForm($form, $form_state);
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
    }

    /**
     * Gets the plugin_id of the plugin instance.
     *
     * @return string
     *   The plugin_id of the plugin instance.
     */
    public function getPluginId()
    {
        // TODO: Implement getPluginId() method.
    }

    /**
     * Gets the definition of the plugin implementation.
     *
     * @return array
     *   The plugin definition, as returned by the discovery object used by the
     *   plugin manager.
     */
    public function getPluginDefinition()
    {
        // TODO: Implement getPluginDefinition() method.
    }

    /**
     * Gets the form class for the given operation.
     *
     * @param string $operation
     *   The name of the operation.
     *
     * @return string|null
     *   The form class if defined, NULL otherwise.
     */
    public function getFormClass($operation)
    {
        // TODO: Implement getFormClass() method.
    }

    /**
     * Gets whether the plugin has a form class for the given operation.
     *
     * @param string $operation
     *   The name of the operation.
     *
     * @return bool
     *   TRUE if the plugin has a form class for the given operation.
     */
    public function hasFormClass($operation)
    {
        // TODO: Implement hasFormClass() method.
    }
}