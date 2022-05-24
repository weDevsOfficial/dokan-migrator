<?php

namespace Wedevs\DokanMigrator\Integrations\WcVendors;

use Wedevs\DokanMigrator\Abstracts\OrderMigration;

/**
 * Order migration class.
 *
 * @since 1.0.0
 */
class OrderMigrator extends OrderMigration {

    /**
     * Create sub order if needed
     *
     * @since 1.0.0
     *
     * @param int $seller_id
     * @param array $seller_products
     *
     * @return \WC_Order
     */
    public function create_sub_order_if_needed( $seller_id, $seller_products, $parent_order_id ) {
        return $this->create_sub_order( $seller_id, $seller_products );
    }

    /**
     * Delete sub orders of needed.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function reset_sub_orders_if_needed() {
        $this->reset_sub_orders();
    }

    /**
     * Gets order data from wcfm order table for dokan.
     *
     * @since 1.0.0
     *
     * @param int $parent_order_id
     * @param int $seller_id
     *
     * @return array
     */
    public function get_dokan_order_data( $parent_order_id, $seller_id ) {
        global $wpdb;

        $orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pv_commission commission WHERE commission.vendor_id = %d AND commission.order_id = %d", $seller_id, $parent_order_id ) );

        $wc_order = wc_get_order( $parent_order_id );

        $net_amount = 0;

        $order_total = $wc_order->get_total();
        $commissions = [];

        if ( $wc_order->get_total_refunded() ) {
            $order_total = $order_total - $wc_order->get_total_refunded();
        }

        foreach ( $orders as $key => $order ) {
            $net_amount += $order->total_due + $order->total_shipping + $order->tax;

            $res_commission = [
                'type'             => 'percent',
                'fixed'            => '',
                'percentage'       => '',
                'item_id'          => '',
                'admin_commission' => 0,
                'product_id'       => $order->product_id,
                'created'       => $order->time,
            ];

            $unit_commissin_rate_vendor = ( $order->total_due / $wc_order->get_subtotal() ) * 100;
            $unit_commissin_rate_admin  = 100 - $unit_commissin_rate_vendor;
            $new_admin_commissin        = ( $wc_order->get_subtotal() * $unit_commissin_rate_admin ) / 100;

            $res_commission['percentage']       = number_format( (float) $unit_commissin_rate_admin, 2, '.', '' );
            $res_commission['admin_commission'] = $new_admin_commissin;

            array_push( $commissions, $res_commission );
        }

        $admin_commission = 0;

        foreach ( $commissions as $com ) {
            $admin_commission += $com['admin_commission'];
        }

        $dokan_order_data = [
            'commission_data'         => $commissions,
            'order_total'             => $order_total,
            'net_sale'                => $net_amount,
            'admin_commission_amount' => $admin_commission,
        ];

        return $dokan_order_data;
    }

    /**
     * Process refund for a child order.
     *
     * @since 1.0.0
     *
     * @param \Wc_Order $child_order
     * @param integer $seller_id
     * @param boolean $from_suborder
     *
     * @return void
     */
    public function process_refund( $child_order, $seller_id, $from_suborder = true ) {
        global $wpdb;
        $order = wc_get_order( $child_order->get_id() );
        $new_total_amount = $order->get_total() - $order->get_total_refunded();

        // insert on dokan sync table

        $wpdb->update(
            $wpdb->prefix . 'dokan_orders',
            array(
                'order_total' => $new_total_amount,
            ),
            array(
                'order_id' => $child_order->get_id(),
            ),
            array(
                '%f',
            )
        );
    }
}
