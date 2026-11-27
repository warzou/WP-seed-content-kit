(function (window, document) {
    'use strict';

    var editorId = 'wp_seed_content_testimonial_text';
    var allowedButtons = ['strong', 'em', 'link', 'block', 'ul', 'ol', 'li', 'more', 'close'];
    var observer = null;

    function isAllowed(button) {
        return allowedButtons.some(function (name) {
            return button.id === 'qt_' + editorId + '_' + name
                || button.id.slice(-(name.length + 1)) === '_' + name;
        });
    }

    function cleanToolbar() {
        var toolbar = document.getElementById('qt_' + editorId + '_toolbar');
        var buttons;

        if (!toolbar) {
            return;
        }

        buttons = toolbar.querySelectorAll('input.ed_button, button.ed_button');
        Array.prototype.forEach.call(buttons, function (button) {
            if (!isAllowed(button)) {
                button.remove();
            }
        });

        if (!observer && window.MutationObserver) {
            observer = new window.MutationObserver(cleanToolbar);
            observer.observe(toolbar, { childList: true });
        }
    }

    document.addEventListener('DOMContentLoaded', cleanToolbar);
    if (window.jQuery) {
        window.jQuery(document).on('quicktags-init', function (event, quicktags) {
            if (quicktags && quicktags.id === editorId) {
                cleanToolbar();
            }
        });
    }
}(window, document));
