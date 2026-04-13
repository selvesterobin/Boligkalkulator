/**
 * Frontend Calculator JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeCalculators();
    });

    function initializeCalculators() {
        const calculators = document.querySelectorAll('.boligkalkulator-wrapper');

        calculators.forEach(calculator => {
            initializeTabs(calculator);
            initializeTab1(calculator);
            initializeTab2(calculator);
            initializeTab3(calculator);
            initializeSearchButton(calculator);
            formatNumberInputs(calculator);
        });
    }

    /**
     * Format all display values with thousands separator on initial load
     */
    function formatNumberInputs(calculator) {
        const settings     = window.boligkalkulatorSettings || {};
        const thousandsSep = settings.thousands_separator || ' ';
        const currencySymbol = settings.currency_symbol || 'kr';

        calculator.querySelectorAll('[data-type]').forEach(el => {
            const text = el.textContent.trim();
            if (text.includes('%')) return;
            const numberMatch = text.match(/[\d\s]+/);
            if (numberMatch) {
                const numberStr = numberMatch[0].replace(/\s/g, '');
                const number    = parseInt(numberStr);
                if (!isNaN(number) && number > 0) {
                    const formatted = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
                    el.textContent  = formatted + ' ' + currencySymbol;
                }
            }
        });
    }

    /**
     * Initialize tab switching
     */
    function initializeTabs(calculator) {
        calculator.querySelectorAll('.boligkalkulator-tab-button').forEach(button => {
            button.addEventListener('click', function() {
                showTab(calculator, this.getAttribute('data-tab'));
            });
        });

        calculator.querySelectorAll('.boligkalkulator-btn-next').forEach(btn => {
            btn.addEventListener('click', function() {
                showTab(calculator, this.getAttribute('data-next-tab'));
            });
        });

        calculator.querySelectorAll('.boligkalkulator-btn-prev').forEach(btn => {
            btn.addEventListener('click', function() {
                showTab(calculator, this.getAttribute('data-prev-tab'));
            });
        });
    }

    function showTab(calculator, tabNumber) {
        calculator.querySelectorAll('.boligkalkulator-tab').forEach(tab => tab.classList.remove('boligkalkulator-tab-active'));
        calculator.querySelectorAll('.boligkalkulator-tab-button').forEach(btn => btn.classList.remove('boligkalkulator-tab-active'));

        const activeTab    = calculator.querySelector(`[data-tab="${tabNumber}"].boligkalkulator-tab`);
        const activeButton = calculator.querySelector(`[data-tab="${tabNumber}"].boligkalkulator-tab-button`);

        if (activeTab)    activeTab.classList.add('boligkalkulator-tab-active');
        if (activeButton) activeButton.classList.add('boligkalkulator-tab-active');

        calculator.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Initialize Tab 1: Hvor mye kan jeg kjøpe for?
     */
    function initializeTab1(calculator) {
        const annualIncomeInput = calculator.querySelector('.boligkalkulator-annual-income');
        const savingsInput      = calculator.querySelector('.boligkalkulator-savings');
        const existingDebtInput = calculator.querySelector('.boligkalkulator-existing-debt');
        const childrenInput     = calculator.querySelector('.boligkalkulator-children-count');
        const carsInput         = calculator.querySelector('.boligkalkulator-cars-count');

        if (!annualIncomeInput || !savingsInput || !existingDebtInput) {
            return;
        }

        annualIncomeInput.addEventListener('input', () => calculateBuyingPower(calculator));
        savingsInput.addEventListener('input', () => {
            calculateBuyingPower(calculator);
            calculateFinancing(calculator);
        });
        existingDebtInput.addEventListener('input', () => calculateBuyingPower(calculator));
        childrenInput?.addEventListener('input', () => calculateBuyingPower(calculator));
        carsInput?.addEventListener('input', () => calculateBuyingPower(calculator));

        calculateBuyingPower(calculator);
    }

    function updateTotalPriceFromBuyingPower(calculator) {
        const totalPriceInput    = calculator.querySelector('.boligkalkulator-total-price');
        const buyingPowerElement = calculator.querySelector('[data-type="buying-power"]');

        if (!totalPriceInput || !buyingPowerElement) {
            return;
        }

        const buyingPowerNumber = buyingPowerElement.textContent.replace(/\D/g, '');
        if (buyingPowerNumber) {
            totalPriceInput.value = parseInt(buyingPowerNumber);
            calculateFinancing(calculator);
        }
    }

    function calculateBuyingPower(calculator) {
        const settings          = window.boligkalkulatorSettings || {};
        const incomeMultiplier  = parseFloat(settings.income_multiplier) || 5;
        const savingsMultiplier = parseFloat(settings.savings_multiplier) || 2;
        const minOwnershipShare = parseFloat(settings.min_ownership_share) || 50;

        const annualIncome      = parseFloat(calculator.querySelector('.boligkalkulator-annual-income').value) || 0;
        const savings           = parseFloat(calculator.querySelector('.boligkalkulator-savings').value) || 0;
        const existingDebt      = parseFloat(calculator.querySelector('.boligkalkulator-existing-debt').value) || 0;
        const childrenCount     = parseFloat(calculator.querySelector('.boligkalkulator-children-count').value) || 0;
        const carsCount         = parseFloat(calculator.querySelector('.boligkalkulator-cars-count').value) || 0;
        const childrenDeduction = parseFloat(settings.children_deduction) || 500000;
        const carsDeduction     = parseFloat(settings.cars_deduction) || 200000;

        const buyingPower =
            (annualIncome * incomeMultiplier) +
            (savings * savingsMultiplier) -
            existingDebt -
            (childrenCount * childrenDeduction) -
            (carsCount * carsDeduction);

        const safeBuyingPower = Math.max(0, buyingPower);

        const resultElement = calculator.querySelector('[data-type="buying-power"]');
        if (resultElement) {
            resultElement.textContent = formatCurrency(safeBuyingPower);
        }

        // Boligspleis price uses the configured minimum ownership share
        const boligSpleisPrice   = safeBuyingPower * (minOwnershipShare / 100);
        const boligSpleisElement = calculator.querySelector('[data-type="boligspleis-price"]');
        if (boligSpleisElement) {
            boligSpleisElement.textContent = formatCurrency(boligSpleisPrice);
        }

        updateTotalPriceFromBuyingPower(calculator);
    }

    /**
     * Initialize Tab 2: Finansiering av boligen
     */
    function initializeTab2(calculator) {
        const totalPriceInput = calculator.querySelector('.boligkalkulator-total-price');
        const ownershipSlider = calculator.querySelector('.boligkalkulator-ownership-slider');
        const ownershipForm   = calculator.querySelector('.boligkalkulator-ownership-form');

        updateTotalPriceFromBuyingPower(calculator);

        if (ownershipSlider && typeof boligkalkulatorSettings !== 'undefined') {
            const defaultValue = boligkalkulatorSettings.default_ownership_share || 50;
            const minValue     = boligkalkulatorSettings.min_ownership_share || 50;
            const maxValue     = boligkalkulatorSettings.max_ownership_share || 90;

            ownershipSlider.min   = minValue;
            ownershipSlider.max   = maxValue;
            ownershipSlider.value = defaultValue;

            const label = calculator.querySelector('.boligkalkulator-slider-value');
            if (label) {
                label.textContent = defaultValue + '%';
            }
        }

        if (totalPriceInput && ownershipSlider) {
            totalPriceInput.addEventListener('input', () => calculateFinancing(calculator));

            ownershipSlider.addEventListener('input', function() {
                const label = calculator.querySelector('.boligkalkulator-slider-value');
                if (label) {
                    label.textContent = this.value + '%';
                }
                calculateFinancing(calculator);
            });

            ownershipForm?.addEventListener('change', () => calculateFinancing(calculator));

            calculateFinancing(calculator);
        }
    }

    function calculateFinancing(calculator) {
        const settings            = window.boligkalkulatorSettings || {};
        const totalPrice          = parseFloat(calculator.querySelector('.boligkalkulator-total-price').value) || 0;
        const ownershipPercentage = parseFloat(calculator.querySelector('.boligkalkulator-ownership-slider').value) || 50;
        const ownershipForm       = calculator.querySelector('.boligkalkulator-ownership-form')?.value || 'borettslag';
        const savings             = parseFloat(calculator.querySelector('.boligkalkulator-savings').value) || 0;
        const minimumSavingsPct   = parseFloat(settings.minimum_savings_percentage) || 10;
        const currencySymbol      = settings.currency_symbol || 'kr';

        // Show/hide Borettslag-only elements
        calculator.querySelectorAll('.boligkalkulator-borettslag-only').forEach(el => {
            el.classList.toggle('hide', ownershipForm === 'selveier');
        });

        const yourPrice = (totalPrice * ownershipPercentage) / 100;

        // Validate minimum own capital
        let requiredOwnCapital;
        if (ownershipForm === 'borettslag') {
            const yourInnskudd = (totalPrice / 2 * ownershipPercentage) / 100;
            requiredOwnCapital = yourInnskudd * (minimumSavingsPct / 100);
        } else {
            requiredOwnCapital = yourPrice * (minimumSavingsPct / 100);
        }

        const hasValidOwnCapital = savings >= requiredOwnCapital;
        const errorElement       = calculator.querySelector('.boligkalkulator-own-capital-error');
        const ownSavingsElement  = calculator.querySelector('[data-type="own-savings"]');

        if (errorElement) {
            if (!hasValidOwnCapital) {
                errorElement.style.display = 'block';
                errorElement.textContent   = 'Du må minimum ha ' + formatCurrencyRaw(requiredOwnCapital) + ' ' + currencySymbol + ' i sparepenger.';
            } else {
                errorElement.style.display = 'none';
            }
        }

        if (ownSavingsElement) {
            ownSavingsElement.classList.toggle('invalid-capital', !hasValidOwnCapital);
        }

        let bankLoan;

        if (ownershipForm === 'borettslag') {
            const innskudd        = totalPrice / 2;
            const fellesgjeld     = totalPrice / 2;
            const yourShare       = yourPrice;
            const yourInnskudd    = (innskudd * ownershipPercentage) / 100;
            const yourFellesgjeld = yourShare * 0.5;

            const minimumSavingsRequired = yourInnskudd * (minimumSavingsPct / 100);
            const effectiveSavings       = Math.max(savings, minimumSavingsRequired);
            bankLoan = Math.max(0, yourInnskudd - effectiveSavings);

            updateElementText(calculator, 'deposit',              formatCurrency(innskudd));
            updateElementText(calculator, 'common-debt',          formatCurrency(fellesgjeld));
            updateElementText(calculator, 'your-price',           formatCurrency(yourShare));
            updateElementText(calculator, 'your-price-percent',   ownershipPercentage + '%');
            updateElementText(calculator, 'your-debt',            formatCurrency(yourFellesgjeld));
            updateElementText(calculator, 'your-debt-percent',    ownershipPercentage + '%');
            updateElementText(calculator, 'your-deposit',         formatCurrency(yourInnskudd));
            updateElementText(calculator, 'your-deposit-percent', ownershipPercentage + '%');
            updateElementText(calculator, 'own-savings',          formatCurrency(effectiveSavings));
            updateElementText(calculator, 'bank-loan',            formatCurrency(bankLoan));
            updateElementText(calculator, 'own-savings-desc',     'Minimum ' + minimumSavingsPct + '% av innskudd');

            const capitalCostItem = calculator.querySelector('.boligkalkulator-capital-cost-item');
            if (capitalCostItem) capitalCostItem.classList.add('show');
        } else {
            bankLoan = Math.max(0, yourPrice - savings);

            updateElementText(calculator, 'your-price',         formatCurrency(yourPrice));
            updateElementText(calculator, 'your-price-percent', ownershipPercentage + '%');
            updateElementText(calculator, 'own-savings',        formatCurrency(savings));
            updateElementText(calculator, 'bank-loan',          formatCurrency(bankLoan));
            updateElementText(calculator, 'own-savings-desc',   'Minimum ' + minimumSavingsPct + '% av kjøpesum');

            const capitalCostItem = calculator.querySelector('.boligkalkulator-capital-cost-item');
            if (capitalCostItem) capitalCostItem.classList.remove('show');
        }

        calculator.dataset.bankLoanAmount = bankLoan;

        calculateMonthlyExpenses(calculator);
    }

    /**
     * Initialize Tab 3: Budsjett
     */
    function initializeTab3(calculator) {
        calculator.querySelectorAll('.boligkalkulator-help-icon').forEach(icon => {
            const helpText = icon.getAttribute('data-content');
            if (helpText) {
                const tooltip       = document.createElement('span');
                tooltip.className   = 'boligkalkulator-tooltip';
                tooltip.textContent = helpText;
                icon.appendChild(tooltip);
            }
        });
    }

    /**
     * Monthly payment: (principal × annual_rate + principal / years) / 12
     * Matches the Excel calculation model.
     */
    function calculateMonthlyAnnuity(principal, annualRatePercent, years) {
        if (principal <= 0 || years <= 0) return 0;
        return (principal * (annualRatePercent / 100) + principal / years) / 12;
    }

    function calculateMonthlyExpenses(calculator) {
        const settings            = window.boligkalkulatorSettings || {};
        const totalPrice          = parseFloat(calculator.querySelector('.boligkalkulator-total-price').value) || 0;
        const ownershipPercentage = parseFloat(calculator.querySelector('.boligkalkulator-ownership-slider').value) || 50;
        const ownershipForm       = calculator.querySelector('.boligkalkulator-ownership-form')?.value || 'borettslag';

        const osloboligRentPct    = parseFloat(settings.oslobolig_rent_percentage) || 4.5;
        const estimatedPricePerKvm = parseFloat(settings.estimated_price_per_kvm) || 120000;
        const driftCostPerKvm     = parseFloat(settings.drift_cost_per_kvm) || 50;
        const broadbandCost       = parseFloat(settings.broadband_cost) || 400;
        const bankInterestPct     = parseFloat(settings.bank_interest_percentage) || 5;
        const loanYears           = parseFloat(settings.loan_repayment_years) || 30;
        const safeLoanYears       = (!isNaN(loanYears) && loanYears > 0) ? loanYears : 30;

        const yourPrice = (totalPrice * ownershipPercentage) / 100;

        // Monthly rent to OsloBolig: annual percentage of your share / 12
        const monthlyRent = yourPrice * (osloboligRentPct / 100) / 12;

        // Drift costs: estimated sqm * drift rate * ownership share + broadband
        const monthlyDriftCosts = estimatedPricePerKvm > 0
            ? ((totalPrice / estimatedPricePerKvm) * driftCostPerKvm * (ownershipPercentage / 100)) + broadbandCost
            : broadbandCost;

        // Bank loan stored by calculateFinancing; recalculate if missing
        let bankLoanAmount = parseFloat(calculator.dataset.bankLoanAmount);
        if (isNaN(bankLoanAmount)) {
            const savings = parseFloat(calculator.querySelector('.boligkalkulator-savings')?.value) || 0;
            if (ownershipForm === 'borettslag') {
                const yourInnskudd = (totalPrice / 2 * ownershipPercentage) / 100;
                bankLoanAmount = Math.max(0, yourInnskudd - savings);
            } else {
                bankLoanAmount = Math.max(0, yourPrice - savings);
            }
        }

        const monthlyBankPayment = calculateMonthlyAnnuity(bankLoanAmount, bankInterestPct, safeLoanYears);

        if (ownershipForm === 'borettslag') {
            const borettslagCapitalPct = parseFloat(settings.borettslag_capital_cost) || 5;
            const yourCommonDebt       = (totalPrice / 2 * ownershipPercentage) / 100;
            const monthlyCapitalCost   = yourCommonDebt * (borettslagCapitalPct / 100) / 12;

            const totalMonthly = monthlyRent + monthlyDriftCosts + monthlyCapitalCost + monthlyBankPayment;

            updateElementText(calculator, 'oslobolig-rent', formatCurrency(monthlyRent));
            updateElementText(calculator, 'common-costs',   formatCurrency(monthlyDriftCosts));
            updateElementText(calculator, 'capital-cost',   formatCurrency(monthlyCapitalCost));
            updateElementText(calculator, 'bank-interest',  formatCurrency(monthlyBankPayment));
            updateElementText(calculator, 'total-monthly',  formatCurrency(totalMonthly));
        } else {
            const totalMonthly = monthlyRent + monthlyDriftCosts + monthlyBankPayment;

            updateElementText(calculator, 'oslobolig-rent', formatCurrency(monthlyRent));
            updateElementText(calculator, 'common-costs',   formatCurrency(monthlyDriftCosts));
            updateElementText(calculator, 'capital-cost',   formatCurrency(0));
            updateElementText(calculator, 'bank-interest',  formatCurrency(monthlyBankPayment));
            updateElementText(calculator, 'total-monthly',  formatCurrency(totalMonthly));

            const capitalCostItem = calculator.querySelector('.boligkalkulator-capital-cost-item');
            if (capitalCostItem) capitalCostItem.classList.remove('show');
        }

        // Comparison: 100% ownership with 90% bank financing
        const borettslagCapitalPct = parseFloat(settings.borettslag_capital_cost) || 5;
        const compDriftCosts       = estimatedPricePerKvm > 0
            ? (totalPrice / estimatedPricePerKvm * driftCostPerKvm) + broadbandCost
            : broadbandCost;

        let compCapitalCost        = 0;
        let compMonthlyBankPayment = 0;

        if (ownershipForm === 'borettslag') {
            compCapitalCost        = (totalPrice / 2) * (borettslagCapitalPct / 100) / 12;
            compMonthlyBankPayment = calculateMonthlyAnnuity((totalPrice / 2) * 0.9, bankInterestPct, safeLoanYears);
        } else {
            compMonthlyBankPayment = calculateMonthlyAnnuity(totalPrice * 0.9, bankInterestPct, safeLoanYears);
        }

        const compTotal = compDriftCosts + compCapitalCost + compMonthlyBankPayment;

        updateElementText(calculator, 'comparison-oslobolig', formatCurrency(0));
        updateElementText(calculator, 'comparison-drift',     formatCurrency(compDriftCosts));
        updateElementText(calculator, 'comparison-capital',   formatCurrency(compCapitalCost));
        updateElementText(calculator, 'comparison-renter',    formatCurrency(compMonthlyBankPayment));
        updateElementText(calculator, 'comparison-total',     formatCurrency(compTotal));
    }

    function updateElementText(calculator, type, value) {
        calculator.querySelectorAll(`[data-type="${type}"]`).forEach(el => el.textContent = value);
    }

    /** Returns number formatted with thousands separator only (no symbol) */
    function formatCurrencyRaw(value) {
        const settings     = window.boligkalkulatorSettings || {};
        const thousandsSep = (settings.thousands_separator !== undefined && settings.thousands_separator !== '')
            ? settings.thousands_separator : ' ';
        return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
    }

    function formatCurrency(value) {
        const settings       = window.boligkalkulatorSettings || {};
        const currencySymbol = settings.currency_symbol || 'kr';
        return formatCurrencyRaw(value) + ' ' + currencySymbol;
    }

    function initializeSearchButton(calculator) {
        const searchButton = calculator.querySelector('.boligkalkulator-btn-search');
        if (!searchButton || typeof boligkalkulatorSettings === 'undefined') {
            return;
        }

        const searchUrl = boligkalkulatorSettings.search_button_url;
        const newTab    = boligkalkulatorSettings.search_button_new_tab;

        if (searchUrl) {
            searchButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.open(searchUrl, newTab ? '_blank' : '_self');
            });
        }
    }

})(jQuery);
