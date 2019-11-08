'use strict';

define(
    [
        'pim/form',
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pcmt/product/template/draft-list'
    ],
    function (BaseForm, $, _, __, Routing, template) {

        return BaseForm.extend({
            events: {
                "click .draft-changes-shortcut": "changesExpand",
                "click .draft-changes-full": "changesCollapse",
            },
            changesExpand: function (ev) {
                let id = ev.currentTarget.dataset.draftId;
                let divFullId = '#draft-changes-full-' + id;
                $(divFullId).show();
                let divShortcutId = '#draft-changes-shortcut-' + id;
                $(divShortcutId).hide();
            },
            changesCollapse: function (ev) {
                let id = ev.currentTarget.dataset.draftId;
                let divFullId = '#draft-changes-full-' + id;
                $(divFullId).hide();
                let divShortcutId = '#draft-changes-shortcut-' + id;
                $(divShortcutId).show();
            },
            data: [],
            loading: true,
            template: _.template(template),
            loadDrafts: function () {
                $.get(Routing.generate('pcmt_product_drafts_api'))
                    .done(_.bind(function (resp) {
                        this.loading = false;
                        this.data = resp;
                        this.renderInside();
                    }, this))
                    .fail(function () {
                        this.loading = false;
                        console.log('failed');
                    });
            },
            render: function () {
                this.loadDrafts();
                this.renderInside();
            },
            renderInside: function () {
                this.$el.html(this.template({
                    data: this.data,
                    _: _,
                    __: __,
                    loading: this.loading
                }));
            }
        });
    }
);

