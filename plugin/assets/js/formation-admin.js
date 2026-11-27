(function () {
    'use strict';

    function updateOrders(container) {
        var rows = container.querySelectorAll('.wpsck-formation-section-row');
        Array.prototype.forEach.call(rows, function (row, index) {
            var order = row.querySelector('.wpsck-formation-section-order');
            if (order) order.value = (index + 1) * 10;
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('button, a');
        if (!button) return;
        if (button.classList.contains('wpsck-formation-person-up') || button.classList.contains('wpsck-formation-person-down')) {
            var personRow = button.closest('.wpsck-formation-person');
            var sibling = button.classList.contains('wpsck-formation-person-up') ? personRow.previousElementSibling : personRow.nextElementSibling;
            if (sibling) personRow.parentNode.insertBefore(button.classList.contains('wpsck-formation-person-up') ? personRow : sibling, button.classList.contains('wpsck-formation-person-up') ? sibling : personRow);
            button.focus();
        }
        if (button.classList.contains('wpsck-formation-section-add')) {
            var container = button.closest('.wpsck-formation-sections');
            var template = container.querySelector('.wpsck-formation-section-template');
            container.querySelector('.wpsck-formation-sections__list').appendChild(template.content.cloneNode(true));
            updateOrders(container);
            container.querySelector('.wpsck-formation-section-row:last-child .wpsck-formation-section-name').focus();
        }
        if (button.classList.contains('wpsck-formation-section-up') || button.classList.contains('wpsck-formation-section-down')) {
            var row = button.closest('.wpsck-formation-section-row');
            var list = row.parentNode;
            if (button.classList.contains('wpsck-formation-section-up') && row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
            if (button.classList.contains('wpsck-formation-section-down') && row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
            updateOrders(button.closest('.wpsck-formation-sections'));
            button.focus();
        }
        if (button.classList.contains('wpsck-formation-section-remove') && !button.disabled) {
            var removedRow = button.closest('.wpsck-formation-section-row');
            var id = removedRow.querySelector('input[name="wpsck_formation_sections[id][]"]').value;
            var sections = button.closest('.wpsck-formation-sections');
            if (id) {
                var removed = document.createElement('input');
                removed.type = 'hidden';
                removed.name = 'wpsck_formation_removed_sections[]';
                removed.value = id;
                sections.appendChild(removed);
            }
            removedRow.remove();
            updateOrders(sections);
        }
        if (button.classList.contains('wpsck-formation-confirm-delete')) {
            var message = window.wpsckFormationAdmin && window.wpsckFormationAdmin.confirmDelete ? window.wpsckFormationAdmin.confirmDelete : 'Supprimer cette programmation ?';
            if (!window.confirm(message)) event.preventDefault();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.id === 'wpsck-program-filter') {
            var option = event.target.options[event.target.selectedIndex];
            var year = document.getElementById('wpsck-year-filter');
            if (year && option.dataset.calendarYear) year.value = option.dataset.calendarYear;
        }
        if (event.target.matches('.wpsck-formation-person input[type="checkbox"]')) {
            event.target.closest('.wpsck-formation-person').querySelector('.wpsck-formation-person-actions').hidden = !event.target.checked;
        }
    });
}());
