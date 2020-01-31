/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';
define(
    [
        'pim/form',
        'oro/translator',
    ], function (
        BaseForm,
        __
    ) {
        return BaseForm.extend({
            count: null,

            initialize(config) {
                this.config = config.config;
            },

            configure: function () {
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', this.setupCount.bind(this));
            },

            render() {
                if (null !== this.count) {
                    this.$el.text(
                        __(this.config.title, {count: this.count}, this.count)
                    );
                } else {
                    this.$el.text(
                        __(this.config.title, {count: 0}, 0)
                    );
                }
            },

            setupCount() {
                let model = this.getFormData();
                this.count = model.draftsData.params.total ? model.draftsData.params.total : null;
                this.render();
            }
        });
    }
);
