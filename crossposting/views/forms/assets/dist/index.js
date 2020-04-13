var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
document.eventListener('DOMContentLoaded', function () {
    var esf = document.querySelector('.export-settings-form');
    if (esf) {
        new CED.TExportSettings({
            element: '.export-settings-form'
        });
    }
    var isf = document.querySelector('.import-settings-form');
    if (isf) {
        new CED.TImportSettings({
            element: '.import-settings-form'
        });
    }
    var clist = document.querySelector('#companies_grid');
    if (clist) {
        new CED.TCompaniesList({
            element: clist
        });
    }
});
var CED;
(function (CED) {
    var consts;
    (function (consts) {
        consts["EVENT_CHANGE_EXPORT_LIST"] = "ev_CompanyListChanged";
        consts["EVENT_OVERLOAD_CHOOSED_ITEMS"] = "ev_OverloadChoosedItems";
        consts["EVENT_CHANGE_VIEW_MODE"] = "ev_ChangeViewMode";
        consts["ATTR_INPUT_COUNT"] = "data-count";
        consts["CLASS_OVERLOADER_CHOOSED_ITEMS"] = "overloaded";
        consts[consts["STAGE_PRE"] = 1] = "STAGE_PRE";
        consts[consts["STAGE_POST"] = 2] = "STAGE_POST";
        consts[consts["STAGE_CHANGE_VIEWMODE"] = 4] = "STAGE_CHANGE_VIEWMODE";
    })(consts = CED.consts || (CED.consts = {}));
    var TExportSettings = (function (_super) {
        __extends(TExportSettings, _super);
        function TExportSettings(options) {
            var _this = _super.call(this, options) || this;
            Objects.setDefinition(_this, '_choosedCount', 0);
            _this._changeCountItems();
            _this.switchInterval();
            return _this;
        }
        TExportSettings.prototype._initEvents = function () {
            var _that = this;
            [].map.call(this.form.fields.findByName('exportCompaniesIds'), function (_el) {
                _el.fieldElement['_change'] = _el.fieldElement.eventListener('change', function () {
                    _that.fire(CED.consts.EVENT_CHANGE_EXPORT_LIST);
                });
                _el.fieldElement['getItemsCount'] = function () {
                    return parseInt(this.parentElement.getAttribute(CED.consts.ATTR_INPUT_COUNT));
                };
            });
            [].map.call(this.form.fields.findByName('viewMode'), function (_el) {
                _el.fieldElement['_change'] = _el.fieldElement.eventListener('change', function () {
                    _that._changeViewMode();
                });
            });
            this.element[CED.consts.EVENT_CHANGE_EXPORT_LIST]
                = this.element.eventListener(CED.consts.EVENT_CHANGE_EXPORT_LIST, function (ev) {
                    _that._changeCountItems();
                });
            try {
                this.sheduleSwitcher.fieldElement
                    .eventListener('change', function (ev) {
                    _that.switchInterval();
                });
            }
            catch (err) { }
            this.submitButton.eventListener('click', function () {
                _that._submitForm();
            });
            $(this.form).on('beforeSubmit', function () {
                _that.form.fields.findByName('itemsCount').value = _that._choosedCount.toString();
            });
        };
        TExportSettings.prototype._submitForm = function () {
            this.form.fields.findByName('stage').value = CED.consts.STAGE_POST.toString();
            this.form.submit();
        };
        TExportSettings.prototype._changeViewMode = function () {
            this.form.fields.findByName('stage').value = CED.consts.STAGE_CHANGE_VIEWMODE.toString();
            this.form.submit();
        };
        TExportSettings.prototype._changeCountItems = function () {
            var _that = this;
            _that.choosedCount = 0;
            [].map.call(this.form.fields.findByName('exportCompaniesIds'), function (_el) {
                if (_el.fieldElement.checked) {
                    _that.choosedCount = _that._choosedCount + _el.fieldElement['getItemsCount']();
                    _that.form.fields.findByName('itemsCount').value = _that._choosedCount.toString();
                }
            });
        };
        Object.defineProperty(TExportSettings.prototype, "form", {
            get: function () {
                return this.element.querySelector('form');
            },
            enumerable: true,
            configurable: true
        });
        TExportSettings.prototype.switchInterval = function () {
            var iSel = this.intervalSelector.fieldElement;
            var seqSw = this.sequenceSwitcher.fieldElement;
            if (this.sheduleSwitcher.fieldElement.checked) {
                iSel.closest('.interval').classList.remove('hidden');
                iSel.disabled = false;
                seqSw.closest('.do-sequence').classList.add('hidden');
                seqSw.checked = false;
                seqSw.disabled = true;
            }
            else {
                iSel.closest('.interval').classList.add('hidden');
                iSel.disabled = true;
                seqSw.closest('.do-sequence').classList.remove('hidden');
                seqSw.disabled = false;
            }
        };
        Object.defineProperty(TExportSettings.prototype, "sequenceSwitcher", {
            get: function () {
                return this.form.fields.findByName('doSequence', 'checkbox');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "sheduleSwitcher", {
            get: function () {
                return this.form.fields.findByName('sheduleTransfer', 'checkbox');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "intervalSelector", {
            get: function () {
                return this.form.fields.findByName('transferInterval');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "limitIndicator", {
            get: function () {
                return this.form.fields.findByName('exportItemsLimit');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "choosedItemsIndicator", {
            get: function () {
                return this.element.querySelector('.choosed-items');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "submitButton", {
            get: function () {
                return this.form.querySelector('.sbmt');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "choosedCount", {
            set: function (val) {
                this._choosedCount = val;
                this.choosedItemsIndicator.innerHTML = this._choosedCount.toString();
                if (this._choosedCount > parseInt(this.limitIndicator.value)) {
                    this.overloaded = true;
                }
                else {
                    this.overloaded = false;
                }
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TExportSettings.prototype, "overloaded", {
            set: function (val) {
                if (val) {
                    this.element.classList.add(CED.consts.CLASS_OVERLOADER_CHOOSED_ITEMS);
                }
                else {
                    this.element.classList.remove(CED.consts.CLASS_OVERLOADER_CHOOSED_ITEMS);
                }
            },
            enumerable: true,
            configurable: true
        });
        return TExportSettings;
    }(mf.TBaseElement));
    CED.TExportSettings = TExportSettings;
})(CED || (CED = {}));
var CED;
(function (CED) {
    var TImportSettings = (function (_super) {
        __extends(TImportSettings, _super);
        function TImportSettings(options) {
            var _this = _super.call(this, options) || this;
            _this.switchInterval();
            return _this;
        }
        TImportSettings.prototype._initEvents = function () {
            var _that = this;
            try {
                this.sheduleSwitcher.fieldElement
                    .eventListener('change', function (ev) {
                    _that.switchInterval();
                });
                this.submitButton.eventListener('click', function () {
                    _that._submitForm();
                });
            }
            catch (err) { }
        };
        TImportSettings.prototype.switchInterval = function () {
            var iSel = this.intervalSelector.fieldElement;
            var seqSw = this.sequenceSwitcher.fieldElement;
            if (this.sheduleSwitcher.fieldElement.checked) {
                iSel.closest('.interval').classList.remove('hidden');
                iSel.disabled = false;
                seqSw.closest('.do-sequence').classList.add('hidden');
                seqSw.checked = false;
                seqSw.disabled = true;
            }
            else {
                iSel.closest('.interval').classList.add('hidden');
                iSel.disabled = true;
                seqSw.closest('.do-sequence').classList.remove('hidden');
                seqSw.disabled = false;
            }
        };
        TImportSettings.prototype._submitForm = function () {
            this.form.fields.findByName('stage').value = CED.consts.STAGE_POST.toString();
            this.form.submit();
        };
        Object.defineProperty(TImportSettings.prototype, "submitButton", {
            get: function () {
                return this.form.querySelector('.sbmt');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TImportSettings.prototype, "sequenceSwitcher", {
            get: function () {
                return this.form.fields.findByName('doSequence', 'checkbox');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TImportSettings.prototype, "sheduleSwitcher", {
            get: function () {
                return this.form.fields.findByName('sheduleTransfer', 'checkbox');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TImportSettings.prototype, "intervalSelector", {
            get: function () {
                return this.form.fields.findByName('transferInterval');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TImportSettings.prototype, "form", {
            get: function () {
                return this.element.querySelector('form');
            },
            enumerable: true,
            configurable: true
        });
        return TImportSettings;
    }(mf.TBaseElement));
    CED.TImportSettings = TImportSettings;
})(CED || (CED = {}));
var CED;
(function (CED) {
    var TTransferList = (function (_super) {
        __extends(TTransferList, _super);
        function TTransferList(options) {
            return _super.call(this, options) || this;
        }
        TTransferList.prototype._initEvents = function () {
            var _that = this;
            document.eventListener('click', function (ev) {
                ev.stopPropagation();
                if (ev.target.closest('.btn-view-settings')) {
                    _that.doViewSettings(ev);
                }
                if (ev.target.closest('.btn-view-import-settings')) {
                    _that.doViewImportSettings(ev);
                }
                if (ev.target.closest('.btn-view-links')) {
                    _that.doViewLinks(ev);
                }
                if (ev.target.closest('.btn-popup-log')) {
                    _that.doPopupLog(ev);
                }
                if (ev.target.closest('.btn-edit')) {
                    _that.doEdit(ev);
                }
                if (ev.target.closest('.btn-view-export')) {
                    _that.doViewExport(ev);
                }
                if (ev.target.closest('.btn.clipboard')) {
                    _that.doLinkToClipboard(ev);
                }
                if (ev.target.closest('.btn.download')) {
                    _that.doLinkDownload(ev);
                }
                if (ev.target.closest('.btn-view-import')) {
                    _that.doViewImport(ev);
                }
                if (ev.target.closest('.btn-remove')) {
                    _that.doRemoveItem(ev);
                }
            });
        };
        Object.defineProperty(TTransferList.prototype, "grid", {
            get: function () {
                return this.element.querySelector('.grid-view');
            },
            enumerable: true,
            configurable: true
        });
        TTransferList.prototype.doViewExport = function (ev) {
            var id = this.getRowID(ev.target);
            window.location.href = '/crosspost/transfer/view-export?id=' + id;
        };
        TTransferList.prototype.doViewImport = function (ev) {
            var id = this.getRowID(ev.target);
            window.location.href = '/crosspost/transfer/view-import?id=' + id;
        };
        TTransferList.prototype.doEdit = function (ev) {
            var id = this.getRowID(ev.target);
            window.location.href = '/crosspost/transfer/edit-transfer?id=' + id;
        };
        TTransferList.prototype.doLinkDownload = function (ev) {
            var input = ev.target.closest('.btn-row').previousElementSibling;
            window.open(input.value);
        };
        TTransferList.prototype.doLinkToClipboard = function (ev) {
            var input = ev.target.closest('.btn-row').previousElementSibling;
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');
        };
        TTransferList.prototype.doViewSettings = function (ev) {
            var id = this.getRowID(ev.target);
            var url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-settings-popup', { id: id });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        };
        TTransferList.prototype.doViewImportSettings = function (ev) {
            var id = this.getRowID(ev.target);
            var url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-import-settings-popup', { id: id });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        };
        TTransferList.prototype.doViewLinks = function (ev) {
            var id = this.getRowID(ev.target);
            var url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-links-popup', { id: id });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: false,
                    CSS_wrapClass: 'popup-wrap view-links',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        };
        TTransferList.prototype.doPopupLog = function (ev) {
            var id = this.getRowID(ev.target);
            var url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-popup-log', { id: id });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: false,
                    CSS_wrapClass: 'popup-wrap view-history',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        };
        TTransferList.prototype.doRemoveItem = function (ev) {
            var _that = this;
            mf.Confirm('Удаление', sprintf('Вы собираетесь удалить %s. Удаление повлечёт с собой удаление элемента из очереди, и очистку его логов. Действительно следует удалить?', ev.target.closest('tr').children[0].childNodes[1].textContent))
                .then(function () {
                $.ajax({
                    url: '/crosspost/transfer/remove-transfer',
                    data: { id: _that.getRowID(ev.target) },
                    method: 'post',
                    async: true,
                    success: function (data, status, xhr) {
                        console.log(_that);
                        if (data.code == TCedResultCode.SUCCESS) {
                            $(_that.element).yiiGridView('applyFilter');
                        }
                        else if (data.code == TCedResultCode.ERROR) {
                            mf.Alert('Удаление', data.message);
                        }
                    },
                    error: function (data, textStatus, errorThrown) {
                        mf.Alert('Удаление', data.responseJSON.message);
                    }
                });
            })
                .catch(function () { });
        };
        TTransferList.prototype.onHide = function (obj) {
            obj.initEvent.target.closest('a').classList.remove('popuped');
        };
        TTransferList.prototype.onShow = function (obj) {
            obj.initEvent.target.closest('a').classList.add('popuped');
        };
        TTransferList.prototype.getRowID = function (el) {
            try {
                return el.closest('tr').getAttribute('data-key');
            }
            catch (err) {
                console.error(err);
            }
        };
        return TTransferList;
    }(mf.TBaseElement));
    CED.TTransferList = TTransferList;
})(CED || (CED = {}));
var CED;
(function (CED) {
    var TCompaniesList = (function (_super) {
        __extends(TCompaniesList, _super);
        function TCompaniesList() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        TCompaniesList.prototype._initEvents = function () {
            var _that = this;
            document.eventListener('click', function (ev) {
                var _t = ev.target;
                if (_t.hasAttribute('data-transfer-id')) {
                    var id = _t.getAttribute('data-transfer-id');
                    var url = void 0, popup = void 0;
                    if (id) {
                        url = Objects.compileGetUrl('/crosspost/transfer/let-import-settings-popup', { id: id });
                        popup = new mf.TAjaxPopup({
                            initEvent: ev,
                            url: url,
                            showAfterLoad: true,
                            CSS_wrapClass: 'popup-wrap view-settings',
                        });
                    }
                }
                if (_t.closest('.btn-view-company')) {
                    var rowid = _that.getRowID(_t);
                    var url = void 0, popup = void 0;
                    if (rowid) {
                        url = Objects.compileGetUrl('/crosspost/transfer/let-company-view-popup', { id: rowid });
                        popup = new mf.TAjaxPopup({
                            initEvent: ev,
                            url: url,
                            showAfterLoad: false,
                            CSS_wrapClass: 'popup-wrap view-company',
                        });
                    }
                }
                if (_t.closest('.btn-create-import')) {
                    Objects.postData('/crosspost/transfer/create-import', {
                        id: _that.getRowID(_t),
                        _csrf: yii.getCsrfToken(),
                    }, mfRequestMethod.METHOD_POST);
                }
            });
        };
        TCompaniesList.prototype.getRowID = function (el) {
            try {
                return el.closest('tr').getAttribute('data-key');
            }
            catch (err) {
                console.error(err);
            }
        };
        return TCompaniesList;
    }(mf.TBaseElement));
    CED.TCompaniesList = TCompaniesList;
})(CED || (CED = {}));
var CED;
(function (CED) {
    var TSequenceList = (function (_super) {
        __extends(TSequenceList, _super);
        function TSequenceList(options) {
            return _super.call(this, options) || this;
        }
        TSequenceList.prototype._initEvents = function () {
            var _that = this;
            this.on('click', function (ev) {
                if (ev.target.closest('.btn-popup-transfert')) {
                    _that.popupTransfer(ev);
                }
            });
        };
        TSequenceList.prototype.popupTransfer = function (ev) {
            var rowid = this.gridObject.getRowID(ev.target);
            var url, popup;
            if (rowid) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-sequence-transfer-popup', { id: rowid });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                });
            }
        };
        Object.defineProperty(TSequenceList.prototype, "grid", {
            get: function () {
                return this._element.querySelector('[data-ancestor="' + CED.TmfGridView.ancestor + '"]');
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TSequenceList.prototype, "gridObject", {
            get: function () {
                return this.grid._getObj();
            },
            enumerable: true,
            configurable: true
        });
        return TSequenceList;
    }(mf.TBaseElement));
    CED.TSequenceList = TSequenceList;
})(CED || (CED = {}));
var CED;
(function (CED) {
    var TItemStat = (function (_super) {
        __extends(TItemStat, _super);
        function TItemStat(options) {
            return _super.call(this, options) || this;
        }
        return TItemStat;
    }(mf.TBaseElement));
    CED.TItemStat = TItemStat;
    var TSequenceGrid = (function (_super) {
        __extends(TSequenceGrid, _super);
        function TSequenceGrid(options) {
            var _this = _super.call(this, options) || this;
            [].map.call(_this.table.tBodies.item(0).rows, function (_tr) {
                _tr.draggable = true;
            });
            _this._doSequenceProgressData();
            return _this;
        }
        TSequenceGrid.prototype._initEvents = function () {
            var _that = this;
            document.eventListener('click', function (ev) {
                if (ev.target.closest('.save-start-time')) {
                    _that._doChangeTime(ev);
                }
            });
            this.on('click', function (ev) {
                if (ev.target.closest('.btn-popup-transfert')) {
                    _that.popupTransfer(ev);
                }
                if (ev.target.closest('.btn-start-time')) {
                    _that.popupChangeStartTime(ev);
                }
            });
            this.on('dragstart', function (ev) {
                ev.dataTransfer.effectAllowed = "move";
                _that._dragTR = ev.target;
            });
            this.on('dragend', function (ev) {
                _that._cleanRows();
            });
            this.on('dragover', function (ev) {
                ev.dataTransfer.effectAllowed = "move";
                _that._dropTR = ev.target.closest('tr');
                if (_that._dropTR) {
                    if (_that._dropTR == _that._dragTR) {
                        return false;
                    }
                    if (_that._dropTR.rowIndex == 1) {
                        _that._dropTR.table.tHead.rows.item(0).classList.add('over');
                    }
                    else if (_that._dropTR.rowIndex == 0) {
                        return false;
                    }
                    else {
                        _that._dropTR.previousElementSibling.classList.add('over');
                    }
                    ev.preventDefault();
                }
            });
            this.on('dragleave', function (ev) {
                var _overTR = ev.target.closest('tr');
                if (_overTR) {
                    if (_overTR.rowIndex == 1) {
                        _overTR.table.tHead.rows.item(0).classList.remove('over');
                    }
                    else {
                        _overTR.previousElementSibling.classList.remove('over');
                    }
                }
            });
            this.on('drop', function (ev) {
                var time;
                if (_that._dropTR.rowIndex == 1) {
                    time = 'now';
                }
                else {
                    return false;
                }
                fetch(Objects.compileGetUrl('/crosspost/transfer/save-sequence-item-start-time', { item: _that.getRowID(_that._dragTR), time: time }))
                    .then(function (resp) {
                    return resp.json();
                }).then(function (resp) {
                    if (resp.code == mfResponseCodes.RESULT_CODE_SUCCESS) {
                        toastr.success(resp.message);
                    }
                    else if (resp.code == mfResponseCodes.RESULT_CODE_ERROR) {
                        toastr.error(resp.message);
                    }
                    mf.TBasePopup.freePopups();
                });
                _that._cleanDrags();
            });
        };
        TSequenceGrid.prototype._cleanDrags = function () {
            this._dragTR = null;
            this._dropTR = null;
        };
        TSequenceGrid.prototype._cleanRows = function () {
            [].map.call(this.table.rows, function (_tr) {
                _tr.classList.remove('over');
            });
        };
        TSequenceGrid.prototype.popupTransfer = function (ev) {
            var rowid = this.getRowID(ev.target);
            var url, popup;
            if (rowid) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-sequence-transfer-popup', { id: rowid });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                });
            }
        };
        TSequenceGrid.prototype.popupChangeStartTime = function (ev) {
            var rowid = this.getRowID(ev.target);
            var url, popup;
            if (rowid) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-sequence-item-change-start-time-popup', { id: rowid });
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-begin-time',
                });
            }
        };
        TSequenceGrid.prototype._doChangeTime = function (ev) {
            var _that = this;
            var popup = ev.target.closest('.view-begin-time')._getObj();
            var itemID = popup.element.querySelector('[name="begin_time_item_id"]');
            var itemTime = popup.element.querySelector('[name="begin_time"]');
            fetch(Objects.compileGetUrl('/crosspost/transfer/save-sequence-item-start-time', { item: itemID.value, time: itemTime.value }))
                .then(function (resp) {
                return resp.json();
            }).then(function (resp) {
                if (resp.code == mfResponseCodes.RESULT_CODE_SUCCESS) {
                    toastr.success(resp.message);
                }
                else if (resp.code == mfResponseCodes.RESULT_CODE_ERROR) {
                    toastr.error(resp.message);
                }
                mf.TBasePopup.freePopups();
            });
        };
        TSequenceGrid.prototype._doSequenceProgressData = function () {
            var _that = this;
            if (this.sequenceRefresh) {
                this._sequenceRefreshInterval = setInterval(function () {
                    fetch(_that.sequenceRefreshUrl)
                        .then(function (resp) {
                        return resp.json();
                    }).then(function (resp) {
                        _that._doGridDataProgress(resp.data);
                    });
                }, this.sequenceRefresh * 1000);
            }
        };
        TSequenceGrid.prototype._doGridDataProgress = function (data) {
            for (var i = 0; i < data.length; i++) {
                this._doMoveRow(data[i].id, i + 1);
                var cell = this.findCellRow(data[i].id, '[role="progress"]');
                cell.innerHTML = data[i].pcaption;
                if (data[i].__stat) {
                    Html.createElementEx('div', cell, {}, data[i].__stat.crossposted + ' / ' + data[i].__stat.supposedNum);
                }
            }
        };
        TSequenceGrid.prototype._doMoveRow = function (rowID, idx) {
            var row = this.findRow(rowID);
            if (row.rowIndex != idx) {
                $(row).fadeOut();
                if (idx != 0) {
                    $(row).insertAfter(this.tbody.rows[idx - 1]);
                }
                else {
                    $(row).insertBefore(this.tbody.rows[0]);
                }
                $(row).fadeIn();
            }
        };
        return TSequenceGrid;
    }(CED.TmfGridView));
    CED.TSequenceGrid = TSequenceGrid;
})(CED || (CED = {}));
