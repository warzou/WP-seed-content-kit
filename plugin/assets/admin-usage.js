(function () {
    'use strict';

    function copyValue(button) {
        var targetId = button.getAttribute('data-seed-usage-copy');
        var target = targetId ? document.getElementById(targetId) : null;
        var status = button.parentNode ? button.parentNode.querySelector('.seed-usage-copy__status') : null;

        if (!target) {
            return;
        }

        target.select();
        target.setSelectionRange(0, target.value.length);

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(target.value);
        } else {
            document.execCommand('copy');
        }

        if (status) {
            status.textContent = button.getAttribute('data-copy-label') || '';
        }
    }

    function updateGenerator(generator) {
        var shortcode = generator.getAttribute('data-shortcode');
        var output = generator.querySelector('[data-seed-usage-output]');
        var fields = generator.querySelectorAll('[data-seed-usage-attr]');
        var parts = [shortcode];

        if (!shortcode || !output) {
            return;
        }

        fields.forEach(function (field) {
            var attr = field.getAttribute('data-seed-usage-attr');
            var defaultValue = field.getAttribute('data-seed-usage-default');
            var value = field.type === 'checkbox' ? (field.checked ? field.value : '') : String(field.value).trim();

            if (!attr || !value || value === defaultValue) {
                return;
            }

            value = value.replace(/"/g, "'").replace(/[\[\]]/g, '');
            parts.push(attr + '="' + value + '"');
        });

        output.value = '[' + parts.join(' ') + ']';
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-seed-usage-copy]');
        if (button) {
            copyValue(button);
        }
    });

    document.querySelectorAll('[data-seed-usage-generator]').forEach(function (generator) {
        generator.addEventListener('input', function () {
            updateGenerator(generator);
        });
        generator.addEventListener('change', function () {
            updateGenerator(generator);
        });
        updateGenerator(generator);
    });
}());