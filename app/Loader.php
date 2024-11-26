<?php

declare(strict_types=1);

namespace BeycanPress\CryptoPay\MemberDash;

use BeycanPress\CryptoPay\Integrator\Hook;
use BeycanPress\CryptoPay\Integrator\Helpers;

class Loader
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('ms_init', [$this, 'registerGateways']);

        Helpers::registerIntegration('memberdash');
        Helpers::createTransactionPage(
            esc_html__('MemberDash transactions', 'cryptopay-gateway-for-memberdash'),
            'memberdash',
        );

        Hook::addFilter('init_memberdash', [$this, 'init']);
        Hook::addAction('payment_finished_memberdash', [$this, 'paymentFinished']);
        Hook::addFilter('payment_redirect_urls_memberdash', [$this, 'paymentRedirectUrls']);
    }

    /**
     * Check subscription
     * @param int $userId
     * @param int $membershipId
     * @return object
     */
    private function getSubscription(int $userId, int $membershipId): object
    {
        $subscription = \MS_Model_Relationship::get_subscription($userId, $membershipId);

        if (!$subscription) {
            Helpers::response('error', esc_html__('Subscription not found', 'cryptopay-gateway-for-memberdash'));
        }

        return $subscription;
    }

    /**
     * Init
     * @param object $data
     * @return object
     */
    public function init(object $data): object
    {
        $userId = (int) $data->getUserId();
        $membershipId = (int) $data->getParams()->get('membershipId');
        $this->getSubscription($userId, $membershipId); // Check subscription
        return $data;
    }

    /**
     * Payment finished
     * @param object $data
     * @return void
     */
    public function paymentFinished(object $data): void
    {
        $userId = (int) $data->getUserId();
        $gateway = $data->getParams()->get('gateway');
        $membershipId = (int) $data->getParams()->get('membershipId');
        $subscription = $this->getSubscription($userId, $membershipId);
        $invoice = \MS_Factory::load('MS_Model_Invoice', $data->getOrder()->getId());

        $invoice->add_notes($note = esc_html__('Payment successful!', 'cryptopay-gateway-for-memberdash'));

        $invoice->pay_it($gateway, $data->getHash());

        if (defined('MS_STRIPE_PLAN_RENEWAL_MAIL') && MS_STRIPE_PLAN_RENEWAL_MAIL) {
            \MS_Model_Event::save_event(\MS_Model_Event::TYPE_MS_RENEWED, $subscription);
        }

        do_action(
            'ms_gateway_transaction_log',
            $gateway,
            'handle',
            true,
            $subscription->id,
            $invoice->id,
            $invoice->total,
            $note,
            $data->getHash()
        );
    }

    /**
     * Payment redirect urls
     * @param object $data
     * @return array<string,string>
     */
    public function paymentRedirectUrls(object $data): array
    {
        $subscriptionId = $data->getParams()->get('subscriptionId');

        $successUrl = \MS_Model_Pages::get_page_url(\MS_Model_Pages::MS_PAGE_REG_COMPLETE);
        $failedUrl  = \MS_Model_Pages::get_page_url(\MS_Model_Pages::MS_PAGE_MEMBERSHIPS);

        $successUrl = esc_url_raw(add_query_arg([
            'ms_relationship_id' => $subscriptionId,
        ], $successUrl));

        return [
            'success' => $successUrl,
            'failed' => $failedUrl
        ];
    }

    /**
     * Register gateways
     * @param \MS_Controller_Api $api
     * @return void
     */
    public function registerGateways(\MS_Controller_Api $api): void
    {
        if (Helpers::exists()) {
            require_once MD_CRYPTOPAY_DIR . 'app/Gateways/MS_Gateway_CryptoPay.php';
            $api->register_payment_gateway(
                \MS_Gateway_CryptoPay::ID,
                \MS_Gateway_CryptoPay::class
            );
        }

        if (Helpers::liteExists()) {
            require_once MD_CRYPTOPAY_DIR . 'app/Gateways/MS_Gateway_CryptoPay_Lite.php';
            $api->register_payment_gateway(
                \MS_Gateway_CryptoPay_Lite::ID,
                \MS_Gateway_CryptoPay_Lite::class
            );
        }
    }
}
