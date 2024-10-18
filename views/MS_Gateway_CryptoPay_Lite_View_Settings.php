<?php

declare(strict_types=1);

// @phpcs:disable Generic.Files.LineLength
// @phpcs:disable Generic.Files.InlineHTML
// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

// @phpcs:ignore
class MS_Gateway_CryptoPay_Lite_View_Settings extends \MS_View
{
    /**
     * @var array<string,mixed>
     */
    // @phpcs:ignore
    protected $data;

    /**
     * Render the gateway settings form.
     *
     * @return string
     */
    public function to_html(): string
    {
        $fields  = $this->prepare_fields();
        $gateway = $this->data['model'];

        ob_start();
        ?>
        <form class="ms-gateway-settings-form ms-form">
            <?php
            $description = sprintf('You can manage the settings in %s menu.', $gateway->name);
            \MS_Helper_Html::settings_box_header('', $description);
            foreach ($fields as $field) {
                \MS_Helper_Html::html_element($field);
            }
            \MS_Helper_Html::settings_box_footer();
            ?>
        </form>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    /**
     * Prepare the gateway settings fields.
     * @return array<string,mixed>
     */
    protected function prepare_fields(): array
    {
        $gateway = $this->data['model'];
        $action  = \MS_Controller_Gateway::AJAX_ACTION_UPDATE_GATEWAY;
        $nonce   = wp_create_nonce($action);

        $fields = array(
            'theme' => array(
                'id'            => 'theme',
                'type'          => \MS_Helper_Html::INPUT_TYPE_SELECT,
                'title'         => __('Theme', 'cryptopay-gateway-for-memberdash'),
                'value'         => $gateway->theme ?? 'light',
                'field_options' => array(
                    'light' => __('Light', 'cryptopay-gateway-for-memberdash'),
                    'dark'  => __('Dark', 'cryptopay-gateway-for-memberdash'),
                ),
                'class'         => 'ms-text-large',
                'ajax_data'     => array(1),
            ),
        );

        // Process the fields and add missing default attributes.
        foreach ($fields as $key => $field) {
            if (!empty($field['ajax_data'])) {
                $fields[$key]['ajax_data']['field']      = $fields[$key]['id'];
                $fields[$key]['ajax_data']['_wpnonce']   = $nonce;
                $fields[$key]['ajax_data']['action']     = $action;
                $fields[$key]['ajax_data']['gateway_id'] = $gateway->id;
            }
        }

        return $fields;
    }
}
