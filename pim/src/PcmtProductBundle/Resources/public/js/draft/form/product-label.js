'use strict';

define(
    ['pim/product-edit-form/product-label'],
    function (Label) {
        return Label.extend({
            /**
             * {@inheritdoc}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            }
        });
    }
);
