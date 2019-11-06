'use strict';

define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pcmt/product/template/draft-list',
        'routing'
    ],
    function ($, _, __, BaseController, FormBuilder, template, Routing) {
        return BaseController.extend({
            data : [],
            loading: true,
            template: _.template(template),
            loadDrafts: function() {
                $.get(Routing.generate('pcmt_product_drafts_api'))
                    .done(_.bind(function(resp) {
                        this.loading = false;
                        this.data = resp;
                        this.renderInside();
                    }, this))
                    .fail(function() {
                        this.loading = false;
                        console.log('failed');
                    });
            },
            renderInside: function(route) {
                return FormBuilder.build('pcmt-product-drafts-index').then((form) => {
                    this.$el.html(this.template({
                        data: this.data,
                        _ : _,
                        __ : __,
                        loading: this.loading
                    }));
                    //form.setElement(this.$el).render({data: 'xxx'});
                });
            },
            renderForm: function(route) {
                this.loadDrafts();
                return this.renderInside();
            }

        });
    }
);

