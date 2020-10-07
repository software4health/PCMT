'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'pim/form/common/creation/modal',
        'pim/form-builder',
        'pim/user-context',
        'oro/loading-mask',
        'pim/router',
        'oro/messenger',
        'pim/template/form/creation/modal',
        'pim/common/property'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Routing,
        BaseForm,
        FormBuilder,
        UserContext,
        LoadingMask,
        router,
        messenger,
        template,
        propertyAccessor
    ) {
        return BaseForm.extend({
            /**
             *
             * @return {Promise}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.displayError.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Save the form content by posting it to backend
             *
             * @return {Promise}
             */
            save() {
                this.validationErrors = {};

                const loadingMask = new LoadingMask();
                this.$el.empty().append(loadingMask.render().$el.show());

                let data = $.extend(this.getFormData(),
                    this.config.defaultValues || {});

                if (this.config.excludedProperties) {
                    data = _.omit(data, this.config.excludedProperties)
                }

                return $.ajax({
                    url: Routing.generate(this.config.postUrl),
                    type: 'POST',
                    data: JSON.stringify(data)
                }).fail(function (response) {
                    if (response.responseJSON) {
                        this.getRoot().trigger(
                            'pim_enrich:form:entity:bad_request',
                            {'sentData': this.getFormData(), 'response': response.responseJSON.values}
                        );
                    }

                    this.validationErrors = response.responseJSON ?
                        this.normalize(response.responseJSON) : [{
                            message: __('pim_enrich.entity.fallback.generic_error')
                        }];
                    this.render();
                }.bind(this))
                    .always(() => loadingMask.remove());
            },

            displayError: function (event) {
                _.each(event.response, function (error) {
                    if (error.global) {
                        messenger.notify('error', error.message);
                    }
                })
            }
        });
    }
);
