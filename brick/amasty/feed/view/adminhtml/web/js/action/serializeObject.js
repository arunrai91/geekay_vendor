/**
 * Convert HTML form data to JS object with parsed arrays
 */
define([ 'jquery' ], function ($) {
    'use strict';

    /**
     * @param {jQuery} $form form element wrapped with jQuery
     * @returns {{}}
     */
    const serializeObject = function ($form) {
        const PATTERNS = {
                'validate': /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                'key': /[a-zA-Z0-9_]+|(?=\[\])/g,
                'push': /^$/,
                'fixed': /^\d+$/,
                'named': /^[a-zA-Z0-9_]+$/
            },
            build = function (base, key, value) {
                base[key] = value;
                return base;
            },
            pushCounter = function (key) {
                if (pushCounters[key] === undefined) {
                    pushCounters[key] = 0;
                }
                return pushCounters[key]++;
            };
        let json = {},
            pushCounters = {};

        $.each($form.serializeArray(), function () {
            let k,
                keys,
                merge,
                reverse_key;

            // Skip invalid keys
            if (!PATTERNS.validate.test(this.name)) {
                return;
            }

            keys = this.name.match(PATTERNS.key);
            merge = this.value;
            reverse_key = this.name;

            while ((k = keys.pop()) !== undefined) {

                // Adjust reverse_key
                reverse_key = reverse_key.replace(new RegExp('\\[' + k + '\\]$'), '');

                // Push
                if (k.match(PATTERNS.push)) {
                    merge = build([], pushCounter(reverse_key), merge);
                } else if (k.match(PATTERNS.fixed)) {
                    merge = build({}, k, merge);
                } else if (k.match(PATTERNS.named)) {
                    merge = build({}, k, merge);
                }
            }

            json = $.extend(true, json, merge);
        });

        return json;
    };

    return serializeObject;
});