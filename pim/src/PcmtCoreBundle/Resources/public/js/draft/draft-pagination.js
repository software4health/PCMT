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
        'pcmt/product/template/draft-pagination'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                "click a": "onChangePage"
            },

            configure: function () {
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', this.render);
            },

            getPaginationHandles: function (model) {

                if (null == model) {
                    return;
                }

                let handles = [];

                const _firstPage = model.firstPage;
                const _currentPage = model.currentPage;
                const _lastPage = model.lastPage;

                if (_lastPage === 0) {
                    return [];
                }

                // how many pages from current should we see in pagination
                const diff = (_currentPage === _firstPage || _currentPage === _lastPage) ? 2 : 1;

                for (let i = _firstPage; i <= _lastPage; ++i) {
                    if (i === _firstPage || i === _lastPage) {
                        this.addHandle(handles, i, i, _currentPage === i);
                        continue;
                    }
                    if (i < _currentPage - diff) {
                        this.addHandle(handles, null, '...');
                        i = _currentPage - diff - 1;
                        continue;
                    }
                    if (i <= _currentPage + diff) {
                        this.addHandle(handles, i, i, _currentPage === i);
                        continue;
                    }
                    if (i > _currentPage + diff) {
                        this.addHandle(handles, null, '...');
                        i = _lastPage - 1;
                        continue;
                    }
                }

                return handles;
            },

            addHandle: function (handles, pageNo, pageText, isCurrent = false) {
                let className = null;
                if (isCurrent) {
                    className = 'active AknActionButton--highlight';
                }
                if (!pageNo) {
                    className = 'AknActionButton--unclickable';
                }
                return handles.push({
                    pageNo: pageNo,
                    pageText: pageText,
                    className: className
                });
            },

            render: function () {
                let model = this.getFormData();
                this.$el.empty();
                if (model.draftsData.params.lastPage <= 1) {
                    return;
                }

                let handles = this.getPaginationHandles(model.draftsData.params);
                this.$el.addClass('AknGridToolbar-center');
                this.$el.append(this.template({
                    handles: handles,
                }));
            },

            onChangePage: function (e) {
                const model = this.getFormData();
                let a = $(e.currentTarget);
                let $span = a.find('.js-page-change');
                const page = $span.data('page');
                if (!page) {
                    return;
                }

                model.draftsData.params.currentPage = page;
                this.setData(model);
                this.render();
                this.getRoot().trigger('pcmt:form:entity:update_pagination');
            }
        });
    }
);

