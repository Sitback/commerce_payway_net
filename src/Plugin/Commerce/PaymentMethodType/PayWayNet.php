<?php

namespace Drupal\commerce_payway_net\Plugin\Commerce\PaymentMethodType;

use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeBase;
use Drupal\commerce\BundleFieldDefinition;

/**
 * Provides the PayPal payment method type.
 *
 * @CommercePaymentMethodType(
 *   id = "paywaynet",
 *   label = @Translation("PayWayNet"),
 *   create_label = @Translation("PayWayNet")
 * )
 */
class PayWayNet extends PaymentMethodTypeBase{

    /**
     * {@inheritdoc}
     */
    public function buildLabel(PaymentMethodInterface $payment_method) {
        $args = [
            '@paywaynet_token' => $payment_method->paywaynet_token->value,
        ];
        return $this->t('PayWayNet token (@paywaynet_token)', $args);
    }

    /**
     * {@inheritdoc}
     */
    public function buildFieldDefinitions() {
        $fields = parent::buildFieldDefinitions();

        $fields['paywaynet_token'] = BundleFieldDefinition::create('string')
            ->setLabel(t('PayWayNet Token'))
            ->setDescription(t('The PayWayNet token associated with the credit card.'))
            ->setRequired(TRUE);

        return $fields;
    }
}