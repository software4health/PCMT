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
        family_key: '',

        configure: function() {
            this.listenTo(this.getRoot(), this.postUpdateEventName, this.onUpdateField);

            BaseField.prototype.configure.apply(this, arguments);
        },

        fetch: function() {
            if (this.family_key) {
                FetcherRegistry.getFetcher(this.config.fetcher).fetchForFamily(this.family_key).then(function (options) {
                    this.selectOptions = options;
                    this.render();
                }.bind(this));
            } else {
                this.selectOptions = [];
            }
        },

        onUpdateField: function() {
            console.log('on update field');

            let newFamilyKey = propertyAccessor.accessProperty(this.getFormData(), 'configuration.sourceFamily');
            console.log(this.family_key);
            if (newFamilyKey !== this.family_key) {
                console.log('updating!');
                this.family_key = newFamilyKey;
                this.fetch();
            }

        },

    });
});