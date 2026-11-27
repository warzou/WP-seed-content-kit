(function (window) {
    'use strict';

    var hooks = window.vendor && window.vendor.wp && window.vendor.wp.hooks;
    var data = window.WpSeedContentKitDiviDirectoryHasMoreConditionData || {};
    if (!hooks) {
        return;
    }

    var conditionName = 'wpsckDirectoryHasMore';
    var valueKey = 'wpsckResolvedPresentationMore';
    var label = 'WPSCK — Annuaire — Présentation avec suite';
    var dynamicValue = '$variable(' + JSON.stringify({
        type: 'content',
        value: {
            name: 'loop_wpsck_directory_presentation_more',
            settings: []
        }
    }) + ')$';
    var contactValueKey = 'wpsckResolvedDirectoryContact';
    var contactConditions = Array.isArray(data.contactConditions)
        ? data.contactConditions.map(function (definition) {
            return [definition.name, definition.label, definition.provider];
        })
        : [];

    function findContactCondition(name) {
        for (var index = 0; index < contactConditions.length; index += 1) {
            if (contactConditions[index][0] === name) {
                return contactConditions[index];
            }
        }
        return null;
    }

    hooks.addFilter(
        'divi.fieldLibrary.conditionalDisplay.conditionsStore',
        'wpsck/directory-has-more/conditions-store',
        function (conditions) {
            var exists = conditions.some(function (condition) {
                return condition.name === conditionName;
            });

            var nextConditions = exists ? conditions : conditions.concat([{
                name: conditionName,
                label: label,
                category: 'postInfo'
            }]);
            contactConditions.forEach(function (definition) {
                var contactExists = nextConditions.some(function (condition) {
                    return condition.name === definition[0];
                });
                if (!contactExists) {
                    nextConditions = nextConditions.concat([{
                        name: definition[0],
                        label: definition[1],
                        category: 'postInfo'
                    }]);
                }
            });
            return nextConditions;
        }
    );

    hooks.addFilter(
        'divi.fieldLibrary.conditionalDisplay.initialCustomItemEdit',
        'wpsck/directory-has-more/initial-item',
        function (item, selectedName, id, operator) {
            var contactCondition = findContactCondition(selectedName);
            if (conditionName !== selectedName && !contactCondition) {
                return item;
            }

            var selectedLabel = contactCondition ? contactCondition[1] : label;
            var conditionSettings = {
                displayRule: 'is',
                enableCondition: 'on',
                adminLabel: selectedLabel
            };
            if (contactCondition) {
                conditionSettings[contactValueKey] = '$variable(' + JSON.stringify({
                    type: 'content',
                    value: {
                        name: contactCondition[2],
                        settings: []
                    }
                }) + ')$';
            } else {
                conditionSettings[valueKey] = dynamicValue;
            }

            return {
                id: id,
                conditionName: selectedName,
                conditionSettings: conditionSettings,
                operator: operator
            };
        }
    );
}(window));
