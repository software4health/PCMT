/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 *
 */
define(
    [
        'jquery',
        'underscore',
        'pim/job/common/edit/field/field',
        'pcmt/rules/template/job/rules/edit/attribute-mapping',
        'pcmt/rules/template/job/rules/edit/attribute-mapping-row'
    ],
    function (
        $,
        _,
        BaseForm,
        template,
        rowTemplate
    ) {

        return BaseForm.extend({
            events: {
                "click .add-row": "addRow",
                "click .remove-row": "removeRow",
                "change input": "updateModelAfterChange",
            },
            template: _.template(template),
            rowTemplate: _.template(rowTemplate),
            mappingValues: [],
            initialize: function(config) {
                BaseForm.prototype.initialize.apply(this, arguments);
                if (undefined === this.config.fieldCode) {
                    throw new Error('This view must be configured with a field code.');
                }
            },
            configure: function() {
                BaseForm.prototype.configure.apply(this, arguments);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.postFetch);
            },
            postFetch: function(data) {
                let value = this.getValue();
                if (_.isArray(value)) {
                    this.mappingValues = value;
                }
                if (_.isEmpty(this.mappingValues)) {
                    this.addRow();
                }
            },
            addRow: function() {
                let index = this.getMaxIndex() + 1;
                this.mappingValues.push({
                    index: index,
                    sourceValue: '',
                    destinationValue: '',
                });
                this.updateState();
                this.render();
            },
            removeRow: function(ev) {
                var chosenIndex = $(ev.currentTarget).data('index');
                this.mappingValues = _.filter(this.mappingValues, function(element) {
                    return element.index !== chosenIndex;
                });
                this.updateState();
                this.render();
            },
            render: function () {
                this.$el.html(this.template({
                    rowCount : this.rowCount,
                    rowTemplate: this.rowTemplate,
                    data: this.mappingValues,
                }));
                this.delegateEvents();
                return this;
            },
            updateModelAfterChange: function (event) {
                this.updateModelValue(parseInt(event.target.dataset.index), event.target.name, event.target.value);
            },
            updateModelValue: function (index, type, value) {
                let mappingValue = _.find(this.mappingValues, function(element) {
                    return element.index === index;
                });
                if (!mappingValue) {
                    return;
                }
                mappingValue[type] = value;
                this.updateState();
            },
            getMaxIndex: function() {
                if (_.isEmpty(this.mappingValues)) {
                    return 0;
                }
                let maxElement = _.max(this.mappingValues, function (element) {
                    return element.index;
                });
                return maxElement.index;

            },
            getFieldValue: function () {
                return this.mappingValues;
            },
        });
    }
);