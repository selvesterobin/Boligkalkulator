/**
 * Admin Settings JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initializeShortcodeCopy();
    });

    function initializeShortcodeCopy() {
        const copyBtn        = document.querySelector('#boligkalkulator-copy-shortcode');
        const shortcodeInput = document.querySelector('#boligkalkulator-shortcode');

        if (!copyBtn || !shortcodeInput) return;

        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(shortcodeInput.value).then(() => {
                flashCopied(copyBtn);
            });
        });
    }

    function flashCopied(btn) {
        const originalText = btn.textContent;
        btn.textContent    = 'Kopiert!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('copied');
        }, 2000);
    }

})(jQuery);
