(function (window) {
    'use strict';

    var data = window.WpSeedContentKitDiviTestimonialCollectionData || {};
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
        var values = {
            ids: collectionValue(attrs, 'ids', ''),
            featured: collectionValue(attrs, 'featured', 'all'),
            context: collectionValue(attrs, 'context', ''),
            limit: collectionValue(attrs, 'limit', '3'),
            orderby: collectionValue(attrs, 'orderby', 'date'),
            order: collectionValue(attrs, 'order', 'desc'),
            template_slug: collectionValue(attrs, 'template', 'native') === 'native'
                ? ''
                : collectionValue(attrs, 'template', 'native'),
            columns: collectionValue(attrs, 'columns', '3')
        };
        var query = Object.keys(values).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(values[key]);
        }).join('&');

        return (data.restRoute || '/wp-seed-content-kit/v1/divi/testimonials-preview') + '?' + query;
    }

    function ModuleStyles(props) {
        return createElement(
            StyleContainer,
            {
                mode: props.mode,
                state: props.state,
                noStyleTag: props.noStyleTag
            },
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

    function TestimonialCollectionEdit(props) {
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
            preview = createElement('p', {className: 'wp-seed-divi-testimonial-collection__status', 'aria-live': 'polite'}, data.labels.loading);
        } else if (hasError) {
            preview = createElement('p', {className: 'wp-seed-divi-testimonial-collection__status', role: 'alert'}, data.labels.error);
        } else {
            preview = createElement('div', {
                className: 'wp-seed-divi-testimonial-collection__preview',
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
                moduleClassName: 'wp_seed_content_kit_testimonial_collection',
                stylesComponent: ModuleStyles,
                classnamesFunction: moduleClassnames,
                scriptDataComponent: ModuleScriptData
            },
            props.elements.styleComponents({attrName: 'module'}),
            createElement(
                'div',
                {className: 'wp-seed-divi-testimonial-collection__inner'},
                props.elements.render({attrName: 'title'}),
                preview
            )
        );
    }

    var moduleRegistered = false;
    function registerTestimonialCollectionModule() {
        if (moduleRegistered) {
            return;
        }

        moduleRegistered = true;
        moduleLibrary.registerModule(metadata, {
            renderers: {edit: TestimonialCollectionEdit}
        });
    }

    var moduleLibraryReadyHook = 'divi.moduleLibrary.registerModuleLibraryStore.after';
    hooks.addAction(
        moduleLibraryReadyHook,
        'wpSeedContentKit.testimonialCollection',
        registerTestimonialCollectionModule
    );

    if (typeof hooks.didAction === 'function' && hooks.didAction(moduleLibraryReadyHook) > 0) {
        registerTestimonialCollectionModule();
    }
}(window));
