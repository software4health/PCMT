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
                "click .draft-status-choice": "statusChoiceChanged",
            },
            configure: function () {
                this.loadParams();
                const model = this.getFormData();
                model.chosenStatus = {};
                model.drafts = [];
                model.params = {};
                model.loading = true;
                this.setData(model);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
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
            statusChoiceChanged: function (ev) {
                this.changeStatusChoice(ev.currentTarget.dataset.value);
            },
            changeStatusChoice: function (newChosenStatusId) {
                newChosenStatusId = parseInt(newChosenStatusId);
                const model = this.getFormData();
                if (newChosenStatusId === model.chosenStatus.id) {
                    return;
                }
                let status = _.find(model.params.statuses, function(s){
                    return s.id === newChosenStatusId;
                });
                if (!status) {
                    return;
                }
                model.chosenStatus = status;
                this.setData(model);
                this.loadDrafts();
            },
            template: _.template(template),
            loadDrafts: function () {
                const model = this.getFormData();
                model.loading = true;
                this.setData(model);
                $.get(Routing.generate('pcmt_product_drafts_api', {status: model.chosenStatus.id}))
                    .done(_.bind(function (resp) {
                        const model = this.getFormData();
                        model.drafts = resp;
                        model.loading = false;
                        this.setData(model);
                    }, this))
                    .fail(function () {
                        const model = this.getFormData();
                        model.loading = false;
                        this.setData(model);
                        console.log('failed');
                    });
            },
            loadParams: function () {
                $.get(Routing.generate('pcmt_product_drafts_params'))
                    .done(_.bind(function (resp) {
                        const model = this.getFormData();
                        model.params = resp;
                        this.setData(model);
                        this.changeStatusChoice(1);
                    }, this))
                    .fail(function () {
                        console.log('Loading params failed');
                    });
            },
            render: function () {
                const model = this.getFormData();
                this.$el.html(this.template({
                    _: _,
                    __: __,
                    data: model.drafts,
                    params: model.params,
                    loading: model.loading,
                    chosenStatus: model.chosenStatus
                }));
                $("#draft_status_choice_" + model.chosenStatus.id).addClass("AknDropdown-menuLink--active active");
            }
        });
    }
);

