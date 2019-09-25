'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/field',
    'pim/fetcher-registry',
    'oro/translator',
    'pim/template/form/common/fields/select'
], function (
    $,
    _,
    BaseField,
    fetcherRegistry,
    __,
    selectTemplate
) {
        return BaseField.extend({
            events: {
                'change select': function (event) {
                    this.errors = [];
                    this.updateModel(this.getFieldValue(event.target));
                    this.render();
                }
            },
            template: _.template(selectTemplate),
            attributes: [],

            // fetch all the attributes currently in the system
            configure: function () {
                return $.when(
                    BaseField.prototype.configure.apply(this, arguments),
                    fetcherRegistry.getFetcher('concat-attribute').fetchAll()
                        .then(function (attributesList) {
                            this.attributes = attributesList;
                        }.bind(this))
                );
            },

            renderInput: function (templateContext) {
                if (!_.has(this.getFormData(), this.fieldName) && _.has(this.config, 'defaultValue')) {
                    this.updateModel(this.config.defaultValue);
                }

                return this.template(_.extend(templateContext, {
                    value: this.getFormData()[this.fieldName],
                    choices: this.formatChoices(this.attributes),
                    multiple: false,
                    labels: {
                        defaultLabel: __('pim_enrich.entity.attribute.property.concat_attribute_member_select.choose')
                    }
                }));
            },

            postRender: function () {
                this.$('select.select2').select2({allowClear: true});
            },

            formatChoices: function (attributesList) {
                return _.mapObject(attributesList, function (attribute) {
                    return attribute.code;
                });
            },

            getFieldValue: function (field) {
                return $(field).val();
            }
        });
});

