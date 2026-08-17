'use strict';

var fs = require('fs');
var vm = require('vm');
var source = fs.readFileSync('plugin/assets/js/directory-editor-validation.js', 'utf8');
var assertions = 0;

function assert(condition, message) {
    assertions += 1;
    if (!condition) {
        throw new Error(message);
    }
}

async function runScenario(options) {
    var hookValues = [];
    var hookDeps = [];
    var hookCursor = 0;
    var pendingEffects = [];
    var registeredPlugin = null;
    var filters = {};
    var actions = {};
    var saveCalls = 0;
    var latestNotice = null;
    var editorState = {
        id: 900001,
        validation: { state: 'blocked', summary: 'Blocked', messages: ['Site internet invalide'] },
        saving: false,
        autosaving: false,
        editedStatus: options.editedStatus || 'draft'
    };
    var serverRecord = {
        id: 900001,
        status: 'draft',
        wpsck_directory_validation: options.serverValidation || { state: 'none', summary: '', messages: [] }
    };

    function sameDeps(left, right) {
        return left && right && left.length === right.length && left.every(function (value, index) {
            return value === right[index];
        });
    }

    function useRef(initial) {
        var index = hookCursor++;
        if (!hookValues[index]) {
            hookValues[index] = { current: initial };
        }
        return hookValues[index];
    }

    function useState(initial) {
        var index = hookCursor++;
        if (!Object.prototype.hasOwnProperty.call(hookValues, index)) {
            hookValues[index] = initial;
        }
        return [hookValues[index], function (value) { hookValues[index] = value; }];
    }

    function useEffect(callback, dependencies) {
        var index = hookCursor++;
        if (!sameDeps(hookDeps[index], dependencies)) {
            hookDeps[index] = dependencies.slice();
            pendingEffects.push(callback);
        }
    }

    function createElement(type, properties) {
        var children = Array.prototype.slice.call(arguments, 2);
        if ('function' === typeof type) {
            return type(Object.assign({}, properties || {}, { children: children }));
        }
        return { type: type, properties: properties || {}, children: children };
    }

    var wp = {
        apiFetch: function () { return Promise.resolve(serverRecord); },
        components: { Notice: 'Notice' },
        data: {
            select: function () {
                return {
                    getCurrentPost: function () {
                        return { id: editorState.id, wpsck_directory_validation: editorState.validation };
                    },
                    getEditedPostAttribute: function () { return editorState.editedStatus; }
                };
            },
            dispatch: function () {
                return {
                    saveEntityRecord: function (kind, name, record) {
                        saveCalls += 1;
                        assert('postType' === kind, 'Core Data kind');
                        assert('seed_directory' === name, 'Core Data entity');
                        assert('publish' === record.status, 'Core Data publish status');
                        editorState.validation = { state: 'none', summary: '', messages: [] };
                        return Promise.resolve({
                            id: record.id,
                            status: 'publish',
                            wpsck_directory_validation: editorState.validation
                        });
                    }
                };
            },
            useSelect: function (selector) {
                return selector(function () {
                    return {
                        getCurrentPost: function () {
                            return { id: editorState.id, wpsck_directory_validation: editorState.validation };
                        },
                        isSavingPost: function () { return editorState.saving; },
                        isAutosavingPost: function () { return editorState.autosaving; }
                    };
                });
            }
        },
        hooks: {
            addAction: function (hook, namespace, callback) { actions[hook] = callback; },
            addFilter: function (hook, namespace, callback) { filters[hook] = callback; }
        },
        editPost: { PluginPostStatusInfo: 'PluginPostStatusInfo' },
        element: {
            Fragment: 'Fragment',
            createElement: createElement,
            useEffect: useEffect,
            useRef: useRef,
            useState: useState
        },
        plugins: {
            registerPlugin: function (name, definition) { registeredPlugin = definition; }
        }
    };

    vm.runInNewContext(source, { window: { wp: wp } });

    filters['editor.preSavePost']({ status: options.requestedStatus });
    await actions['editor.savePost'](
        { id: editorState.id, type: 'seed_directory' },
        { isAutosave: !!options.autosave }
    );

    async function render() {
        hookCursor = 0;
        pendingEffects = [];
        latestNotice = registeredPlugin.render();
        pendingEffects.forEach(function (effect) { effect(); });
        await new Promise(function (resolve) { setImmediate(resolve); });
        await new Promise(function (resolve) { setImmediate(resolve); });
    }

    await render();
    editorState.saving = true;
    await render();
    editorState.saving = false;
    await render();
    await render();

    return { saveCalls: saveCalls, notice: latestNotice };
}

(async function () {
    var publishResult = await runScenario({ requestedStatus: 'publish' });
    assert(1 === publishResult.saveCalls, 'One publish click performs one Core Data finalization');
    assert(null === publishResult.notice.children[0], 'Clean validation removes the Gutenberg notice');

    var saveResult = await runScenario({ requestedStatus: 'draft' });
    assert(0 === saveResult.saveCalls, 'A simple save never auto-publishes a draft');

    var invalidResult = await runScenario({
        requestedStatus: 'publish',
        serverValidation: { state: 'blocked', summary: 'Blocked', messages: ['Site internet invalide'] }
    });
    assert(0 === invalidResult.saveCalls, 'A still-invalid draft cannot be finalized as published');

    var autosaveResult = await runScenario({ requestedStatus: 'publish', autosave: true });
    assert(0 === autosaveResult.saveCalls, 'An autosave never finalizes publication');

    process.stdout.write('PASS ' + assertions + ' Gutenberg validation JS assertions\n');
}()).catch(function (error) {
    process.stderr.write('FAIL: ' + error.message + '\n');
    process.exit(1);
});
