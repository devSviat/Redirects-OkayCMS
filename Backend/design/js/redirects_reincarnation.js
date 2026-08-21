(function () {
    'use strict';

    function showError(message) {
        if (window.toastr && typeof window.toastr.error === 'function') {
            window.toastr.error(message);
            return;
        }
        window.alert(message);
    }

    function initReincarnation() {
        var form = document.querySelector('.fn_sviat_redirects_reincarnation_form');
        if (!form) {
            return;
        }

        var button = form.querySelector('.fn_sviat_redirects_reincarnation_button');
        var label = form.querySelector('.fn_sviat_redirects_reincarnation_label');
        var count = form.querySelector('.fn_sviat_redirects_reincarnation_count');
        if (!button || !label || !count) {
            return;
        }

        var defaultLabel = label.textContent;
        var requestInProgress = false;

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (requestInProgress) {
                return;
            }

            requestInProgress = true;
            button.disabled = true;
            button.classList.add('is-loading');
            label.textContent = button.getAttribute('data-running-text') || defaultLabel;

            fetch(form.getAttribute('action') || window.location.href, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text();
                })
                .then(function (responseText) {
                    var report;
                    try {
                        report = JSON.parse(responseText);
                    } catch (error) {
                        throw new Error('Invalid JSON response');
                    }

                    if (report.status === 'busy') {
                        throw new Error(button.getAttribute('data-busy-text') || 'Scan is already running');
                    }

                    count.textContent = String(parseInt(report.found, 10) || 0);
                    window.location.reload();
                })
                .catch(function (error) {
                    requestInProgress = false;
                    button.disabled = false;
                    button.classList.remove('is-loading');
                    label.textContent = defaultLabel;

                    var genericMessage = button.getAttribute('data-error-text') || 'Scan failed';
                    showError(error && error.message ? genericMessage + ': ' + error.message : genericMessage);
                });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReincarnation);
    } else {
        initReincarnation();
    }
}());
