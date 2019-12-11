 'use strict';

 define(
     ['pim/product-edit-form/attribute-filter-missing-required'],
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
