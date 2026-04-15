<?php
/**
 * Shortcode for embedding the calculator
 */

add_shortcode( 'boligkalkulator', 'boligkalkulator_render_shortcode' );

function boligkalkulator_render_shortcode( $atts = array() ) {
    $atts = shortcode_atts( array(
        'id' => 'boligkalkulator-' . uniqid(),
    ), $atts, 'boligkalkulator' );

    $tab_id = $atts['id'];

    // Get backend settings
    $settings                = get_option( 'boligkalkulator_settings', array() );
    $min_ownership_share     = isset( $settings['min_ownership_share'] ) ? intval( $settings['min_ownership_share'] ) : 50;
    $max_ownership_share     = isset( $settings['max_ownership_share'] ) ? intval( $settings['max_ownership_share'] ) : 90;
    $default_ownership_share = isset( $settings['default_ownership_share'] ) ? intval( $settings['default_ownership_share'] ) : 50;
    $minimum_savings_pct     = isset( $settings['minimum_savings_percentage'] ) ? intval( $settings['minimum_savings_percentage'] ) : 10;
    $currency_symbol         = isset( $settings['currency_symbol'] ) ? $settings['currency_symbol'] : 'kr';

    // Help texts
    $oslobolig_rent_help  = isset( $settings['oslobolig_rent_help'] ) ? $settings['oslobolig_rent_help'] : '';
    $common_costs_help    = isset( $settings['common_costs_help'] ) ? $settings['common_costs_help'] : '';
    $capital_cost_help    = isset( $settings['capital_cost_help'] ) ? $settings['capital_cost_help'] : '';
    $bank_interest_help   = isset( $settings['bank_interest_help'] ) ? $settings['bank_interest_help'] : '';

    // Tab 1 Help texts
    $annual_income_help   = isset( $settings['annual_income_help'] ) ? $settings['annual_income_help'] : '';
    $savings_help         = isset( $settings['savings_help'] ) ? $settings['savings_help'] : '';
    $existing_debt_help   = isset( $settings['existing_debt_help'] ) ? $settings['existing_debt_help'] : '';
    $children_count_help  = isset( $settings['children_count_help'] ) ? $settings['children_count_help'] : '';
    $cars_count_help      = isset( $settings['cars_count_help'] ) ? $settings['cars_count_help'] : '';

    // Info box text
    $two_applicants_text  = isset( $settings['two_applicants_text'] ) ? $settings['two_applicants_text'] : 'Hvis dere er to søkere, kan dere summere opp tallene i hvert felt.';

    // Tab 2 Help texts
    $ownership_form_help  = isset( $settings['ownership_form_help'] ) ? $settings['ownership_form_help'] : '';
    $total_price_help     = isset( $settings['total_price_help'] ) ? $settings['total_price_help'] : '';
    $your_share_help      = isset( $settings['your_share_help'] ) ? $settings['your_share_help'] : '';
    $financing_help       = isset( $settings['financing_help'] ) ? $settings['financing_help'] : '';

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $tab_id ); ?>" class="boligkalkulator-wrapper">
        <div class="boligkalkulator-container">
            <div class="boligkalkulator-header">
                <h2><?php esc_html_e( 'Boligkalkulator', 'boligkalkulator' ); ?></h2>
                <p class="boligkalkulator-subtitle"><?php esc_html_e( 'Beregn ditt boligbudsjett steg for steg', 'boligkalkulator' ); ?></p>
            </div>

            <!-- Tab Navigation -->
            <div class="boligkalkulator-tabs">
                <button class="boligkalkulator-tab-button boligkalkulator-tab-active" data-tab="1">
                    <span class="boligkalkulator-tab-number">1</span>
                    <span class="boligkalkulator-tab-label"><?php esc_html_e( 'Hvor mye kan jeg kjøpe for?', 'boligkalkulator' ); ?></span>
                </button>
                <button class="boligkalkulator-tab-button" data-tab="2">
                    <span class="boligkalkulator-tab-number">2</span>
                    <span class="boligkalkulator-tab-label"><?php esc_html_e( 'Finansiering av boligen', 'boligkalkulator' ); ?></span>
                </button>
                <button class="boligkalkulator-tab-button" data-tab="3">
                    <span class="boligkalkulator-tab-number">3</span>
                    <span class="boligkalkulator-tab-label"><?php esc_html_e( 'Budsjett', 'boligkalkulator' ); ?></span>
                </button>
            </div>

            <div class="boligkalkulator-content">
                <!-- TAB 1: Hvor mye kan jeg kjøpe for? -->
                <div class="boligkalkulator-tab boligkalkulator-tab-active" data-tab="1">
                    <div class="boligkalkulator-tab-title">
                        <?php esc_html_e( 'Hvor mye kan jeg kjøpe for?', 'boligkalkulator' ); ?>
                    </div>

                    <div class="boligkalkulator-tab-content">
                        <div class="boligkalkulator-input-row">
                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Årsinntekt', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="annual_income_help" data-content="<?php echo esc_attr( $annual_income_help ); ?>">?</span>
                                </label>
                                <div class="boligkalkulator-input-wrapper">
                                    <input
                                        type="number"
                                        class="boligkalkulator-input boligkalkulator-annual-income"
                                        value="740000"
                                        min="0"
                                        step="10000" />
                                    <span class="boligkalkulator-currency"><?php echo esc_html( $currency_symbol ); ?></span>
                                </div>
                            </div>

                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Sparepenger', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="savings_help" data-content="<?php echo esc_attr( $savings_help ); ?>">?</span>
                                </label>
                                <div class="boligkalkulator-input-wrapper">
                                    <input
                                        type="number"
                                        class="boligkalkulator-input boligkalkulator-savings"
                                        value="250000"
                                        min="0"
                                        step="10000" />
                                    <span class="boligkalkulator-currency"><?php echo esc_html( $currency_symbol ); ?></span>
                                </div>
                            </div>

                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Dagens lån', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="existing_debt_help" data-content="<?php echo esc_attr( $existing_debt_help ); ?>">?</span>
                                </label>
                                <div class="boligkalkulator-input-wrapper">
                                    <input
                                        type="number"
                                        class="boligkalkulator-input boligkalkulator-existing-debt"
                                        value="400000"
                                        min="0"
                                        step="10000" />
                                    <span class="boligkalkulator-currency"><?php echo esc_html( $currency_symbol ); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="boligkalkulator-input-row boligkalkulator-info-row">
                            <div class="boligkalkulator-info-box">
                                <span class="boligkalkulator-info-icon">i</span>
                                <span class="boligkalkulator-info-text"><?php echo esc_html( $two_applicants_text ); ?></span>
                            </div>
                        </div>

                        <div class="boligkalkulator-input-row boligkalkulator-column-break">
                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Antall barn', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="children_count_help" data-content="<?php echo esc_attr( $children_count_help ); ?>">?</span>
                                </label>
                                <div class="boligkalkulator-input-wrapper">
                                    <input
                                        type="number"
                                        class="boligkalkulator-input boligkalkulator-children-count"
                                        value="0"
                                        min="0"
                                        step="1" />
                                </div>
                            </div>

                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Antall biler', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="cars_count_help" data-content="<?php echo esc_attr( $cars_count_help ); ?>">?</span>
                                </label>
                                <div class="boligkalkulator-input-wrapper">
                                    <input
                                        type="number"
                                        class="boligkalkulator-input boligkalkulator-cars-count"
                                        value="0"
                                        min="0"
                                        step="1" />
                                </div>
                            </div>
                        </div>

                        <div class="boligkalkulator-result-box boligkalkulator-buying-power">
                            <span class="boligkalkulator-result-label"><?php esc_html_e( 'Du kan kjøpe for', 'boligkalkulator' ); ?></span>
                            <span class="boligkalkulator-result-value" data-type="buying-power">3 800 000 <?php echo esc_html( $currency_symbol ); ?></span>
                        </div>

                        <div class="boligkalkulator-result-box boligkalkulator-boligspleis-price">
                            <span class="boligkalkulator-result-label">
                                <?php
                                printf(
                                    /* translators: %d: minimum ownership share percentage */
                                    esc_html__( 'Din pris med Boligspleis (%d%%)', 'boligkalkulator' ),
                                    $min_ownership_share
                                );
                                ?>
                            </span>
                            <span class="boligkalkulator-result-value" data-type="boligspleis-price">1 900 000 <?php echo esc_html( $currency_symbol ); ?></span>
                        </div>

                        <div class="boligkalkulator-tab-navigation">
                            <button class="boligkalkulator-btn boligkalkulator-btn-next" data-next-tab="2">
                                <?php esc_html_e( 'Neste', 'boligkalkulator' ); ?> →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Finansiering av boligen -->
                <div class="boligkalkulator-tab" data-tab="2">
                    <div class="boligkalkulator-tab-title">
                        <?php esc_html_e( 'Finansiering av boligen', 'boligkalkulator' ); ?>
                    </div>

                    <div class="boligkalkulator-tab-content">
                        <div class="boligkalkulator-error-message boligkalkulator-own-capital-error" style="display: none; background-color: #fee; border: 1px solid #fcc; color: #c00; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
                            <?php
                            printf(
                                /* translators: %d: minimum savings percentage */
                                esc_html__( 'Du tilfredstiller ikke kravet om minimum %d%% sparepenger.', 'boligkalkulator' ),
                                $minimum_savings_pct
                            );
                            ?>
                        </div>

                        <div class="boligkalkulator-input-row">
                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Eierform', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="ownership_form_help" data-content="<?php echo esc_attr( $ownership_form_help ); ?>">?</span>
                                </label>
                                <select class="boligkalkulator-input boligkalkulator-ownership-form">
                                    <option value="borettslag"><?php esc_html_e( 'Borettslag', 'boligkalkulator' ); ?></option>
                                    <option value="selveier"><?php esc_html_e( 'Selveier', 'boligkalkulator' ); ?></option>
                                </select>
                            </div>

                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Ønsket eierandel', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-slider-value"><?php echo $default_ownership_share; ?>%</span>
                                </label>
                                <input
                                    type="range"
                                    class="boligkalkulator-input boligkalkulator-ownership-slider"
                                    min="<?php echo $min_ownership_share; ?>"
                                    max="<?php echo $max_ownership_share; ?>"
                                    value="<?php echo $default_ownership_share; ?>"
                                    step="10" />
                            </div>
                        </div>

                        <div class="boligkalkulator-input-row">
                            <div class="boligkalkulator-input-group">
                                <label class="boligkalkulator-label">
                                    <?php esc_html_e( 'Kjøpesum - totalpris', 'boligkalkulator' ); ?>
                                    <span class="boligkalkulator-help-icon" data-help="total_price_help" data-content="<?php echo esc_attr( $total_price_help ); ?>">?</span>
                                </label>
                                <div class="boligkalkulator-input-wrapper">
                                    <input
                                        type="number"
                                        class="boligkalkulator-input boligkalkulator-total-price"
                                        value="3000000"
                                        min="0"
                                        step="100000" />
                                    <span class="boligkalkulator-currency"><?php echo esc_html( $currency_symbol ); ?></span>
                                    <span class="boligkalkulator-percentage">100%</span>
                                </div>
                            </div>
                        </div>

                        <div class="boligkalkulator-financing-breakdown boligkalkulator-borettslag-only">
                            <div class="boligkalkulator-financing-row">
                                <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Innskudd', 'boligkalkulator' ); ?></span>
                                <span class="boligkalkulator-financing-value" data-type="deposit">1 500 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                <span class="boligkalkulator-financing-percent">100%</span>
                            </div>
                            <div class="boligkalkulator-financing-row">
                                <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Fellesgjeld', 'boligkalkulator' ); ?></span>
                                <span class="boligkalkulator-financing-value" data-type="common-debt">1 500 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                <span class="boligkalkulator-financing-percent">100%</span>
                            </div>
                        </div>

                        <div class="boligkalkulator-your-share boligkalkulator-highlight-section">
                            <h4>
                                <?php esc_html_e( 'Din eierandel', 'boligkalkulator' ); ?>
                                <span class="boligkalkulator-help-icon" data-help="your_share_help" data-content="<?php echo esc_attr( $your_share_help ); ?>">?</span>
                            </h4>
                            <div class="boligkalkulator-financing-breakdown">
                                <div class="boligkalkulator-financing-row boligkalkulator-highlight-row">
                                    <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Kjøpesum - din eierandel', 'boligkalkulator' ); ?></span>
                                    <span class="boligkalkulator-financing-value" data-type="your-price">1 500 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                    <span class="boligkalkulator-financing-percent" data-type="your-price-percent">50%</span>
                                </div>
                                <div class="boligkalkulator-financing-row boligkalkulator-borettslag-only">
                                    <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Fellesgjeld - din andel', 'boligkalkulator' ); ?></span>
                                    <span class="boligkalkulator-financing-value" data-type="your-debt">750 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                    <span class="boligkalkulator-financing-percent" data-type="your-debt-percent">50%</span>
                                </div>
                                <div class="boligkalkulator-financing-row boligkalkulator-borettslag-only">
                                    <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Innskudd', 'boligkalkulator' ); ?></span>
                                    <span class="boligkalkulator-financing-value" data-type="your-deposit">750 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                    <span class="boligkalkulator-financing-percent" data-type="your-deposit-percent">50%</span>
                                </div>
                            </div>
                        </div>

                        <div class="boligkalkulator-your-financing">
                            <h4>
                                <?php esc_html_e( 'Ditt innskudd finansieres med', 'boligkalkulator' ); ?>
                                <span class="boligkalkulator-help-icon" data-help="financing_help" data-content="<?php echo esc_attr( $financing_help ); ?>">?</span>
                            </h4>
                            <div class="boligkalkulator-financing-breakdown">
                                <div class="boligkalkulator-financing-row">
                                    <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Dine sparepenger', 'boligkalkulator' ); ?></span>
                                    <span class="boligkalkulator-financing-value" data-type="own-savings">200 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                    <span class="boligkalkulator-financing-desc" data-type="own-savings-desc">
                                        <?php
                                        printf(
                                            /* translators: %d: minimum savings percentage */
                                            esc_html__( 'Minimum %d%% av innskudd', 'boligkalkulator' ),
                                            $minimum_savings_pct
                                        );
                                        ?>
                                    </span>
                                </div>
                                <div class="boligkalkulator-financing-row">
                                    <span class="boligkalkulator-financing-label"><?php esc_html_e( 'Lån fra banken', 'boligkalkulator' ); ?></span>
                                    <span class="boligkalkulator-financing-value" data-type="bank-loan">550 000 <?php echo esc_html( $currency_symbol ); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="boligkalkulator-tab-navigation">
                            <button class="boligkalkulator-btn boligkalkulator-btn-prev" data-prev-tab="1">
                                ← <?php esc_html_e( 'Forrige', 'boligkalkulator' ); ?>
                            </button>
                            <button class="boligkalkulator-btn boligkalkulator-btn-next" data-next-tab="3">
                                <?php esc_html_e( 'Neste', 'boligkalkulator' ); ?> →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Budsjett -->
                <div class="boligkalkulator-tab" data-tab="3">
                    <div class="boligkalkulator-tab-title">
                        <?php esc_html_e( 'Budsjett', 'boligkalkulator' ); ?>
                    </div>

                    <div class="boligkalkulator-tab-content">
                        <div class="boligkalkulator-budget-section">
                            <h4><?php esc_html_e( 'Estimerte månedlige utgifter', 'boligkalkulator' ); ?></h4>

                            <div class="boligkalkulator-budget-items">
                                <div class="boligkalkulator-budget-item boligkalkulator-budget-item-highlight">
                                    <span class="boligkalkulator-budget-label"><?php esc_html_e( 'Leie av OsloBolig (estimat)', 'boligkalkulator' ); ?></span>
                                    <div class="boligkalkulator-budget-value-wrapper">
                                        <span class="boligkalkulator-budget-value" data-type="oslobolig-rent">5 625 <?php echo esc_html( $currency_symbol ); ?></span>
                                        <span class="boligkalkulator-help-icon" data-help="oslobolig_rent_help" data-content="<?php echo esc_attr( $oslobolig_rent_help ); ?>">?</span>
                                    </div>
                                </div>
                            </div>

                            <div class="boligkalkulator-budget-subtitle">
                                <?php esc_html_e( 'Felles utgifter - din andel', 'boligkalkulator' ); ?>
                            </div>

                            <div class="boligkalkulator-budget-items">
                                <div class="boligkalkulator-budget-item">
                                    <span class="boligkalkulator-budget-label"><?php esc_html_e( 'Fellesutgifter - din andel', 'boligkalkulator' ); ?></span>
                                    <div class="boligkalkulator-budget-value-wrapper">
                                        <span class="boligkalkulator-budget-value" data-type="common-costs">1 400 <?php echo esc_html( $currency_symbol ); ?></span>
                                        <span class="boligkalkulator-help-icon" data-help="common_costs_help" data-content="<?php echo esc_attr( $common_costs_help ); ?>">?</span>
                                    </div>
                                </div>

                                <div class="boligkalkulator-budget-item boligkalkulator-capital-cost-item">
                                    <span class="boligkalkulator-budget-label"><?php esc_html_e( 'Kapitalkostnad – din andel fellesgjeld', 'boligkalkulator' ); ?></span>
                                    <div class="boligkalkulator-budget-value-wrapper">
                                        <span class="boligkalkulator-budget-value" data-type="capital-cost">3 125 <?php echo esc_html( $currency_symbol ); ?></span>
                                        <span class="boligkalkulator-help-icon" data-help="capital_cost_help" data-content="<?php echo esc_attr( $capital_cost_help ); ?>">?</span>
                                    </div>
                                </div>

                                <div class="boligkalkulator-budget-item">
                                    <span class="boligkalkulator-budget-label"><?php esc_html_e( 'Renter og avdrag på banklån', 'boligkalkulator' ); ?></span>
                                    <div class="boligkalkulator-budget-value-wrapper">
                                        <span class="boligkalkulator-budget-value" data-type="bank-interest">3 819 <?php echo esc_html( $currency_symbol ); ?></span>
                                        <span class="boligkalkulator-help-icon" data-help="bank_interest_help" data-content="<?php echo esc_attr( $bank_interest_help ); ?>">?</span>
                                    </div>
                                </div>
                            </div>

                            <div class="boligkalkulator-result-box boligkalkulator-monthly-costs">
                                <span class="boligkalkulator-result-label"><?php esc_html_e( 'Månedlige boutgifter', 'boligkalkulator' ); ?></span>
                                <span class="boligkalkulator-result-value" data-type="total-monthly">13 969 <?php echo esc_html( $currency_symbol ); ?></span>
                            </div>

                            <div class="boligkalkulator-comparison-section">
                                <h4><?php esc_html_e( 'Sammenligning mot 100% kjøp og 90% banklån', 'boligkalkulator' ); ?></h4>

                                <div class="boligkalkulator-budget-items">
                                    <div class="boligkalkulator-budget-item boligkalkulator-budget-item-highlight">
                                        <span class="boligkalkulator-comparison-label"><?php esc_html_e( 'Leie av OsloBolig', 'boligkalkulator' ); ?></span>
                                        <span class="boligkalkulator-comparison-value" data-type="comparison-oslobolig">0</span>
                                    </div>
                                </div>

                                <div class="boligkalkulator-budget-subtitle">
                                    <?php esc_html_e( 'Felles utgifter - din andel', 'boligkalkulator' ); ?>
                                </div>

                                <div class="boligkalkulator-budget-items">
                                    <div class="boligkalkulator-budget-item">
                                        <span class="boligkalkulator-comparison-label"><?php esc_html_e( 'Driftskostnader - din andel', 'boligkalkulator' ); ?></span>
                                        <span class="boligkalkulator-comparison-value" data-type="comparison-drift">2 900</span>
                                    </div>
                                    <div class="boligkalkulator-budget-item">
                                        <span class="boligkalkulator-comparison-label"><?php esc_html_e( 'Kapitalkostnad fellesgjeld', 'boligkalkulator' ); ?></span>
                                        <span class="boligkalkulator-comparison-value" data-type="comparison-capital">12 500</span>
                                    </div>
                                    <div class="boligkalkulator-budget-item">
                                        <span class="boligkalkulator-comparison-label"><?php esc_html_e( 'Renter og avdrag på eget banklån', 'boligkalkulator' ); ?></span>
                                        <span class="boligkalkulator-comparison-value" data-type="comparison-renter">18 750 <span class="boligkalkulator-comparison-percent">90%</span></span>
                                    </div>
                                </div>

                                <div class="boligkalkulator-result-box">
                                    <span class="boligkalkulator-result-label"><?php esc_html_e( 'Månedlige boutgifter', 'boligkalkulator' ); ?></span>
                                    <span class="boligkalkulator-result-value" data-type="comparison-total">34 150</span>
                                </div>
                            </div>
                        </div>

                        <div class="boligkalkulator-tab-navigation">
                            <button class="boligkalkulator-btn boligkalkulator-btn-prev" data-prev-tab="2">
                                ← <?php esc_html_e( 'Forrige', 'boligkalkulator' ); ?>
                            </button>
                            <button class="boligkalkulator-btn boligkalkulator-btn-search">
                                <?php esc_html_e( 'Søk nå', 'boligkalkulator' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
