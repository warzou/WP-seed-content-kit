(function (window) {
    'use strict';
    var hooks = window.vendor && window.vendor.wp && window.vendor.wp.hooks;
    var React = window.vendor && window.vendor.React;
    var data = window.WpsckCalendarFiltersData;
    if (!hooks || !React || !data) return;
    var targets = ['divi/section', 'divi/row', 'divi/column', 'divi/group'];
    var defaults = {enabled: 'off', program: '', year: '', section: '', theme: '', status: '', sort: 'date_asc'};

    function CalendarFilters(props) {
        var value = Object.assign({}, defaults, props.value || {});
        function update(key, next) {
            var changed = Object.assign({}, value);
            changed[key] = next;
            if (key === 'program') changed.section = '';
            props.onChange(changed);
        }
        function select(key, label, options) {
            return React.createElement('label', {key: key, style: {display: 'block', marginBottom: '12px'}},
                React.createElement('span', {style: {display: 'block', marginBottom: '6px'}}, label),
                React.createElement(window.divi.fieldLibrary.Select, Object.assign({}, props, {
                    name: 'wpsck-calendar-' + key, value: value[key] || '0', defaultValue: '0', options: options,
                    onChange: function (next) { update(key, next.inputValue === '0' ? '' : next.inputValue); }
                })));
        }
        function options(items, empty) {
            var result = {'0': {label: empty}};
            items.forEach(function (item) { result[item.id] = {label: item.label}; });
            return result;
        }
        var controls = [React.createElement('label', {key: 'enabled', style: {display: 'block', marginBottom: '12px'}},
            React.createElement('input', {type: 'checkbox', checked: value.enabled === 'on', onChange: function (event) { update('enabled', event.target.checked ? 'on' : 'off'); }}),
            ' Utiliser les filtres Calendrier')];
        if (value.enabled !== 'on') return React.createElement('div', null, controls);
        controls.push(select('program', 'Programme', options(data.program, 'Choisir un programme')));
        controls.push(select('year', 'Ann\u00e9e', options(Object.keys(data.year).map(function (year) { return {id: year, label: year}; }), 'Toutes les ann\u00e9es')));
        controls.push(select('section', 'Section', options(data.section.filter(function (item) { return item.program === value.program; }), 'Toutes les sections')));
        controls.push(select('theme', 'Th\u00e8me', options(data.theme, 'Tous les th\u00e8mes')));
        controls.push(select('status', 'Statut', options(Object.keys(data.status).map(function (id) { return {id: id, label: data.status[id]}; }), 'Tous')));
        controls.push(select('sort', 'Tri', {date_asc: {label: 'Date croissante'}, date_desc: {label: 'Date d\u00e9croissante'}, display_order: {label: 'Ordre'}}));
        return React.createElement('div', null, controls);
    }
    CalendarFilters.fieldName = 'wpsck/calendar-filters';
    hooks.addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'wpsck/calendar-filters', function () {
        window.divi.fieldLibrary.registerFieldComponent({name: CalendarFilters.fieldName, component: CalendarFilters});
    }, 5);
    hooks.addFilter('divi.moduleLibrary.moduleSettings.groups', 'wpsck/calendar-filters', function (groups, metadata) {
        if (targets.indexOf(metadata.name) === -1) return groups;
        groups.wpsckCalendar = {groupName: 'wpsckCalendar', panel: 'content', priority: 96,
            component: {name: 'divi/composite', props: {groupLabel: 'Calendrier WPSCK'}}};
        return groups;
    });
    hooks.addFilter('divi.moduleLibrary.moduleAttributes', 'wpsck/calendar-filters', function (attributes, metadata) {
        if (targets.indexOf(metadata.name) === -1) return attributes;
        attributes.wpsckCalendarFilters = {type: 'object', default: {innerContent: {desktop: {value: defaults}}},
            settings: {innerContent: {groupType: 'group-item', item: {
                attrName: 'wpsckCalendarFilters.innerContent', label: 'Filtres', groupSlug: 'wpsckCalendar', priority: 1, render: true,
                features: {hover: false, sticky: false, responsive: false, dynamicContent: false, preset: 'content'},
                component: {name: CalendarFilters.fieldName, type: 'field'}
            }}}};
        return attributes;
    });
    hooks.addFilter('divi.module.layout.childModule.loop.resultsQueryParams', 'wpsck/calendar-filters', function (params, attrs, moduleId, queryType) {
        var loop = attrs.module && attrs.module.advanced && attrs.module.advanced.loop && attrs.module.advanced.loop.desktop && attrs.module.advanced.loop.desktop.value;
        var field = attrs.wpsckCalendarFilters && attrs.wpsckCalendarFilters.innerContent && attrs.wpsckCalendarFilters.innerContent.desktop;
        if (queryType === 'post_types' && loop && loop.subTypes && loop.subTypes.length === 1 && loop.subTypes[0].value === 'seed_schedule' && field && field.value && field.value.enabled === 'on') {
            params.set('wpsck_calendar_filters', JSON.stringify(field.value));
        }
        return params;
    });
}(window));
