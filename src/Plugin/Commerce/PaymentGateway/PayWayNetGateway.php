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
     * Gets this plugin's configuration.
     *
     * @return array
     *   An array of this plugin's configuration.
     */
    public function getConfiguration()
    {
        // TODO: Implement getConfiguration() method.
    }

    /**
     * Sets the configuration for this plugin instance.
     *
     * @param array $configuration
     *   An associative array containing the plugin's configuration.
     */
    public function setConfiguration(array $configuration)
    {
        // TODO: Implement setConfiguration() method.
    }

    /**
     * Gets default configuration for this plugin.
     *
     * @return array
     *   An associative array with the default configuration.
     */
    public function defaultConfiguration()
    {
        // TODO: Implement defaultConfiguration() method.
    }

    /**
     * Calculates dependencies for the configured plugin.
     *
     * Dependencies are saved in the plugin's configuration entity and are used to
     * determine configuration synchronization order. For example, if the plugin
     * integrates with specific user roles, this method should return an array of
     * dependencies listing the specified roles.
     *
     * @return array
     *   An array of dependencies grouped by type (config, content, module,
     *   theme). For example:
     * @code
     *   array(
     *     'config' => array('user.role.anonymous', 'user.role.authenticated'),
     *     'content' => array('node:article:f0a189e6-55fb-47fb-8005-5bef81c44d6d'),
     *     'module' => array('node', 'user'),
     *     'theme' => array('seven'),
     *   );
     * @endcode
     *
     * @see \Drupal\Core\Config\Entity\ConfigDependencyManager
     * @see \Drupal\Core\Entity\EntityInterface::getConfigDependencyName()
     */
    public function calculateDependencies()
    {
        // TODO: Implement calculateDependencies() method.
    }

    /**
     * Gets the base_plugin_id of the plugin instance.
     *
     * @return string
     *   The base_plugin_id of the plugin instance.
     */
    public function getBaseId()
    {
        // TODO: Implement getBaseId() method.
    }

    /**
     * Gets the derivative_id of the plugin instance.
     *
     * @return string|null
     *   The derivative_id of the plugin instance NULL otherwise.
     */
    public function getDerivativeId()
    {
        // TODO: Implement getDerivativeId() method.
    }

    /**
     * Gets the URL to the "notify" page.
     *
     * When supported, this page is called asynchronously to notify the site of
     * payment changes (new payment or capture/void/refund of an existing one).
     *
     * @return \Drupal\Core\Url
     *   The "notify" page url.
     */
    public function getNotifyUrl()
    {
        // TODO: Implement getNotifyUrl() method.
    }

    /**
     * Processes the "return" request.
     *
     * @param \Drupal\commerce_order\Entity\OrderInterface $order
     *   The order.
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request.
     *
     * @throws \Drupal\commerce_payment\Exception\PaymentGatewayException
     *   Thrown when the request is invalid or the payment failed.
     */
    public function onReturn(OrderInterface $order, Request $request)
    {
        // TODO: Implement onReturn() method.
    }

    /**
     * Processes the "cancel" request.
     *
     * Allows the payment gateway to clean up any data added to the $order, set
     * a message for the customer.
     *
     * @param \Drupal\commerce_order\Entity\OrderInterface $order
     *   The order.
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request.
     */
    public function onCancel(OrderInterface $order, Request $request)
    {
        // TODO: Implement onCancel() method.
    }

    /**
     * Processes the "notify" request.
     *
     * Note:
     * This method can't throw exceptions on failure because some payment
     * providers expect an error response to be returned in that case.
     * Therefore, the method can log the error itself and then choose which
     * response to return.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The request.
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     *   The response, or NULL to return an empty HTTP 200 response.
     */
    public function onNotify(Request $request)
    {
        // TODO: Implement onNotify() method.
    }

    /**
     * Gets the payment gateway label.
     *
     * The label is admin-facing and usually includes the name of the used API.
     * For example: "Braintree (Hosted Fields)".
     *
     * @return mixed
     *   The payment gateway label.
     */
    public function getLabel()
    {
        // TODO: Implement getLabel() method.
    }

    /**
     * Gets the payment gateway display label.
     *
     * The display label is customer-facing and more generic.
     * For example: "Braintree".
     *
     * @return string
     *   The payment gateway display label.
     */
    public function getDisplayLabel()
    {
        // TODO: Implement getDisplayLabel() method.
    }

    /**
     * Gets the mode in which the payment gateway is operating.
     *
     * @return string
     *   The machine name of the mode.
     */
    public function getMode()
    {
        // TODO: Implement getMode() method.
    }

    /**
     * Gets the supported modes.
     *
     * @return string[]
     *   The mode labels keyed by machine name.
     */
    public function getSupportedModes()
    {
        // TODO: Implement getSupportedModes() method.
    }

    /**
     * Gets the JS library ID.
     *
     * This is usually an external library defined in the module's
     * libraries.yml file. Included by the PaymentInformation pane
     * to get around core bug #1988968.
     * Example: 'commerce_braintree/braintree'.
     *
     * @return string|null
     *   The JS library ID, or NULL if not available.
     */
    public function getJsLibrary()
    {
        // TODO: Implement getJsLibrary() method.
    }

    /**
     * Gets the payment type used by the payment gateway.
     *
     * @return \Drupal\commerce_payment\Plugin\Commerce\PaymentType\PaymentTypeInterface
     *   The payment type.
     */
    public function getPaymentType()
    {
        // TODO: Implement getPaymentType() method.
    }

    /**
     * Gets the payment method types handled by the payment gateway.
     *
     * @return \Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeInterface[]
     *   The payment method types.
     */
    public function getPaymentMethodTypes()
    {
        // TODO: Implement getPaymentMethodTypes() method.
    }

    /**
     * Gets the default payment method type.
     *
     * @return \Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeInterface
     *   The default payment method type.
     */
    public function getDefaultPaymentMethodType()
    {
        // TODO: Implement getDefaultPaymentMethodType() method.
    }

    /**
     * Gets the credit card types handled by the gateway.
     *
     * @return \Drupal\commerce_payment\CreditCardType[]
     *   The credit card types.
     */
    public function getCreditCardTypes()
    {
        // TODO: Implement getCreditCardTypes() method.
    }

    /**
     * Builds the available operations for the given payment.
     *
     * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
     *   The payment.
     *
     * @return array
     *   The operations.
     *   Keyed by operation ID, each value is an array with the following keys:
     *   - title: The operation title.
     *   - page_title: The operation page title.
     *   - plugin_form: The plugin form ID.
     *   - access: Whether the operation is allowed for the given payment.
     */
    public function buildPaymentOperations(PaymentInterface $payment)
    {
        // TODO: Implement buildPaymentOperations() method.
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
        // TODO: Implement buildConfigurationForm() method.
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
        // TODO: Implement validateConfigurationForm() method.
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