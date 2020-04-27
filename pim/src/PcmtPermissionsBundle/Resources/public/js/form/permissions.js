/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pcmt/permissions/template',
        'jquery.select2'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'AknFieldContainer',
            template: _.template(template),
            groups: null,
            initialGroups: null, //@todo - groups loaded on form build

            initialize: function(config) {
                this.config = config.config;
                BaseForm.prototype.initialize.apply(this, arguments);
            },
            /**
             *
             * @return {Promise}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.render.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_save', this.setGroups.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            setInitialGroups: function(categoryId) {
                console.log('update groups on select' + categoryId);
            },

            render: function () {
                FetcherRegistry.getFetcher('user-group').fetchAll().then(function (groups) {
                    this.groups = groups;
                    this.$el.html(this.template({
                        label: 'Allowed to view products',
                        userGroups: groups,
                        requiredLabel: __('pim_common.required_label'),
                    }));

                    this.$('.select2').select2().on('change', this.updateState.bind(this));

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            setGroups: function(groups) {
                let data = this.getFormData();
                data.groups = groups;
                this.setData(data);
            },

            /**
             * Sets new groups on change
             *
             * @param {Object} event
             */
            updateState: function (event) {
                var groupsToSet = [];

                _.each(event.val, function (groupName) {
                    groupsToSet.push(
                        _.find(this.groups, function (group) {
                            return group.code === groupName;
                        })
                    );
                }.bind(this));

                this.setGroups(groupsToSet);
            },
        });
    }
);
