<?php
/**
 * Admin Settings Page
 */

add_action( 'admin_menu', function() {
    add_options_page(
        'Boligkalkulator Innstillinger',
        'Boligkalkulator',
        'manage_options',
        'boligkalkulator',
        'boligkalkulator_render_settings_page'
    );
} );

function boligkalkulator_render_settings_page() {
    ?>
    <div class="wrap boligkalkulator-admin-page">
        <div class="boligkalkulator-admin-header">
            <h1><?php esc_html_e( 'Boligkalkulator Innstillinger', 'boligkalkulator' ); ?></h1>
            <p class="boligkalkulator-admin-subtitle"><?php esc_html_e( 'Konfigurer kalkulator-parametere for hver tab', 'boligkalkulator' ); ?></p>
        </div>
        
        <div class="boligkalkulator-shortcode-card">
            <h3><?php esc_html_e( 'Shortcode', 'boligkalkulator' ); ?></h3>
            <p><?php esc_html_e( 'Bruk denne shortcode for å vise kalkulatoren på siden din:', 'boligkalkulator' ); ?></p>
            <div class="boligkalkulator-shortcode-wrapper">
                <input type="text" 
                       id="boligkalkulator-shortcode" 
                       value="[boligkalkulator]" 
                       readonly 
                       class="boligkalkulator-shortcode-input" />
                <button type="button" 
                        id="boligkalkulator-copy-shortcode" 
                        class="boligkalkulator-copy-btn">
                    <?php esc_html_e( 'Kopier', 'boligkalkulator' ); ?>
                </button>
            </div>
        </div>
        
        <form method="post" action="options.php" class="boligkalkulator-form">
            <?php
            settings_fields( 'boligkalkulator_settings_group' );
            $settings = get_option( 'boligkalkulator_settings', array() );
            ?>

            <div class="boligkalkulator-settings-grid">
                
                <!-- TAB 1 SETTINGS -->
                <div class="boligkalkulator-settings-card">
                    <div class="boligkalkulator-card-header">
                        <span class="boligkalkulator-card-icon">1</span>
                        <h2><?php esc_html_e( 'Tab 1: Hvor mye kan jeg kjøpe for?', 'boligkalkulator' ); ?></h2>
                    </div>
                    <div class="boligkalkulator-card-content">
                        <div class="boligkalkulator-form-group">
                            <label for="income_multiplier"><?php esc_html_e( 'Årsinntekt multiplier', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="income_multiplier" 
                                   name="boligkalkulator_settings[income_multiplier]" 
                                   value="<?php echo esc_attr( isset( $settings['income_multiplier'] ) ? $settings['income_multiplier'] : 5 ); ?>" 
                                   min="1" 
                                   max="20" 
                                   step="0.5" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Hvor mange ganger årsinntekt kan lånes (f.eks. 5 = årsinntekt × 5)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="savings_multiplier"><?php esc_html_e( 'Sparepenger multiplier', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="savings_multiplier" 
                                   name="boligkalkulator_settings[savings_multiplier]" 
                                   value="<?php echo esc_attr( isset( $settings['savings_multiplier'] ) ? $settings['savings_multiplier'] : 2 ); ?>" 
                                   min="1" 
                                   max="10" 
                                   step="0.5" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Hvor mange ganger sparepengene skal telles (f.eks. 2 = sparepenger × 2)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="children_deduction"><?php esc_html_e( 'Trekk per barn under 18år', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="children_deduction" 
                                   name="boligkalkulator_settings[children_deduction]" 
                                   value="<?php echo esc_attr( isset( $settings['children_deduction'] ) ? $settings['children_deduction'] : 500000 ); ?>" 
                                   min="0" 
                                   step="10000" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Beløp som trekkes fra kjøpekraft per barn (f.eks. 500000)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="cars_deduction"><?php esc_html_e( 'Trekk per bil', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="cars_deduction" 
                                   name="boligkalkulator_settings[cars_deduction]" 
                                   value="<?php echo esc_attr( isset( $settings['cars_deduction'] ) ? $settings['cars_deduction'] : 200000 ); ?>" 
                                   min="0" 
                                   step="10000" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Beløp som trekkes fra kjøpekraft per bil (f.eks. 200000)', 'boligkalkulator' ); ?></span>
                        </div>
                    </div>
                </div>

                <!-- TAB 2 SETTINGS -->
                <div class="boligkalkulator-settings-card">
                    <div class="boligkalkulator-card-header">
                        <span class="boligkalkulator-card-icon">2</span>
                        <h2><?php esc_html_e( 'Tab 2: Finansiering av boligen', 'boligkalkulator' ); ?></h2>
                    </div>
                    <div class="boligkalkulator-card-content">
                        <div class="boligkalkulator-form-group">
                            <label for="default_ownership_share"><?php esc_html_e( 'Standard ønsket eierandel (%)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="default_ownership_share" 
                                   name="boligkalkulator_settings[default_ownership_share]" 
                                   value="<?php echo esc_attr( isset( $settings['default_ownership_share'] ) ? $settings['default_ownership_share'] : 50 ); ?>" 
                                   min="1" 
                                   max="100" 
                                   step="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Standardverdi for eierandel-slider (f.eks. 50 = 50%)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="min_ownership_share"><?php esc_html_e( 'Minimum eierandel (%)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="min_ownership_share" 
                                   name="boligkalkulator_settings[min_ownership_share]" 
                                   value="<?php echo esc_attr( isset( $settings['min_ownership_share'] ) ? $settings['min_ownership_share'] : 50 ); ?>" 
                                   min="1" 
                                   max="100" 
                                   step="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Laveste tillatt eierandel (f.eks. 50 = 50%)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="max_ownership_share"><?php esc_html_e( 'Maksimum eierandel (%)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="max_ownership_share" 
                                   name="boligkalkulator_settings[max_ownership_share]" 
                                   value="<?php echo esc_attr( isset( $settings['max_ownership_share'] ) ? $settings['max_ownership_share'] : 90 ); ?>" 
                                   min="1" 
                                   max="100" 
                                   step="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Høyeste tillatt eierandel (f.eks. 90 = 90%)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="minimum_savings_percentage"><?php esc_html_e( 'Minimum % sparepenger (Borettslag)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="minimum_savings_percentage" 
                                   name="boligkalkulator_settings[minimum_savings_percentage]" 
                                   value="<?php echo esc_attr( isset( $settings['minimum_savings_percentage'] ) ? $settings['minimum_savings_percentage'] : 10 ); ?>" 
                                   min="0" 
                                   max="100" 
                                   step="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Minimum prosent av innskuddsandel som må være i sparepenger (f.eks. 10 = 10%)', 'boligkalkulator' ); ?></span>
                        </div>
                    </div>
                </div>

                <!-- TAB 3 SETTINGS -->
                <div class="boligkalkulator-settings-card">
                    <div class="boligkalkulator-card-header">
                        <span class="boligkalkulator-card-icon">3</span>
                        <h2><?php esc_html_e( 'Tab 3: Budsjett - Månedlige utgifter', 'boligkalkulator' ); ?></h2>
                    </div>
                    <div class="boligkalkulator-card-content">
                        <div class="boligkalkulator-form-group">
                            <label for="oslobolig_rent_percentage"><?php esc_html_e( 'OsloBolig leieprosent (%)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="oslobolig_rent_percentage" 
                                   name="boligkalkulator_settings[oslobolig_rent_percentage]" 
                                   value="<?php echo esc_attr( isset( $settings['oslobolig_rent_percentage'] ) ? $settings['oslobolig_rent_percentage'] : 4.5 ); ?>" 
                                   min="0" 
                                   step="0.1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Årlig prosentandel av kjøpesum (f.eks. 4.5 = 4.5%)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="borettslag_capital_cost"><?php esc_html_e( 'Borettslag - Kapitalkostnad fellesgjeld (%)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="borettslag_capital_cost" 
                                   name="boligkalkulator_settings[borettslag_capital_cost]" 
                                   value="<?php echo esc_attr( isset( $settings['borettslag_capital_cost'] ) ? $settings['borettslag_capital_cost'] : 5 ); ?>" 
                                   min="0" 
                                   step="0.1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Årlig prosentandel for kapitalkostnad fellesgjeld (f.eks. 5 = 5%)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="bank_interest_percentage"><?php esc_html_e( 'Banklånrente prosentandel (%)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="bank_interest_percentage" 
                                   name="boligkalkulator_settings[bank_interest_percentage]" 
                                   value="<?php echo esc_attr( isset( $settings['bank_interest_percentage'] ) ? $settings['bank_interest_percentage'] : 5 ); ?>" 
                                   min="0" 
                                   step="0.1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Årlig rente + avdrag prosentandel (f.eks. 5 = 5%)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="estimated_price_per_kvm"><?php esc_html_e( 'Estimert pris per m² (kr)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="estimated_price_per_kvm" 
                                   name="boligkalkulator_settings[estimated_price_per_kvm]" 
                                   value="<?php echo esc_attr( isset( $settings['estimated_price_per_kvm'] ) ? $settings['estimated_price_per_kvm'] : 120000 ); ?>" 
                                   min="0" 
                                   step="10000" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Brukt til å beregne driftskostnader (f.eks. 120000 kr/m²)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="drift_cost_per_kvm"><?php esc_html_e( 'Driftskostnad per m² (kr)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="drift_cost_per_kvm" 
                                   name="boligkalkulator_settings[drift_cost_per_kvm]" 
                                   value="<?php echo esc_attr( isset( $settings['drift_cost_per_kvm'] ) ? $settings['drift_cost_per_kvm'] : 50 ); ?>" 
                                   min="0" 
                                   step="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Årlig driftskostnad per m² (f.eks. 50 kr/m²)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="broadband_cost"><?php esc_html_e( 'Bredbånd kostnad (kr)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="broadband_cost" 
                                   name="boligkalkulator_settings[broadband_cost]" 
                                   value="<?php echo esc_attr( isset( $settings['broadband_cost'] ) ? $settings['broadband_cost'] : 400 ); ?>" 
                                   min="0" 
                                   step="10" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Månedlig bredbånd kostnad (f.eks. 400 kr)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="loan_repayment_years"><?php esc_html_e( 'Avdragslengde banklån (år)', 'boligkalkulator' ); ?></label>
                            <input type="number" 
                                   id="loan_repayment_years" 
                                   name="boligkalkulator_settings[loan_repayment_years]" 
                                   value="<?php echo esc_attr( isset( $settings['loan_repayment_years'] ) ? $settings['loan_repayment_years'] : 30 ); ?>" 
                                   min="1" 
                                   step="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'Antall år for avbetaling av banklån (f.eks. 30)', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-highlight">
                            <div class="boligkalkulator-form-group">
                                <label for="search_button_url"><?php esc_html_e( 'URL for "Søk nå"-knapp', 'boligkalkulator' ); ?></label>
                                <input type="url"
                                       id="search_button_url" 
                                       name="boligkalkulator_settings[search_button_url]" 
                                       value="<?php echo esc_attr( isset( $settings['search_button_url'] ) ? $settings['search_button_url'] : '' ); ?>" 
                                       placeholder="https://example.com" />
                                <span class="boligkalkulator-help-text"><?php esc_html_e( 'URL som åpnes når "Søk nå"-knappen klikkes', 'boligkalkulator' ); ?></span>
                            </div>

                            <div class="boligkalkulator-form-group">
                                <label>
                                    <input type="checkbox" 
                                           name="boligkalkulator_settings[search_button_new_tab]" 
                                           value="1"
                                           <?php checked( isset( $settings['search_button_new_tab'] ) && $settings['search_button_new_tab'], 1 ); ?> />
                                    <?php esc_html_e( 'Åpne i ny side', 'boligkalkulator' ); ?>
                                </label>
                                <span class="boligkalkulator-help-text"><?php esc_html_e( 'Hvis avhuket, åpnes lenken i ny side', 'boligkalkulator' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TEKSTER / HELP TEXTS -->
                <div class="boligkalkulator-settings-card boligkalkulator-settings-card-full">
                    <div class="boligkalkulator-card-header">
                        <span class="boligkalkulator-card-icon">📝</span>
                        <h2><?php esc_html_e( 'Hjelpetekster', 'boligkalkulator' ); ?></h2>
                    </div>
                    <div class="boligkalkulator-card-content boligkalkulator-card-content-4col">
                        <div class="boligkalkulator-form-group">
                            <label for="annual_income_help"><?php esc_html_e( 'Årsinntekt - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="annual_income_help" 
                                      name="boligkalkulator_settings[annual_income_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['annual_income_help'] ) ? $settings['annual_income_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="savings_help"><?php esc_html_e( 'Sparepenger - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="savings_help" 
                                      name="boligkalkulator_settings[savings_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['savings_help'] ) ? $settings['savings_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="existing_debt_help"><?php esc_html_e( 'Dagens lån - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="existing_debt_help" 
                                      name="boligkalkulator_settings[existing_debt_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['existing_debt_help'] ) ? $settings['existing_debt_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="children_count_help"><?php esc_html_e( 'Antall barn - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="children_count_help" 
                                      name="boligkalkulator_settings[children_count_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['children_count_help'] ) ? $settings['children_count_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="cars_count_help"><?php esc_html_e( 'Antall biler - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="cars_count_help" 
                                      name="boligkalkulator_settings[cars_count_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['cars_count_help'] ) ? $settings['cars_count_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="oslobolig_rent_help"><?php esc_html_e( 'OsloBolig leieprosent - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="oslobolig_rent_help" 
                                      name="boligkalkulator_settings[oslobolig_rent_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['oslobolig_rent_help'] ) ? $settings['oslobolig_rent_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="common_costs_help"><?php esc_html_e( 'Fellesutgifter - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="common_costs_help" 
                                      name="boligkalkulator_settings[common_costs_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['common_costs_help'] ) ? $settings['common_costs_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="capital_cost_help"><?php esc_html_e( 'Kapitalkostnad - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="capital_cost_help" 
                                      name="boligkalkulator_settings[capital_cost_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['capital_cost_help'] ) ? $settings['capital_cost_help'] : '' ); ?></textarea>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="bank_interest_help"><?php esc_html_e( 'Banklånrente - hjelpetekst', 'boligkalkulator' ); ?></label>
                            <textarea id="bank_interest_help" 
                                      name="boligkalkulator_settings[bank_interest_help]" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e( 'Hjelpetekst som vises ved hover på ? ikonet', 'boligkalkulator' ); ?>"><?php echo esc_textarea( isset( $settings['bank_interest_help'] ) ? $settings['bank_interest_help'] : '' ); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- REGIONAL SETTINGS -->
                <div class="boligkalkulator-settings-card boligkalkulator-settings-card-full">
                    <div class="boligkalkulator-card-header">
                        <span class="boligkalkulator-card-icon">⚙</span>
                        <h2><?php esc_html_e( 'Regionale innstillinger', 'boligkalkulator' ); ?></h2>
                    </div>
                    <div class="boligkalkulator-card-content boligkalkulator-card-content-3col">
                        <div class="boligkalkulator-form-group">
                            <label for="currency_symbol"><?php esc_html_e( 'Valutasymbol', 'boligkalkulator' ); ?></label>
                            <input type="text" 
                                   id="currency_symbol" 
                                   name="boligkalkulator_settings[currency_symbol]" 
                                   value="<?php echo esc_attr( isset( $settings['currency_symbol'] ) ? $settings['currency_symbol'] : 'kr' ); ?>" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'F.eks. kr, NOK, $', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="decimal_separator"><?php esc_html_e( 'Desimalseparator', 'boligkalkulator' ); ?></label>
                            <input type="text" 
                                   id="decimal_separator" 
                                   name="boligkalkulator_settings[decimal_separator]" 
                                   value="<?php echo esc_attr( isset( $settings['decimal_separator'] ) ? $settings['decimal_separator'] : ',' ); ?>" 
                                   maxlength="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'F.eks. , eller .', 'boligkalkulator' ); ?></span>
                        </div>

                        <div class="boligkalkulator-form-group">
                            <label for="thousands_separator"><?php esc_html_e( 'Tusenseparator', 'boligkalkulator' ); ?></label>
                            <input type="text" 
                                   id="thousands_separator" 
                                   name="boligkalkulator_settings[thousands_separator]" 
                                   value="<?php echo esc_attr( isset( $settings['thousands_separator'] ) ? $settings['thousands_separator'] : ' ' ); ?>" 
                                   maxlength="1" />
                            <span class="boligkalkulator-help-text"><?php esc_html_e( 'F.eks. mellomrom, . eller ,', 'boligkalkulator' ); ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="boligkalkulator-form-actions">
                <?php submit_button( __( 'Lagre innstillinger', 'boligkalkulator' ), 'primary', 'submit', true ); ?>
            </div>
        </form>
    </div>
    <?php
}
