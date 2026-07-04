import '../scss/admin.scss';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';

$(function() {
    // Datetime picker initialization.
    // See https://flatpickr.js.org/
    document.querySelectorAll('input[data-toggle="flatpickr"]').forEach(function (input) {
        var fp = flatpickr(input, {
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: input.dataset.dateFormat,
        });

        var icon = input.closest('.input-group').querySelector('.input-group-addon');
        if (icon) {
            icon.addEventListener('click', function () { fp.open(); });
        }

        // Best-effort locale loading: not every app locale has a matching
        // flatpickr l10n bundle, so silently keep the English default on failure.
        // webpackInclude narrows the lazy-loaded chunk set to just the locales
        // this app supports (see app_locales in config/services.yaml), instead
        // of bundling all ~70 locales flatpickr ships as separate chunks.
        var lang = input.dataset.dateLocale ? input.dataset.dateLocale.split('-')[0] : null;
        if (lang && lang !== 'en') {
            import(
                /* webpackInclude: /(bg|ca|cs|de|es|fr|hr|id|it|ja|lt|nl|pl|pt|ro|ru|sl|tr|uk|zh)\.js$/ */
                `flatpickr/dist/l10n/${lang}.js`
            ).then(function (m) {
                fp.set('locale', m.default[lang] || m.default);
            }).catch(function () { /* no matching locale bundle; keep English default */ });
        }
    });

    // Tagify initialization
    // See https://github.com/yaireo/tagify
    var tagsInput = document.querySelector('input[data-toggle="tagify"]');
    if (tagsInput) {
        new Tagify(tagsInput, {
            whitelist: JSON.parse(tagsInput.dataset.tags || '[]'),
            dropdown: { enabled: 0 }, // show suggestions immediately on focus
            originalInputValueFormat: values => values.map(item => item.value).join(','),
        });
    }
});

// Handling the modal confirmation message.
$(document).on('submit', 'form[data-confirmation]', function (event) {
    var $form = $(this),
        $confirm = $('#confirmationModal');

    if ($confirm.data('result') !== 'yes') {
        //cancel submit event
        event.preventDefault();

        $confirm
            .off('click', '#btnYes')
            .on('click', '#btnYes', function () {
                $confirm.data('result', 'yes');
                $form.find('input[type="submit"]').attr('disabled', 'disabled');
                $form.submit();
            })
            .modal('show');
    }
});
