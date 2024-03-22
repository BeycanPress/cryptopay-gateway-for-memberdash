<?php

declare(strict_types=1);

// @phpcs:disable Generic.Files.LineLength
// @phpcs:disable Generic.Files.InlineHTML
// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

use BeycanPress\CryptoPayLite\Payment;
use BeycanPress\CryptoPayLite\Helpers;
use BeycanPress\CryptoPayLite\PluginHero\Hook;
use BeycanPress\CryptoPayLite\Types\Order\OrderType;
use BeycanPress\CryptoPayLite\Types\Transaction\ParamsType;

// @phpcs:ignore
class MS_Gateway_CryptoPay_Lite_View_Button extends \MS_View
{
    /**
     * @var array<string,mixed>
     */
    // @phpcs:ignore
    protected $data;

    /**
     * @param array<string,mixed> $data
     * @return void
     */
    public function enqueueScripts(array $deps): void
    {
        wp_enqueue_script(
            'md-cp-script',
            MD_CRYPTOPAY_URL . 'assets/js/main.js',
            array_merge(array('jquery'), $deps),
            MD_CRYPTOPAY_VERSION,
            true
        );
    }

    /**
     * @return string
     */
    public function to_html(): string
    {
        $gateway      = $this->data['gateway'];
        $fields       = $this->prepare_fields();
        $subscription = $this->data['ms_relationship'];
        $invoice      = $subscription->get_next_billable_invoice();

        ob_start();
        
        foreach ($fields as $field) {
            \MS_Helper_Html::html_element($field);
        }
        
        $paymentButton = apply_filters(
            'ms_gateway_form',
            ob_get_clean(),
            $gateway,
            $invoice,
            $this
        );

        // Cp modal css
        Helpers::addStyle('main.min.css');

        Hook::addFilter('theme', function () use ($gateway) {
            return $gateway->theme ?? 'light';
        });
        
        Hook::addFilter('lang', function(array $lang) {
            return array_merge($lang, [
                'orderId' => __('Invoice ID:', 'md-cryptopay'),
            ]);
        });

        $params = ParamsType::fromArray([
            'gateway' => $gateway->id,
            'subscriptionId' => $subscription->id,
            'membershipId' => $invoice->membership_id,
        ]);

        $order = OrderType::fromArray([
            'id' => $invoice->id,
            'amount' => $invoice->total,
            'currency' => $invoice->currency
        ]);
        
        $cp = new Payment('memberdash');
        $cp->setOrder($order);
        $cp->setParams($params);

        // Get cp html
        $paymentButton .= $cp->modal();

        $this->enqueueScripts([Helpers::getProp('mainJsKey')]);

        $rowClass = 'gateway_' . $gateway->id;
		if ( ! $gateway->is_live_mode() ) {
			$rowClass .= ' sandbox-mode';
		}

        ob_start();

        ?>
            <tr class="<?php echo esc_attr($rowClass); ?>">
                <td class="ms-buy-now-column" colspan="2">
                    <?php Helpers::ksesEcho($paymentButton); ?>
                </td>
            </tr>
        <?php

        $html = apply_filters(
            'ms_gateway_button-' . $gateway->id,
            ob_get_clean(),
            $this
        );

        $html = apply_filters(
            'ms_gateway_button',
            $html,
            $gateway->id,
            $this
        );

        return $html;
    }

    /**
     * @return array<string,mixed>
     */
    private function prepare_fields(): array
    {
        $gateway      = $this->data['gateway'];
        $subscription = $this->data['ms_relationship'];

        $fields = array(
            '_wpnonce' => array(
                'id'    => '_wpnonce',
                'type'  => MS_Helper_Html::INPUT_TYPE_HIDDEN,
                'value' => wp_create_nonce("{$gateway->id}_{$subscription->id}"),
            ),
            'gateway' => array(
                'id'    => 'gateway',
                'type'  => MS_Helper_Html::INPUT_TYPE_HIDDEN,
                'value' => $gateway->id,
            ),
            'ms_relationship_id' => array(
                'id'    => 'ms_relationship_id',
                'type'  => MS_Helper_Html::INPUT_TYPE_HIDDEN,
                'value' => $subscription->id,
            ),
            'step' => array(
                'id'    => 'step',
                'type'  => MS_Helper_Html::INPUT_TYPE_HIDDEN,
                'value' => $this->data['step'],
            ),
            'button' => array(
                'id'    => 'mb-cryptopay-lite-start',
                'type'  => MS_Helper_Html::INPUT_TYPE_BUTTON,
                'value' => esc_html__('CryptoPay Lite', 'md-cryptopay'),
            )
        );

        return $fields;
    }
}
