(function () {
    'use strict';

    function copyValue(button) {
        var targetId = button.getAttribute('data-seed-usage-copy');
        var target = targetId ? document.getElementById(targetId) : null;
        var directValue = button.getAttribute('data-seed-usage-copy-value');
        var status = button.parentNode ? button.parentNode.querySelector('.seed-usage-copy__status') : null;
        var value = target ? target.value : directValue;

        if (value === null || typeof value === 'undefined') {
            return;
        }

        if (target) {
            target.select();
            target.setSelectionRange(0, target.value.length);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value);
        } else if (target) {
            document.execCommand('copy');
        } else {
            var temporary = document.createElement('textarea');
            temporary.value = value;
            temporary.setAttribute('readonly', 'readonly');
            temporary.style.position = 'fixed';
            temporary.style.opacity = '0';
            document.body.appendChild(temporary);
            temporary.select();
            document.execCommand('copy');
            document.body.removeChild(temporary);
        }

        if (status) {
            status.textContent = button.getAttribute('data-copy-label') || '';
        }
    }

    function updateGenerator(generator) {
        var shortcode = generator.getAttribute('data-shortcode');
        var output = generator.querySelector('[data-seed-usage-output]');
        var summary = generator.querySelector('[data-seed-usage-summary]');
        var fields = generator.querySelectorAll('[data-seed-usage-attr]');
        var parts = [shortcode];
        var descriptions = [];

        if (!shortcode || !output) {
            return;
        }

        fields.forEach(function (field) {
            var attr = field.getAttribute('data-seed-usage-attr');
            var label = field.getAttribute('data-seed-usage-label') || attr;
            var defaultValue = field.getAttribute('data-seed-usage-default');
            var value = field.type === 'checkbox' ? (field.checked ? field.value : '') : String(field.value).trim();
            var humanValue = value;

            if (!attr || !value || value === defaultValue) {
                return;
            }

            if (field.tagName === 'SELECT' && field.selectedIndex >= 0) {
                humanValue = field.options[field.selectedIndex].text;
            }

            value = value.replace(/"/g, "'").replace(/[\[\]]/g, '');
            parts.push(attr + '="' + value + '"');
            descriptions.push(label + ' : ' + humanValue);
        });

        output.value = '[' + parts.join(' ') + ']';
        if (summary) {
            summary.textContent = descriptions.length
                ? 'Sélection : ' + descriptions.join(', ') + '.'
                : 'Toutes les fiches publiques, ordre manuel puis nom croissant, avec le rendu natif.';
        }
    }

    function handleTabKeys(event) {
        var tab = event.target.closest('[role="tab"]');
        var tablist = tab ? tab.parentNode : null;
        var tabs;
        var index;
        var next;

        if (!tab || !tablist || tablist.getAttribute('role') !== 'tablist'
            || ['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(event.key) === -1) {
            return;
        }

        tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));
        index = tabs.indexOf(tab);
        if (event.key === 'Home') {
            next = tabs[0];
        } else if (event.key === 'End') {
            next = tabs[tabs.length - 1];
        } else if (event.key === 'ArrowLeft') {
            next = tabs[(index - 1 + tabs.length) % tabs.length];
        } else {
            next = tabs[(index + 1) % tabs.length];
        }

        event.preventDefault();
        next.focus();
        window.location.href = next.href;
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-seed-usage-copy], [data-seed-usage-copy-value]');
        if (button) {
            copyValue(button);
        }
    });
    document.addEventListener('keydown', handleTabKeys);

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
