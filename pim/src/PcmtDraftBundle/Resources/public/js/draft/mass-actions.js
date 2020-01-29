/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * This extension will display the mass actions panel with all the actions available for checked items in a grid.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pcmt/draft/template/mass-actions',
        'pim/dialog'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        Dialog
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDefault-bottomPanel AknDefault-bottomPanel--hidden AknMassActions mass-actions',
            collection: null,
            count: 0,
            events: {
                'click .select-none': 'selectNone',
                'click .select-visible': 'selectVisible',
                'click .select-button': 'toggleButton',
                'click .draft-bulk-approve': 'approveBulkDraftClicked'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure() {
                const setCollection = (collection) => {
                    if (null === this.collection) {
                        this.listenTo(this.getRoot(), 'pcmt_draft_checkbox:selected', this.select.bind(this));
                    }

                    this.collection = collection;
                    this.updateView();
                };
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', setCollection);

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    selectedProductsLabel: __(this.config.label),
                    select: __('pim_datagrid.select.title'),
                    selectAll: __('pim_common.all'),
                    selectVisible: __('pim_datagrid.select.all_visible'),
                    selectNone: __('pim_common.none')
                }));

                this.updateView();

                BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Updates the count after clicking in a single event
             *
             * @param {Object}  model The selected model
             * @param {boolean} checked
             */
            select(model, checked) {
                if (checked) {
                    this.count = Math.min(this.count + 1, this.collection.length);
                } else {
                    this.count = Math.max(this.count - 1, 0);
                }

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select all visible" button
             */
            selectVisible() {
                if (this.count === this.collection.length) {
                    this.count = 0;
                }
                this.getRoot().trigger('pcmt_draft_checkbox:selectAllVisible');

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select none" button
             */
            selectNone() {
                this.count = 0;
                this.getRoot().trigger('pcmt_draft_checkbox:selectNone');

                this.updateView();
            },

            /**
             * Updates the count (select all or select none), regarding the current count.
             */
            toggleButton() {
                if (this.count === this.collection.length) {
                    this.selectNone();
                } else {
                    this.selectAll();
                }
            },

            /**
             * Updates the current view.
             *
             * In this function, we do not use render() method because:
             * - We need to animate this extension (with CSS)
             * - The events of the sub extensions are lost after re-render.
             */
            updateView() {
                if (this.count > 0) {
                    this.$el.removeClass('AknDefault-bottomPanel--hidden');

                    if (this.count >= this.collection.length) {
                        this.$el.find('.AknSelectButton')
                            .removeClass('AknSelectButton--partial')
                            .addClass('AknSelectButton--selected');
                    } else {
                        this.$el.find('.AknSelectButton')
                            .removeClass('AknSelectButton--selected')
                            .addClass('AknSelectButton--partial');
                    }
                } else {
                    this.$el.addClass('AknDefault-bottomPanel--hidden');

                    this.$el.find('.AknSelectButton')
                        .removeClass('AknSelectButton--selected')
                        .removeClass('AknSelectButton--partial');
                }

                this.$el.find('.count').text(this.count);
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
                    url: Routing.generate('pcmt_core_drafts_approve_bulk'),
                    data: JSON.stringify({chosenDrafts: chosenDrafts}),
                    type: 'PUT'
                }).done((function () {
                    this.selectNone();
                    this.getRoot().trigger('pcmt_drafts:approved');
                }).bind(this)).fail((function (jqXHR) {
                    this.selectNone();
                    let messages = _.map(jqXHR.responseJSON.values, function (value) {
                        return value.attribute + ': ' + value.message;
                    });
                    Dialog.alert(messages.join('\n'), 'Problem with approving draft', '');
                    console.log('bulk approve failed.');
                    this.getRoot().trigger('pcmt_drafts:approved');
                }).bind(this));
            }
        });
    }
);
