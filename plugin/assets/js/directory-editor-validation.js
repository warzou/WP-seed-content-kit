(function (wp) {
    'use strict';

    if (!wp || !wp.plugins || !wp.editPost || !wp.element || !wp.data || !wp.components || !wp.hooks || !wp.apiFetch) {
        return;
    }

    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var useEffect = wp.element.useEffect;
    var useRef = wp.element.useRef;
    var useState = wp.element.useState;
    var Notice = wp.components.Notice;
    var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
    var correctedPublishIntents = {};

    function normalizeEditedTitle(title) {
        if ('string' === typeof title) {
            return title;
        }
        return title && 'string' === typeof title.raw ? title.raw : '';
    }

    function getDirectoryNameField() {
        return window.document.getElementById('wp_seed_content_directory_name');
    }

    function getEditedTitle() {
        var editor = wp.data.select('core/editor');
        return normalizeEditedTitle(
            editor && editor.getEditedPostAttribute
                ? editor.getEditedPostAttribute('title')
                : ''
        );
    }

    function updateCanonicalTitle(event) {
        var nameField = event && event.target;
        if (!nameField || 'wp_seed_content_directory_name' !== nameField.id) {
            return;
        }
        var value = nameField.value || '';
        if (value === getEditedTitle()) {
            return;
        }
        var editorDispatch = wp.data.dispatch('core/editor');
        if (editorDispatch && editorDispatch.editPost) {
            editorDispatch.editPost({ title: value });
        }
    }

    function syncDirectoryNameField() {
        var nameField = getDirectoryNameField();
        var title = getEditedTitle();
        if (nameField && window.document.activeElement !== nameField && nameField.value !== title) {
            nameField.value = title;
        }
    }

    function initializeDirectoryNameSync() {
        window.document.addEventListener('input', updateCanonicalTitle);
        window.document.addEventListener('change', updateCanonicalTitle);
        wp.data.subscribe(syncDirectoryNameField);
        syncDirectoryNameField();
    }

    if ('loading' === window.document.readyState) {
        window.document.addEventListener('DOMContentLoaded', initializeDirectoryNameSync);
    } else {
        initializeDirectoryNameSync();
    }

    wp.hooks.addFilter('editor.preSavePost', 'wp-seed-content-kit/directory-publish-intent', function (edits) {
        var editor = wp.data.select('core/editor');
        var record = editor && editor.getCurrentPost ? editor.getCurrentPost() : null;
        var validation = record && record.wpsck_directory_validation ? record.wpsck_directory_validation : null;
        var requestedStatus = edits && edits.status
            ? edits.status
            : (editor && editor.getEditedPostAttribute ? editor.getEditedPostAttribute('status') : '');

        if (
            record
            && record.id
            && 'publish' === requestedStatus
            && validation
            && ('blocked' === validation.state || 'drafted' === validation.state)
        ) {
            correctedPublishIntents[record.id] = true;
        }

        return edits;
    });

    wp.hooks.addAction('editor.savePost', 'wp-seed-content-kit/directory-finalize-corrected-publish', function (post, options) {
        var postId = post && post.id ? post.id : 0;
        if (!postId || (options && options.isAutosave) || !correctedPublishIntents[postId]) {
            return;
        }
        delete correctedPublishIntents[postId];

        return wp.apiFetch({
            path: '/wp/v2/seed_directory/' + postId + '?context=edit&_fields=id,status,wpsck_directory_validation'
        }).then(function (record) {
            if (
                !record
                || 'draft' !== record.status
                || !record.wpsck_directory_validation
                || 'none' !== record.wpsck_directory_validation.state
            ) {
                return;
            }

            return wp.data.dispatch('core').saveEntityRecord(
                'postType',
                'seed_directory',
                { id: postId, status: 'publish' }
            );
        });
    }, 20);

    function DirectoryValidationNotice() {
        var editorState = wp.data.useSelect(function (select) {
            var editor = select('core/editor');
            var record = editor && editor.getCurrentPost ? editor.getCurrentPost() : null;
            return {
                postId: record && record.id ? record.id : 0,
                validation: record && record.wpsck_directory_validation ? record.wpsck_directory_validation : null,
                saving: editor && editor.isSavingPost ? editor.isSavingPost() : false,
                autosaving: editor && editor.isAutosavingPost ? editor.isAutosavingPost() : false
            };
        }, []);
        var validationState = useState(editorState.validation);
        var validation = validationState[0];
        var setValidation = validationState[1];
        var wasSaving = useRef(false);
        var validationHydrated = useRef(false);

        useEffect(function () {
            var editor = wp.data.select('core/editor');
            var panels = wp.data.dispatch('core/edit-post');
            if (editor && editor.getCurrentPostType && 'seed_directory' === editor.getCurrentPostType()
                && panels && panels.removeEditorPanel) {
                panels.removeEditorPanel('post-excerpt');
            }
        }, []);

        useEffect(function () {
            if (editorState.validation && !validationHydrated.current) {
                validationHydrated.current = true;
                setValidation(editorState.validation);
            }
        }, [editorState.validation]);

        useEffect(function () {
            var completedSave = wasSaving.current && !editorState.saving && !editorState.autosaving;
            wasSaving.current = editorState.saving && !editorState.autosaving;
            if (!completedSave || !editorState.postId) {
                return;
            }
            wp.apiFetch({
                path: '/wp/v2/seed_directory/' + editorState.postId + '?context=edit&_fields=id,status,wpsck_directory_validation'
            }).then(function (record) {
                if (record && record.wpsck_directory_validation) {
                    setValidation(record.wpsck_directory_validation);
                }
            });
        }, [editorState.postId, editorState.saving, editorState.autosaving]);

        if (!validation || !validation.state || 'none' === validation.state) {
            return null;
        }

        var status = 'pending' === validation.state || 'grandfathered' === validation.state ? 'warning' : 'error';
        var messages = Array.isArray(validation.messages) ? validation.messages : [];
        return createElement(
            PluginPostStatusInfo,
            { className: 'wpsck-directory-validation-status' },
            createElement(
                Notice,
                { status: status, isDismissible: false },
                createElement('strong', null, validation.summary || ''),
                messages.length ? createElement('ul', null, messages.map(function (message, index) {
                    return createElement('li', { key: index }, message);
                })) : null
            )
        );
    }

    wp.plugins.registerPlugin('wpsck-directory-validation', {
        render: function () {
            return createElement(Fragment, null, createElement(DirectoryValidationNotice));
        }
    });
}(window.wp));
