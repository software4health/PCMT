/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
        'underscore',
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/page-title',
        'pim/i18n'
    ],
    function (
        _,
        BaseController,
        FormBuilder,
        fetcherRegistry,
        UserContext,
        PageTitle,
        i18n
    ) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                if (!this.active) {
                    return;
                }

                return fetcherRegistry.getFetcher('rule').fetch(route.params.id)
                    .then((rule => {
                        var label = _.escape(
                            i18n.getLabel(
                                rule.labels,
                                UserContext.get('catalogLocale'),
                                rule.code
                            )
                        );

                        PageTitle.set({'rule.label': label});

                        return FormBuilder.getFormMeta('pcmt-rules-edit-form')
                            .then(FormBuilder.buildForm)
                            .then((form) => {
                                return form.configure().then(() => {
                                    return form;
                                });
                            })
                            .then((form) => {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(rule);
                                form.trigger('pim_enrich:form:entity:post_fetch', rule);
                                form.setElement(this.$el).render();

                                return form;
                            });
                    }));
            }
        });
    });
