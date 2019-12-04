'use strict';

define(
    ['pim/product-edit-form/attribute-filter-at-this-level'],
    function (BaseForm) {
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
