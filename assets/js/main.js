;(($) => {
    $(document).ready(() => {
        if (window.CryptoPayModal) {
            $(document).on('click', '#mb-cryptopay-start', CryptoPayModal.open);
        }

        if (window.CryptoPayLiteModal) {
            $(document).on('click', '#mb-cryptopay-lite-start', CryptoPayLiteModal.open);
        }
    });
})(jQuery);