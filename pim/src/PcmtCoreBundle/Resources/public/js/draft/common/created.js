'use strict';

define(
    [
        'pim/form/common/meta/created'
    ],
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
