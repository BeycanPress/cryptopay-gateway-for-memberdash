<?php

declare(strict_types=1);

// @phpcs:disable PSR1.Files.SideEffects
// @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

use BeycanPress\CryptoPay\Helpers;

require_once MD_CRYPTOPAY_DIR . 'views/MS_Gateway_CryptoPay_View_Button.php';
require_once MD_CRYPTOPAY_DIR . 'views/MS_Gateway_CryptoPay_View_Settings.php';

// @phpcs:ignore
class MS_Gateway_CryptoPay extends \MS_Gateway
{
    // @phpcs:ignore
    const ID = 'cryptopay';

    /**
     * @var string $instance
     */
    // @phpcs:ignore
    public static $instance;

    /**
     * @var string $id
     */
    // @phpcs:ignore
    public $id = '';

    /**
     * @var string $theme
     */
    // @phpcs:ignore
    public $theme;

    /**
     * @var string $name
     */
    // @phpcs:ignore
    protected $name = '';

    /**
     * @var string $group
     */
    // @phpcs:ignore
    protected $group = '';

    /**
     * @var string $description
     */
    // @phpcs:ignore
    protected $description = '';

    /**
     * @var bool $active
     */
    // @phpcs:ignore
    protected $active = false;

    /**
     * @var bool $manual_payment
     */
    // @phpcs:ignore
    protected $manual_payment = true;

    /**
     * @var bool $pro_rate
     */
    // @phpcs:ignore
    protected $pro_rate = false;

    /**
     * @var string $mode
     */
    // @phpcs:ignore
    protected $mode = 'live';

    /**
     * @return void
     */
    public function after_load(): void
    {
        parent::after_load();

        $this->id             = self::ID;
        $this->group          = 'cryptopay';
        $this->name           = __('CryptoPay', 'md-cryptopay');
        $this->description    = __('Cryptocurrency payments', 'md-cryptopay');

        $this->manual_payment = true;
        $this->pro_rate       = true;
        $this->mode           = Helpers::getTestnetStatus() ? \MS_Gateway::MODE_LIVE : \MS_Gateway::MODE_SANDBOX;
    }

    /**
     * @return bool
     */
    public function is_configured(): bool
    {
        $isConfigured = true;
        $required     = [];

        foreach ($required as $field) {
            $value = $this->$field;
            if (empty($value)) {
                $isConfigured = false;
                break;
            }
        }

        return apply_filters(
            'ms_gateway_' . self::ID . '_is_configured',
            $isConfigured
        );
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return void
     */
    // @phpcs:ignore
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    parent::__set($property, $value);
                    break;
            }
        }

        do_action(
            'ms_gateway_' . self::ID . '__set_after',
            $property,
            $value,
            $this
        );
    }
}
