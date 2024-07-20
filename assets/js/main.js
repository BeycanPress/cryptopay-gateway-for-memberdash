;(($) => {
    $(document).ready(() => {
        if (window.CryptoPayApp) {
            $(document).on('click', '#mb-cryptopay-start', CryptoPayApp.modal.open);
        }

        if (window.CryptoPayLiteApp) {
            $(document).on('click', '#mb-cryptopay-lite-start', CryptoPayLiteApp.modal.open);
        }
    });
})(jQuery);