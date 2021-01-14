/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Attribute Select field for rules job
 */
define([
    'underscore',
    'pcmt/rules/job/field/select',
    'pim/common/property',
    'pim/fetcher-registry'
], function (
    _,
    BaseField,
    propertyAccessor,
    FetcherRegistry
) {
    return BaseField.extend({
        sourceFamily: '',
        destinationFamily: '',

        configure: function() {
            this.listenTo(this.getRoot(), this.postUpdateEventName, this.onUpdateField);

            BaseField.prototype.configure.apply(this, arguments);
        },

        fetch: function() {
            if (this.sourceFamily && this.destinationFamily) {
                let options = {
                    sourceFamily: this.sourceFamily,
                    destinationFamily: this.destinationFamily
                };
                if (typeof this.config.types !== 'undefined') {
                    options.types = this.config.types;
                }
                if (typeof this.config.validationRule !== 'undefined') {
                    options.validationRule = this.config.validationRule;
                }
                FetcherRegistry.getFetcher(this.config.fetcher).fetchForOptions(options).then(
                    function (options) {
                        this.selectOptions = options;
                        this.render();
                        this.updateState();
                    }.bind(this)
                );
            } else {
                this.selectOptions = [];
            }
        },

        onUpdateField: function() {
            const newSourceFamily = propertyAccessor.accessProperty(this.getFormData(), this.config.sourceFamily);
            const newDestinationFamily = propertyAccessor.accessProperty(this.getFormData(), this.config.destinationFamily);

            if (newSourceFamily !== this.sourceFamily) {
                this.sourceFamily = newSourceFamily;

                this.fetch();
            }

            if (newDestinationFamily !== this.destinationFamily) {
                this.destinationFamily = newDestinationFamily;

                this.fetch();
            }
        },

    });
});
