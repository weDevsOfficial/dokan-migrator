<?php

namespace Wedevs\DokanMigrator\Integrations\YithMultiVendor;

use Wedevs\DokanMigrator\Abstracts\WithdrawMigration;

/**
 * Formats vendor data for migration to Dokan.
 *
 * @since 1.0.0
 */
class WithdrawMigrator extends WithdrawMigration {

    /**
     * Current withdraw data.
     *
     * @var object
     */
    private $withdraw = '';

    /**
     * Current withdraw metadata.
     *
     * @var array
     */
    private $meta_data = '';

    /**
     * Current withdraw id.
     *
     * @var int
     */
    private $withdraw_id = '';

    /**
     * Sets single withdraw item data.
     *
     * @since 1.0.0
     */
    public function set_withdraw_data( $withdraw_data ) {
        $this->withdraw = $withdraw_data;
        $this->withdraw_id = $withdraw_data->ID;

        $this->meta_data = $this->get_withdraw_meta_data();
    }

    /**
     * Returns vendor id.
     *
     * @since 1.0.0
     *
     * @return int
     */
    public function get_vendor_id() {
        return ! empty( $this->withdraw->user_id ) ? $this->withdraw->user_id : '';
    }

    /**
     * Returns withdraw amount.
     *
     * @since 1.0.0
     *
     * @return int|float
     */
    public function get_withdraw_amount() {
        return $this->withdraw->amount - abs( $this->withdraw->amount_refunded );
    }

    /**
     * Returns withdraw created date.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_withdraw_created_date() {
        return ! empty( $this->withdraw->last_edit_gmt ) ? $this->withdraw->last_edit_gmt : '';
    }

    /**
     * Returns withdraw status.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_withdraw_status() {
        return 1;
    }

    /**
     * Returns withdraw payment method.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_withdraw_payment_method() {
        return '';
    }

    /**
     * Returns withdraw note
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_withdraw_note() {
        return 'Made by dokan migrator.';
    }

    /**
     * Returns withdraw details.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_withdraw_details() {
        $order_ids          = ! empty( $this->withdraw->order_ids ) ? $this->withdraw->order_ids : '';
        $commission_ids     = ! empty( $this->withdraw->commission_ids ) ? $this->withdraw->commission_ids : '';
        $withdraw_charges   = ! empty( $this->withdraw->withdraw_charges ) ? $this->withdraw->withdraw_charges : '';
        $withdraw_mode      = ! empty( $this->withdraw->withdraw_mode ) ? $this->withdraw->withdraw_mode : '';
        $is_auto_withdrawal = ! empty( $this->withdraw->is_auto_withdrawal ) ? $this->withdraw->is_auto_withdrawal : '';
        $withdraw_paid_date = ! empty( $this->withdraw->time ) ? $this->withdraw->time : '';

        $vendor_id  = ! empty( $this->withdraw->user_id ) ? $this->withdraw->user_id : '';
        $product_id = ! empty( $this->withdraw->line_item_id ) ? $this->withdraw->line_item_id : '';
        $qty        = ! empty( $this->withdraw->qty ) ? $this->withdraw->qty : '';
        $rate       = ! empty( $this->withdraw->rate ) ? ( $this->withdraw->rate * 100 ) : '';

        $dokan_details                       = $this->meta_data;
        $dokan_details['email']              = get_userdata( $this->get_vendor_id() )->user_email;
        $dokan_details['order_ids']          = $order_ids;
        $dokan_details['commission_ids']     = $commission_ids;
        $dokan_details['withdraw_charges']   = $withdraw_charges;
        $dokan_details['withdraw_mode']      = $withdraw_mode;
        $dokan_details['is_auto_withdrawal'] = $is_auto_withdrawal;
        $dokan_details['withdraw_paid_date'] = $withdraw_paid_date;
        $dokan_details['vendor_id']          = $vendor_id;
        $dokan_details['product_id']         = $product_id;
        $dokan_details['qty']                = $qty;
        $dokan_details['rate']               = $rate;

        return maybe_serialize( $dokan_details );
    }

    /**
     * Returns withdraw ip.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_withdraw_ip() {
        return '';
    }

    /**
     * Gets the withdraw meta data.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_withdraw_meta_data() {
        return [];
    }
}
