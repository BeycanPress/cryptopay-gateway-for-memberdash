<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

// @phpcs:disable PSR1.Files.SideEffects
// @phpcs:disable PSR12.Files.FileHeader
// @phpcs:disable Generic.Files.InlineHTML
// @phpcs:disable Generic.Files.LineLength

/**
 * Plugin Name: CryptoPay Gateway for MemberDash
 * Version:     1.0.0
 * Plugin URI:  https://beycanpress.com/cryptopay/
 * Description: Adds Cryptocurrency payment gateway (CryptoPay) for MemberDash.
 * Author:      BeycanPress LLC
 * Author URI:  https://beycanpress.com
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: md-cryptopay
 * Tags: Cryptopay, Cryptocurrency, WooCommerce, WordPress, MetaMask, Trust, Binance, Wallet, Ethereum, Bitcoin, Binance smart chain, Payment, Plugin, Gateway, Moralis, Converter, API, coin market cap, CMC
 * Requires at least: 5.0
 * Tested up to: 6.5.0
 * Requires PHP: 8.1
*/

// Autoload
require_once __DIR__ . '/vendor/autoload.php';

use BeycanPress\CryptoPay\Integrator\Helpers;

define('MD_CRYPTOPAY_FILE', __FILE__);
define('MD_CRYPTOPAY_VERSION', '1.0.0');
define('MD_CRYPTOPAY_URL', plugin_dir_url(__FILE__));
define('MD_CRYPTOPAY_DIR', plugin_dir_path(__FILE__));

/**
 * @return void
 */
function memberdash_cryptopay_addModels(): void
{
    Helpers::registerModel(BeycanPress\CryptoPay\MemberDash\Models\TransactionsPro::class);
    Helpers::registerLiteModel(BeycanPress\CryptoPay\MemberDash\Models\TransactionsLite::class);
}

memberdash_cryptopay_addModels();

add_action('plugins_loaded', function (): void {

    memberdash_cryptopay_addModels();

    load_plugin_textdomain('md-cryptopay', false, basename(__DIR__) . '/languages');

    if (!defined('MEMBERDASH_VERSION')) {
        Helpers::requirePluginMessage('MemberDash', 'https://www.learndash.com/memberdash-plugin/', false);
        return;
    }

    if (Helpers::bothExists()) {
        new BeycanPress\CryptoPay\MemberDash\Loader();
    } else {
        Helpers::requireCryptoPayMessage('MemberDash');
    }
});
