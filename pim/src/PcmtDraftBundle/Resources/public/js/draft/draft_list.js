/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

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
        'pim/router',
        'pim/form-builder',
        'pim/form-modal'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template, Dialog, SecurityContext, Router, FormBuilder, FormModal) {

        return BaseForm.extend({
            events: {
                "click .draft-changes-shortcut": "changesExpand",
                "click .draft-changes-full": "changesCollapse",
                "click .draft-reject": "rejectDraftClicked",
                "click .draft-approve": "approveDraftClicked",
                "click .draft-edit": "editDraftClicked",
                "click .draft-checkbox": "checkboxDraftClicked"
            },
            configure: function () {
                const model = this.getFormData();
                model.drafts = [];
                model.chosenDrafts = [];
                model.params = {};
                model.draftsData = {params: {currentPage: 1}};
                model.loading = true;
                this.setData(model);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
                this.listenTo(this.getRoot(), 'pcmt:form:entity:update_pagination', this.onUpdatePagination);
                this.listenTo(this.getRoot(), 'pcmt:drafts:status_choice:changed', this.onUpdateStatusChoice);
                this.listenTo(this.getRoot(), 'pcmt_draft_checkbox:selected', this.draftSelected);
                this.listenTo(this.getRoot(), 'pcmt_draft_checkbox:selectAllVisible', this.allVisibleDraftsSelected);
                this.listenTo(this.getRoot(), 'pcmt_draft_checkbox:selectNone', this.resetChosenDrafts);
                this.listenTo(this.getRoot(), 'pcmt_drafts:approved', this.loadDrafts);
            },

            onUpdatePagination: function (ev) {
                this.loadDrafts();
            },

            onUpdateStatusChoice: function (ev) {
                this.loadDrafts(true);
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
                var draftId = parseInt(ev.currentTarget.dataset.draftId);
                var draft = _.filter(this.getFormData().drafts, (draft) => {
                    return draftId === draft.id;
                })[0];

                if ('New product draft' === draft.type) {
                    if (draft.values.parentId) {
                        return FormBuilder.build('pcmt-product-model-add-child-form').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    } else {
                        return FormBuilder.build('pcmt-product-create-modal').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    }
                } else if ('New product model draft' === draft.type) {
                    if (draft.values.parentId) {
                        return FormBuilder.build('pcmt-product-model-add-child-form').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    } else {
                        return FormBuilder.build('pcmt-product-model-create-modal').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    }
                } else {
                    Router.navigate('/' + Routing.generate('pcmt_core_drafts_edit', {id: draftId}), true);
                }
            },
            approveDraft: function (draftId) {
                $.ajax({
                    url: Routing.generate('pcmt_core_drafts_approve', {id: draftId}),
                    type: 'PUT'
                }).done((function () {
                    this.loadDrafts();
                }).bind(this)).fail(function (jqXHR) {
                    let messages = _.map(jqXHR.responseJSON.values, function (value) {
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
                    url: Routing.generate('pcmt_core_drafts_delete', {id: draftId}),
                    type: 'DELETE'
                }).done((function () {
                    this.loadDrafts();
                }).bind(this)).fail(function () {
                    console.log('rejecting failed.');
                });
            },
            checkboxDraftClicked: function (ev) {
                const model = this.getFormData();

                this.getRoot().trigger(
                    'pcmt_draft_checkbox:selected',
                    model.drafts,
                    ev.currentTarget.checked,
                    ev.currentTarget.dataset.draftId
                );
            },

            draftSelected: function (drafts, isChecked, draftId) {
                let model = this.getFormData();

                if (isChecked) {
                    if (!model.chosenDrafts.includes(parseInt(draftId))) {
                        model.chosenDrafts.push(parseInt(draftId));
                    }
                } else {
                    if (model.chosenDrafts.includes(parseInt(draftId))) {
                        _.each(model.chosenDrafts, (draft, index) => {
                            if (draft === parseInt(draftId)) {
                                model.chosenDrafts.splice(index, 1);
                                return true;
                            }
                        });
                    }
                }

                if (model.chosenDrafts.length > 0) {
                    $('.draft-checkbox-bodyCell').removeClass('AknGrid-bodyCell--actions');
                } else {
                    $('.draft-checkbox-bodyCell').addClass('AknGrid-bodyCell--actions');
                }
            },

            allVisibleDraftsSelected: function () {
                this.resetChosenDrafts();

                let model = this.getFormData();

                _.each(this.$el.find('.draft-checkbox'), (checkbox) => {
                    $(checkbox).prop('checked', true);
                    this.getRoot().trigger(
                        'pcmt_draft_checkbox:selected',
                        model.drafts,
                        true,
                        $(checkbox).data('draft-id')
                    );
                });
            },

            resetChosenDrafts: function () {
                let model = this.getFormData();

                _.each(this.$el.find('.draft-checkbox'), (checkbox) => {
                    $(checkbox).prop('checked', false);
                    this.getRoot().trigger(
                        'pcmt_draft_checkbox:selected',
                        model.drafts,
                        false,
                        $(checkbox).data('draft-id')
                    );
                });
            },

            template: _.template(template),

            loadDrafts: function (reset = false) {
                const model = this.getFormData();
                if (!model.chosenStatus) {
                    return;
                }
                if (reset) {
                    model.draftsData.params.currentPage = 1;
                }
                this.resetChosenDrafts();
                model.loading = true;
                this.setData(model);
                $.get(Routing.generate('pcmt_core_drafts_api', {
                    status: model.chosenStatus.id,
                    page: model.draftsData.params.currentPage
                }))
                    .done(_.bind(function (resp) {
                        const model = this.getFormData();
                        model.drafts = resp.objects;
                        model.draftsData.params = resp.params;
                        model.loading = false;
                        this.setData(model);
                        this.getRoot().trigger('pcmt:drafts:listReloaded', model.drafts);
                    }, this))
                    .fail(_.bind(function () {
                        const model = this.getFormData();
                        model.drafts = [];
                        model.loading = false;
                        this.setData(model);
                        this.getRoot().trigger('pcmt:drafts:listReloaded', model.drafts);
                    }, this));
            },
            render: function () {
                const model = this.getFormData();
                if (!model.chosenStatus) {
                    return;
                }
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

