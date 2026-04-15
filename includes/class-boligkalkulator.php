<?php
/**
 * Main Boligkalkulator Class
 */

class Boligkalkulator {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Enqueue frontend assets – only on pages that use the shortcode
     */
    public function enqueue_frontend_assets() {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'boligkalkulator' ) ) {
            return;
        }

        wp_enqueue_style(
            'boligkalkulator-chivo-font',
            'https://fonts.googleapis.com/css2?family=Chivo:wght@400;700&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'boligkalkulator-frontend',
            BOLIGKALKULATOR_PLUGIN_URL . 'assets/css/frontend.css',
            array( 'boligkalkulator-chivo-font' ),
            BOLIGKALKULATOR_VERSION
        );

        wp_enqueue_script(
            'boligkalkulator-frontend',
            BOLIGKALKULATOR_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            BOLIGKALKULATOR_VERSION,
            true
        );

        // Pass settings to frontend
        $settings = get_option( 'boligkalkulator_settings', array() );
        wp_localize_script(
            'boligkalkulator-frontend',
            'boligkalkulatorSettings',
            array(
                'income_multiplier'          => isset( $settings['income_multiplier'] ) ? (float) $settings['income_multiplier'] : 5,
                'savings_multiplier'         => isset( $settings['savings_multiplier'] ) ? (float) $settings['savings_multiplier'] : 2,
                'default_ownership_share'    => isset( $settings['default_ownership_share'] ) ? (int) $settings['default_ownership_share'] : 50,
                'min_ownership_share'        => isset( $settings['min_ownership_share'] ) ? (int) $settings['min_ownership_share'] : 50,
                'max_ownership_share'        => isset( $settings['max_ownership_share'] ) ? (int) $settings['max_ownership_share'] : 90,
                'children_deduction'         => isset( $settings['children_deduction'] ) ? (int) $settings['children_deduction'] : 500000,
                'cars_deduction'             => isset( $settings['cars_deduction'] ) ? (int) $settings['cars_deduction'] : 200000,
                'oslobolig_rent_percentage'  => isset( $settings['oslobolig_rent_percentage'] ) ? (float) $settings['oslobolig_rent_percentage'] : 4.5,
                'estimated_price_per_kvm'    => isset( $settings['estimated_price_per_kvm'] ) ? (int) $settings['estimated_price_per_kvm'] : 120000,
                'drift_cost_per_kvm'         => isset( $settings['drift_cost_per_kvm'] ) ? (int) $settings['drift_cost_per_kvm'] : 50,
                'broadband_cost'             => isset( $settings['broadband_cost'] ) ? (int) $settings['broadband_cost'] : 400,
                'loan_repayment_years'       => isset( $settings['loan_repayment_years'] ) ? (int) $settings['loan_repayment_years'] : 30,
                'bank_interest_percentage'   => isset( $settings['bank_interest_percentage'] ) ? (float) $settings['bank_interest_percentage'] : 5,
                'borettslag_capital_cost'    => isset( $settings['borettslag_capital_cost'] ) ? (float) $settings['borettslag_capital_cost'] : 5,
                'minimum_savings_percentage' => isset( $settings['minimum_savings_percentage'] ) ? (int) $settings['minimum_savings_percentage'] : 10,
                'search_button_url'          => isset( $settings['search_button_url'] ) ? esc_url( $settings['search_button_url'] ) : '',
                'search_button_new_tab'      => isset( $settings['search_button_new_tab'] ) && $settings['search_button_new_tab'] ? true : false,
                'oslobolig_rent_help'        => isset( $settings['oslobolig_rent_help'] ) ? $settings['oslobolig_rent_help'] : '',
                'common_costs_help'          => isset( $settings['common_costs_help'] ) ? $settings['common_costs_help'] : '',
                'capital_cost_help'          => isset( $settings['capital_cost_help'] ) ? $settings['capital_cost_help'] : '',
                'bank_interest_help'         => isset( $settings['bank_interest_help'] ) ? $settings['bank_interest_help'] : '',
                'currency_symbol'            => isset( $settings['currency_symbol'] ) ? $settings['currency_symbol'] : 'kr',
                'decimal_separator'          => isset( $settings['decimal_separator'] ) ? $settings['decimal_separator'] : ',',
                'thousands_separator'        => isset( $settings['thousands_separator'] ) ? $settings['thousands_separator'] : ' ',
            )
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_boligkalkulator' === $hook ) {
            wp_enqueue_style(
                'boligkalkulator-admin',
                BOLIGKALKULATOR_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                BOLIGKALKULATOR_VERSION
            );

            wp_enqueue_script(
                'boligkalkulator-admin',
                BOLIGKALKULATOR_PLUGIN_URL . 'assets/js/admin.js',
                array( 'jquery' ),
                BOLIGKALKULATOR_VERSION,
                true
            );
        }
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'boligkalkulator_settings_group',
            'boligkalkulator_settings',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'show_in_rest'      => false,
            )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $settings ) {
        if ( ! is_array( $settings ) ) {
            $settings = array();
        }

        return array(
            'income_multiplier'          => isset( $settings['income_multiplier'] ) ? floatval( $settings['income_multiplier'] ) : 5,
            'savings_multiplier'         => isset( $settings['savings_multiplier'] ) ? floatval( $settings['savings_multiplier'] ) : 2,
            'default_ownership_share'    => isset( $settings['default_ownership_share'] ) ? absint( $settings['default_ownership_share'] ) : 50,
            'min_ownership_share'        => isset( $settings['min_ownership_share'] ) ? absint( $settings['min_ownership_share'] ) : 50,
            'max_ownership_share'        => isset( $settings['max_ownership_share'] ) ? absint( $settings['max_ownership_share'] ) : 90,
            'children_deduction'         => isset( $settings['children_deduction'] ) ? absint( $settings['children_deduction'] ) : 500000,
            'cars_deduction'             => isset( $settings['cars_deduction'] ) ? absint( $settings['cars_deduction'] ) : 200000,
            'oslobolig_rent_percentage'  => isset( $settings['oslobolig_rent_percentage'] ) ? floatval( $settings['oslobolig_rent_percentage'] ) : 4.5,
            'estimated_price_per_kvm'    => isset( $settings['estimated_price_per_kvm'] ) ? absint( $settings['estimated_price_per_kvm'] ) : 120000,
            'drift_cost_per_kvm'         => isset( $settings['drift_cost_per_kvm'] ) ? absint( $settings['drift_cost_per_kvm'] ) : 50,
            'broadband_cost'             => isset( $settings['broadband_cost'] ) ? absint( $settings['broadband_cost'] ) : 400,
            'loan_repayment_years'       => isset( $settings['loan_repayment_years'] ) ? absint( $settings['loan_repayment_years'] ) : 30,
            'bank_interest_percentage'   => isset( $settings['bank_interest_percentage'] ) ? floatval( $settings['bank_interest_percentage'] ) : 5,
            'borettslag_capital_cost'    => isset( $settings['borettslag_capital_cost'] ) ? floatval( $settings['borettslag_capital_cost'] ) : 5,
            'minimum_savings_percentage' => isset( $settings['minimum_savings_percentage'] ) ? absint( $settings['minimum_savings_percentage'] ) : 10,
            'search_button_url'          => isset( $settings['search_button_url'] ) ? esc_url_raw( $settings['search_button_url'] ) : '',
            'search_button_new_tab'      => isset( $settings['search_button_new_tab'] ) && $settings['search_button_new_tab'] ? 1 : 0,
            'annual_income_help'         => isset( $settings['annual_income_help'] ) ? sanitize_textarea_field( $settings['annual_income_help'] ) : '',
            'savings_help'               => isset( $settings['savings_help'] ) ? sanitize_textarea_field( $settings['savings_help'] ) : '',
            'existing_debt_help'         => isset( $settings['existing_debt_help'] ) ? sanitize_textarea_field( $settings['existing_debt_help'] ) : '',
            'children_count_help'        => isset( $settings['children_count_help'] ) ? sanitize_textarea_field( $settings['children_count_help'] ) : '',
            'cars_count_help'            => isset( $settings['cars_count_help'] ) ? sanitize_textarea_field( $settings['cars_count_help'] ) : '',
            'ownership_form_help'        => isset( $settings['ownership_form_help'] ) ? sanitize_textarea_field( $settings['ownership_form_help'] ) : '',
            'total_price_help'           => isset( $settings['total_price_help'] ) ? sanitize_textarea_field( $settings['total_price_help'] ) : '',
            'your_share_help'            => isset( $settings['your_share_help'] ) ? sanitize_textarea_field( $settings['your_share_help'] ) : '',
            'financing_help'             => isset( $settings['financing_help'] ) ? sanitize_textarea_field( $settings['financing_help'] ) : '',
            'two_applicants_text'        => isset( $settings['two_applicants_text'] ) ? sanitize_textarea_field( $settings['two_applicants_text'] ) : 'Hvis dere er to søkere, kan dere summere opp tallene i hvert felt.',
            'oslobolig_rent_help'        => isset( $settings['oslobolig_rent_help'] ) ? sanitize_textarea_field( $settings['oslobolig_rent_help'] ) : '',
            'common_costs_help'          => isset( $settings['common_costs_help'] ) ? sanitize_textarea_field( $settings['common_costs_help'] ) : '',
            'capital_cost_help'          => isset( $settings['capital_cost_help'] ) ? sanitize_textarea_field( $settings['capital_cost_help'] ) : '',
            'bank_interest_help'         => isset( $settings['bank_interest_help'] ) ? sanitize_textarea_field( $settings['bank_interest_help'] ) : '',
            'currency_symbol'            => isset( $settings['currency_symbol'] ) ? sanitize_text_field( $settings['currency_symbol'] ) : 'kr',
            'decimal_separator'          => isset( $settings['decimal_separator'] ) ? sanitize_text_field( $settings['decimal_separator'] ) : ',',
            'thousands_separator'        => isset( $settings['thousands_separator'] ) ? sanitize_text_field( $settings['thousands_separator'] ) : ' ',
        );
    }
}
