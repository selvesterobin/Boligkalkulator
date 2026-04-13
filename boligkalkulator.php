<?php
/**
 * Plugin Name: Boligkalkulator
 * Plugin URI: https://oslobolig.no
 * Description: En profesjonell boligkalkulator for Oslo Bolig med backend-innstillinger og embed-funksjonalitet
 * Version: 1.1.5
 * Author: Robin Andersen
 * Author URI: https://robin.as
 * License: GPL v2 or later
 * Text Domain: boligkalkulator
 * Domain Path: /languages
 * GitHub Plugin URI: selvesterobin/Boligkalkulator
 * Primary Branch: master
 * Requires PHP: 7.4
 * Requires at least: 5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'BOLIGKALKULATOR_VERSION', '1.1.5' );
define( 'BOLIGKALKULATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BOLIGKALKULATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include core files
require_once BOLIGKALKULATOR_PLUGIN_DIR . 'includes/class-boligkalkulator.php';
require_once BOLIGKALKULATOR_PLUGIN_DIR . 'includes/admin-settings.php';
require_once BOLIGKALKULATOR_PLUGIN_DIR . 'includes/shortcode.php';

// Initialize plugin
add_action( 'plugins_loaded', function() {
    Boligkalkulator::get_instance();
} );

// Activation hook
register_activation_hook( __FILE__, function() {
    // Create default settings if they don't exist
    if ( ! get_option( 'boligkalkulator_settings' ) ) {
        $default_settings = array(
            'income_multiplier'          => 5,
            'savings_multiplier'         => 2,
            'default_ownership_share'    => 50,
            'min_ownership_share'        => 50,
            'max_ownership_share'        => 90,
            'children_deduction'         => 500000,
            'cars_deduction'             => 200000,
            'oslobolig_rent_percentage'  => 4.5,
            'estimated_price_per_kvm'    => 120000,
            'drift_cost_per_kvm'         => 50,
            'broadband_cost'             => 400,
            'loan_repayment_years'       => 30,
            'bank_interest_percentage'   => 5,
            'borettslag_capital_cost'    => 5,
            'minimum_savings_percentage' => 10,
            'search_button_url'          => '',
            'search_button_new_tab'      => 0,
            'annual_income_help'         => '',
            'savings_help'               => '',
            'existing_debt_help'         => '',
            'children_count_help'        => '',
            'cars_count_help'            => '',
            'ownership_form_help'        => '',
            'total_price_help'           => '',
            'your_share_help'            => '',
            'financing_help'             => '',
            'oslobolig_rent_help'        => '',
            'common_costs_help'          => '',
            'capital_cost_help'          => '',
            'bank_interest_help'         => '',
            'currency_symbol'            => 'kr',
            'decimal_separator'          => ',',
            'thousands_separator'        => ' ',
        );
        update_option( 'boligkalkulator_settings', $default_settings );
    }
} );

// Deactivation hook
register_deactivation_hook( __FILE__, function() {
    // Cleanup if needed
} );
