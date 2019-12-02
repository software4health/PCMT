'use strict';

define(
    [
        'pim/form',
        'backbone',
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pcmt/product/template/draft-list',
        'pim/dialog',
        'pim/security-context',
        'pim/router'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template, Dialog, SecurityContext, Router) {

        return BaseForm.extend({
            events: {
                "click .draft-changes-shortcut": "changesExpand",
                "click .draft-changes-full": "changesCollapse",
                "click .draft-status-choice": "statusChoiceChanged",
                "click .draft-reject": "rejectDraftClicked",
                "click .draft-approve": "approveDraftClicked",
                "click .draft-edit": "editDraftClicked",
                "click .draft-checkbox": "checkboxDraftClicked",
                "click .draft-bulk-approve": "approveBulkDraftClicked",
            },
            configure: function () {
                this.loadParams();
                const model = this.getFormData();
                model.chosenStatus = {};
                model.drafts = [];
                model.chosenDrafts = [];
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
            approveDraftClicked: function (ev) {
                let draftId = ev.currentTarget.dataset.draftId;
                Dialog.confirm(
                    'Are you sure you want to approve this draft?',
                    'Draft approval',
                    function () {
                        return this.approveDraft(draftId);
                    }.bind(this),
                    '',
                    'ok',
                    'Approve'
                );
            },
            editDraftClicked: function (ev) {
                var draftId = ev.currentTarget.dataset.draftId;

                Router.navigate('/' + Routing.generate('pcmt_product_drafts_edit', {id: draftId}), true);
            },
            approveDraft: function (draftId) {
                $.ajax({
                    url: Routing.generate('pcmt_product_drafts_approve', {id: draftId}),
                    type: 'PUT'
                }).done((function () {
                    this.loadDrafts();
                }).bind(this)).fail(function (jqXHR) {
                    let messages = _.map(jqXHR.responseJSON.values, function(value) {
                        return value.attribute + ': ' + value.message;
                    });
                    Dialog.alert(messages.join('\n'), 'Problem with approving draft', '');
                });
            },
            rejectDraftClicked: function (ev) {
                let draftId = ev.currentTarget.dataset.draftId;
                Dialog.confirmDelete(
                    'Are you sure you want to reject this draft?',
                    'Draft rejection',
                    function () {
                        return this.rejectDraft(draftId);
                    }.bind(this),
                    'subtitle',
                    'Reject'
                );
            },
            rejectDraft: function (draftId) {
                $.ajax({
                    url: Routing.generate('pcmt_product_drafts_delete', {id: draftId}),
                    type: 'DELETE'
                }).done((function () {
                    this.loadDrafts();
                }).bind(this)).fail(function () {
                    console.log('rejecting failed.');
                });
            },
            checkboxDraftClicked: function (ev) {
                const model = this.getFormData();
                let draftId = ev.currentTarget.dataset.draftId;
                if (model.chosenDrafts.includes(draftId)) {
                    _.each( model.chosenDrafts, function(draft, index) {
                        if (draft === draftId) {
                            model.chosenDrafts.splice(index, 1);
                            return true;
                        }
                    });
                } else {
                    model.chosenDrafts.push(draftId);
                }
                if (model.chosenDrafts.length > 0) {
                    $('.draft-checkbox-bodyCell').removeClass('AknGrid-bodyCell--actions');
                    $('.draft-bulk-approve-div').show();
                } else {
                    $('.draft-checkbox-bodyCell').addClass('AknGrid-bodyCell--actions');
                    $('.draft-bulk-approve-div').hide();
                }
                $('.draft-bulk-count').text(model.chosenDrafts.length);
            },
            resetChosenDrafts: function (model) {
                model.chosenDrafts = [];
                $('.draft-bulk-approve-div').hide();
            },
            approveBulkDraftClicked: function (ev) {
                const model = this.getFormData();
                Dialog.confirm(
                    'Are you sure you want to approve ' + model.chosenDrafts.length + ' draft(s)?',
                    'Draft approval',
                    function () {
                        const model = this.getFormData();
                        return this.approveBulkDraft(model.chosenDrafts);
                    }.bind(this),
                    '',
                    'ok',
                    'Approve'
                );
            },
            approveBulkDraft: function (chosenDrafts) {
                $.ajax({
                    url: Routing.generate('pcmt_product_drafts_approve_bulk'),
                    data: JSON.stringify({chosenDrafts: chosenDrafts}),
                    type: 'PUT'
                }).done((function () {
                    this.loadDrafts();
                }).bind(this)).fail((function (jqXHR) {
                    let messages = _.map(jqXHR.responseJSON.values, function(value) {
                        return value.attribute + ': ' + value.message;
                    });
                    Dialog.alert(messages.join('\n'), 'Problem with approving draft', '');
                    console.log('bulk approve failed.');
                    this.loadDrafts();
                }).bind(this));
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
                let status = _.find(model.params.statuses, function (s) {
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
                this.resetChosenDrafts(model);
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
                    ...model,
                    _: _,
                    __: __,
                    rejectPermission: SecurityContext.isGranted('pcmt_permission_drafts_reject'),
                    approvePermission: SecurityContext.isGranted('pcmt_permission_drafts_approve'),
                    editPermission: SecurityContext.isGranted('pcmt_permission_drafts_edit')
                }));
                $('#draft_status_choice_' + model.chosenStatus.id).addClass('AknDropdown-menuLink--active active');
            }
        });
    }
);

