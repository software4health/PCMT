'use strict';

define(
    [
        'pim/product-edit-form/attributes/read-only-parent-attributes'
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
