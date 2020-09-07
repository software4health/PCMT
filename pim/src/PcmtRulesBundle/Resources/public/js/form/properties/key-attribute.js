/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/fields/field',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'pim/template/attribute/tab/properties/group'
    ],
    function (
        $,
        _,
        __,
        BaseField,
        fetcherRegistry,
        UserContext,
        i18n,
        template
    ) {
        return BaseField.extend({
            events: {
                'change select': function (event) {
                    this.errors = [];
                    console.log('change select');
                    console.log(this.getFieldValue(event.target));
                    this.updateModel(this.getFieldValue(event.target));
                    this.getRoot().render();
                }
            },
            template: _.template(template),
            attributes: {},

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return $.when(
                    BaseField.prototype.configure.apply(this, arguments),
                    fetcherRegistry.getFetcher('attribute').fetchAll()
                        .then(function (attributes) {
                            this.attributes = attributes;
                        }.bind(this))
                );
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (templateContext) {
                return this.template(_.extend(templateContext, {
                    value: this.getFormData()[this.fieldName],
                    groups: _.sortBy(this.attributes, 'sort_order'),
                    i18n: i18n,
                    locale: UserContext.get('catalogLocale'),
                    labels: {
                        defaultLabel: __('pcmt.entity.rules.property.attribute.choose')
                    }
                }));
            },

            /**
             * {@inheritdoc}
             */
            postRender: function () {
                this.$('select.select2').select2();
            },

            /**
             * {@inheritdoc}
             */
            getFieldValue: function (field) {
                return $(field).val();
            }
        });
    });
