'use strict';

define(
    [
        'pim/product-edit-form/meta/change-family'
    ],
    function (
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            }
        });
    }
);
