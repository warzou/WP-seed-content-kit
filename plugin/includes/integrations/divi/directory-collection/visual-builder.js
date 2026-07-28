(function (window) {
    'use strict';

    var data = window.WpSeedContentKitDiviDirectoryCollectionData || {};
    var React = window.vendor && window.vendor.React;
    var hooks = window.vendor && window.vendor.wp && window.vendor.wp.hooks;
    var divi = window.divi || {};
    var moduleApi = divi.module || {};
    var moduleLibrary = divi.moduleLibrary || {};
    var restApi = divi.rest || {};

    if (!React || !hooks || !moduleApi.ModuleContainer || !moduleLibrary.registerModule || !restApi.useFetch || !data.metadata) {
        return;
    }

    var metadata = JSON.parse(JSON.stringify(data.metadata));
    var collectionItems = metadata.attributes.collection.settings.innerContent.items;
    if (data.canManageTemplates) {
        collectionItems.template.component.props.options = data.templates || {
            native: {label: 'Rendu natif'}
        };
    } else {
        delete collectionItems.template;
    }

    var createElement = React.createElement;
    var Fragment = React.Fragment;
    var ModuleContainer = moduleApi.ModuleContainer;
    var StyleContainer = moduleApi.StyleContainer;
    var elementClassnames = moduleApi.elementClassnames;
    var useFetch = restApi.useFetch;

    function collectionValue(attrs, key, fallback) {
        var collection = attrs && attrs.collection;
        var innerContent = collection && collection.innerContent;
        var desktop = innerContent && innerContent.desktop;
        var values = desktop && desktop.value;

        return values && typeof values[key] !== 'undefined' ? values[key] : fallback;
    }

    function buildRoute(attrs) {
        var defaults = {
            status: 'all',
            profile_types: 'all',
            profile_type_operator: 'or',
            seeking_models: 'all',
            department: '',
            country: '',
            featured: 'all',
            ids: '',
            exclude_ids: '',
            limit: '0',
            offset: '0',
            orderby: 'display_order',
            order: 'asc',
            template: 'native'
        };
        var values = {};
        Object.keys(defaults).forEach(function (key) {
            values[key] = collectionValue(attrs, key, defaults[key]);
        });
        if (values.profile_types === 'all') {
            values.profile_types = '';
        }
        values.template_slug = values.template === 'native' ? '' : values.template;
        delete values.template;

        return (data.restRoute || '/wp-seed-content-kit/v1/divi/directory-preview')
            + '?'
            + Object.keys(values).map(function (key) {
                return encodeURIComponent(key) + '=' + encodeURIComponent(values[key]);
            }).join('&');
    }

    function ModuleStyles(props) {
        return createElement(
            StyleContainer,
            {mode: props.mode, state: props.state, noStyleTag: props.noStyleTag},
            props.elements.style({
                attrName: 'module',
                styleProps: {
                    disabledOn: {
                        disabledModuleVisibility: props.settings && props.settings.disabledModuleVisibility
                    }
                }
            }),
            props.elements.style({attrName: 'title'})
        );
    }

    function moduleClassnames(args) {
        var attrs = args.attrs || {};
        var decoration = attrs.module && attrs.module.decoration ? attrs.module.decoration : {};
        args.classnamesInstance.add(elementClassnames({attrs: decoration}));
    }

    function ModuleScriptData(props) {
        return createElement(
            Fragment,
            null,
            props.elements.scriptData({attrName: 'module'})
        );
    }

    function DirectoryCollectionEdit(props) {
        var request = useFetch({html: ''});
        var errorState = React.useState(false);
        var hasError = errorState[0];
        var setHasError = errorState[1];
        var abortRef = React.useRef();
        var route = buildRoute(props.attrs);

        React.useEffect(function () {
            if (abortRef.current) {
                abortRef.current.abort();
            }
            abortRef.current = new AbortController();
            setHasError(false);
            request.fetch({
                restRoute: route,
                method: 'GET',
                signal: abortRef.current.signal
            }).catch(function (error) {
                if (!error || error.name !== 'AbortError') {
                    setHasError(true);
                }
            });

            return function () {
                if (abortRef.current) {
                    abortRef.current.abort();
                }
            };
        }, [route]);

        var response = request.response || {};
        var preview;
        if (request.isLoading) {
            preview = createElement(
                'p',
                {className: 'wp-seed-divi-directory-collection__status', 'aria-live': 'polite'},
                data.labels.loading
            );
        } else if (hasError) {
            preview = createElement(
                'p',
                {className: 'wp-seed-divi-directory-collection__status', role: 'alert'},
                data.labels.error
            );
        } else {
            preview = createElement('div', {
                className: 'wp-seed-divi-directory-collection__preview',
                dangerouslySetInnerHTML: {__html: response.html || ''}
            });
        }

        return createElement(
            ModuleContainer,
            {
                attrs: props.attrs,
                elements: props.elements,
                id: props.id,
                name: props.name,
                moduleClassName: 'wp_seed_content_kit_directory_collection',
                stylesComponent: ModuleStyles,
                classnamesFunction: moduleClassnames,
                scriptDataComponent: ModuleScriptData
            },
            props.elements.styleComponents({attrName: 'module'}),
            createElement(
                'div',
                {className: 'wp-seed-divi-directory-collection__inner'},
                props.elements.render({attrName: 'title'}),
                preview
            )
        );
    }

    var moduleRegistered = false;
    function registerDirectoryCollectionModule() {
        if (!moduleRegistered) {
            moduleRegistered = true;
            moduleLibrary.registerModule(metadata, {
                renderers: {edit: DirectoryCollectionEdit}
            });
        }
    }

    var readyHook = 'divi.moduleLibrary.registerModuleLibraryStore.after';
    hooks.addAction(readyHook, 'wpSeedContentKit.directoryCollection', registerDirectoryCollectionModule);
    if (typeof hooks.didAction === 'function' && hooks.didAction(readyHook) > 0) {
        registerDirectoryCollectionModule();
    }
}(window));
