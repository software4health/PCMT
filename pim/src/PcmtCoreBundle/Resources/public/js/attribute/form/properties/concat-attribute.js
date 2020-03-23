/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
    'jquery',
    'underscore',
    'pim/form',
    'pim/fetcher-registry',
    'oro/translator',
    'pim/template/form/properties/concat-attribute',
], function (
    $,
    _,
    BaseForm,
    FetcherRegistry,
    __,
    formTemplate
) {
    return BaseForm.extend({
        className: 'concatenated-attribute-container',
        events: {
            'change  .member-attribute': 'updateModel',
            'change .separator-field': 'updateModel'
        },
        template: _.template(formTemplate),
        attributes: [],
        members : {
            attributes : ['attribute1', 'attribute2', 'attribute3'],
            separators: ['separator1', 'separator2']
        },

        initialize: function (config) {
            this.config = config.config;
            BaseForm.prototype.initialize.apply(this, arguments);
        },

        configure: function() {

            return $.when(
                this.getAttributes()
                    .then(function (attributes) {
                        this.attributes = attributes;
                    }.bind(this)),
                BaseForm.prototype.configure.apply(this, arguments)
            );
        },
        updateModel: function (event) {

            let data = this.getFormData();

            if(typeof data.concatenated !== 'object' || data.concatenated == null){
                data.concatenated = {}
            }

            /** stringMember is the key, whether 'separators' or 'attributes' **/
            data.concatenated[event.target.name] = this.getFieldValue(event.target);

            this.setData(data);
        },

        render: function(templateContext) {

            this.$el.html(this.template({
                value: "",
                model: this.getFormData(),
                members: this.members,
                multiple: false,
                choices: this.formatChoices(this.attributes),
                readOnly: false,
                isReadOnly: false,
                labels: {
                    defaultChooseAttribute: __('pim_enrich.entity.attribute.property.concat_attribute_member_select.choose'),
                    defaultChooseSeparator: __('pim_enrich.entity.attribute.property.concat_attribute_separator_select.choose')
                }
            }));

            this.delegateEvents();
            this.renderExtensions();
            this.postRender();
        },

        postRender: function () {
            this.$('select.select2').select2({allowClear: true});
        },

        getAttributes: function () {
            return FetcherRegistry.getFetcher('concat-attribute').fetchAll({ options: { limit: 9999 } })
        },

        getFieldValue: function (field) {
            return $(field).val();
        },

        formatChoices: function (attributes) {
            return _.mapObject(attributes, function (attribute) {
                return attribute.code;
            })
        }
    })
});