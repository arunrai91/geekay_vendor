define(['ko'], function (ko) {
    'use strict';

    return {
        /**
         * @param {Object} component
         * @returns {void}
         */
        disableComponent(component) {
            if (component.elems?.()) {
                component.elems().map((elem) => {
                    this.disableComponent(elem);
                });
            }

            if (typeof component.disabled === 'undefined') {
                return;
            }

            if (ko.isObservable(component.disabled)) {
                component.disabled(true);
            }

            if (typeof component.disabled === 'boolean') {
                component.disabled = true;
            }
        }
    };
});
