define([], function () {
    'use strict';

    return function (form) {
        let originalUpdate = form.update;

        form.update = function (type) {
            let self = this;

            // temporary fix to set value for ui-component "simple_action"
            setTimeout(function() {
                originalUpdate.call(self, type);
            }, 50);
        };

        return form;
    };
});
