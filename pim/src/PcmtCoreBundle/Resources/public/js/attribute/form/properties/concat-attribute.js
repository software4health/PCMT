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
        members : {},

        initialize: function (config) {
            this.config = config.config;
            BaseForm.prototype.initialize.apply(this, arguments);
            this.members = {
                attributes : ['attribute1', 'attribute2'],
                separators: ['separator1']
            };
        },

        configure: function() {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.updateMembersList);
            this.listenTo(this.getRoot(), 'pcmt:attribute:members_changed', this.render);
            return $.when(
                this.getAttributes()
                    .then(function (attributes) {
                        this.attributes = attributes;
                    }.bind(this)),
                BaseForm.prototype.configure.apply(this, arguments)
            );

        },
        updateMembersList: function () {
            if (!this.checkIfLastAttributeEmpty()) {
                this.addBaseAttribute();
                this.updateMembersList();
            } else {
                if (this.checkIfLastTwoAttributesAndSeparatorEmpty()) {
                    this.removeBaseAttribute();
                }
            }
        },
        removeBaseAttribute: function() {
            this.members.attributes.pop();
            this.members.separators.pop();
            this.getRoot().trigger('pcmt:attribute:members_changed');
        },
        addBaseAttribute: function() {
            let attributeCount = this.members.attributes.length;
            let keya = 'attribute' + (attributeCount + 1);
            this.members.attributes.push(keya);
            let keys = 'separator' + attributeCount;
            this.members.separators.push(keys);
            this.getRoot().trigger('pcmt:attribute:members_changed');
        },
        checkIfLastAttributeEmpty: function() {
            let data = this.getFormData();
            let lastA = _.last(this.members.attributes);
            return !data.concatenated[lastA]
        },
        checkIfLastTwoAttributesAndSeparatorEmpty: function() {
            let data = this.getFormData();
            let lastS = _.last(this.members.separators);
            let lastAs = _.last(this.members.attributes, 2);
            if (data.concatenated[lastS]) {
                 return false;
            }
            for (let i = 0; i < lastAs.length; i++) {
                if (data.concatenated[lastAs[i]]) {
                    return false;
                }
            }
            return true;
        },
        updateModel: function (event) {

            let data = this.getFormData();

            if(typeof data.concatenated !== 'object' || data.concatenated == null){
                data.concatenated = {}
            }

            /** stringMember is the key, whether 'separators' or 'attributes' **/
            data.concatenated[event.target.name] = this.getFieldValue(event.target);
            this.setData(data);

            this.updateMembersList();
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