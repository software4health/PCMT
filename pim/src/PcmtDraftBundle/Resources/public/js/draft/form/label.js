/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

define (
    [
        'oro/translator',
        'pim/form/common/label',
        'pcmt/common/template/form/label'
    ],
    function (
        __,
        Label,
        template
    ) {
        return Label.extend({
            tagName: 'div',
            template: _.template(template),
            className: '',

            createdDraftLabel: '',
            updatedDraftLabel: '',

            initialize: function (meta) {
                this.config = meta.config;

                this.createdDraftLabel = __(this.config.labels.createdDraft);
                this.updatedDraftLabel = __(this.config.labels.updatedDraft);

                Label.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                let label = '';

                if ('' !== this.getFormData().updatedAt && this.getFormData().createdAt !== this.getFormData().updatedAt) {
                    const updated = new Date(this.getFormData().updatedAt);

                    label = this.updatedDraftLabel + ' ' + updated.toLocaleDateString(undefined, {year: 'numeric', month: 'long', day: 'numeric'});
                } else {
                    label = this.createdDraftLabel;
                }

                this.$el.empty().html(this.template({
                    label: label
                }));

                return this;
            }
        });
    }
);
