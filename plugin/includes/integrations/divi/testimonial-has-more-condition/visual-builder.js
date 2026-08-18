(function (window) {
    'use strict';

    var hooks = window.vendor && window.vendor.wp && window.vendor.wp.hooks;
    if (!hooks) {
        return;
    }

    var conditionName = 'wpsckTestimonialHasMore';
    var valueKey = 'wpsckResolvedTestimonialHasMore';
    var label = 'WPSCK — Témoignages — Témoignage avec suite';
    var dynamicValue = '$variable(' + JSON.stringify({
        type: 'content',
        value: {
            name: 'loop_wpsck_testimonial_has_more',
            settings: []
        }
    }) + ')$';

    hooks.addFilter(
        'divi.fieldLibrary.conditionalDisplay.conditionsStore',
        'wpsck/testimonial-has-more/conditions-store',
        function (conditions) {
            var exists = conditions.some(function (condition) {
                return condition.name === conditionName;
            });

            return exists ? conditions : conditions.concat([{
                name: conditionName,
                label: label,
                category: 'postInfo'
            }]);
        }
    );

    hooks.addFilter(
        'divi.fieldLibrary.conditionalDisplay.initialCustomItemEdit',
        'wpsck/testimonial-has-more/initial-item',
        function (item, selectedName, id, operator) {
            if (conditionName !== selectedName) {
                return item;
            }

            var conditionSettings = {
                displayRule: 'is',
                enableCondition: 'on',
                adminLabel: label
            };
            conditionSettings[valueKey] = dynamicValue;

            return {
                id: id,
                conditionName: selectedName,
                conditionSettings: conditionSettings,
                operator: operator
            };
        }
    );
}(window));
