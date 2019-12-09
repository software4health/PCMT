'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/save',
        'oro/messenger',
        'pcmt/product-draft-saver',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        ProductDraftSaver,
        FieldManager,
        i18n,
        UserContext
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.product.flash.update.success'),
            updateFailureMessage: __('pim_enrich.entity.product.flash.update.fail'),
            label: __('pcmt_product.drafts_editing.button.save_draft'),

            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.save);
                this.listenTo(this.getRoot(), 'pim_enrich:form:update-association', this.save);

                return BaseSave.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            save: function (options) {
                var draft = $.extend(true, {}, this.getFormData());
                var draftId = draft.id;

                delete draft.product.meta;

                var notReadyFields = FieldManager.getNotReadyFields();

                if (0 < notReadyFields.length) {
                    var fieldLabels = _.map(notReadyFields, function (field) {
                        return i18n.getLabel(
                            field.attribute.label,
                            UserContext.get('catalogLocale'),
                            field.attribute.code
                        );
                    });

                    messenger.notify(
                        'error',
                        __('pim_enrich.entity.product.flash.update.fields_not_ready', {
                            'fields': fieldLabels.join(', ')
                        })
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ProductDraftSaver
                    .save(draftId, draft)
                    .then(function (data) {
                        this.postSave();

                        this.setData(data, options);

                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);