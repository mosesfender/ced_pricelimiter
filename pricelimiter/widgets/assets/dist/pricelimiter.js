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
var CED;
(function (CED) {
    var TGeonameFilterMode;
    (function (TGeonameFilterMode) {
        TGeonameFilterMode[TGeonameFilterMode["MODE_LIKE"] = 1] = "MODE_LIKE";
        TGeonameFilterMode[TGeonameFilterMode["MODE_BEFORE"] = 2] = "MODE_BEFORE";
        TGeonameFilterMode[TGeonameFilterMode["MODE_END"] = 3] = "MODE_END";
    })(TGeonameFilterMode = CED.TGeonameFilterMode || (CED.TGeonameFilterMode = {}));
    var TGeonameFilter = (function (_super) {
        __extends(TGeonameFilter, _super);
        function TGeonameFilter(options) {
            var _this = _super.call(this, options) || this;
            var _that = _this;
            _this.element = Html.createElementEx('textarea', _this.grid.table.getCell(0, 0), { rows: 1, spellcheck: false });
            _this.element.tabIndex = 0;
            _this.element.eventListener('click', function (ev) {
                _that.grid.table.blur();
                _that.element.focus();
            });
            _this.element.eventListener('keyup', function (ev) {
                ev.stopPropagation();
                ev.preventDefault();
                if (ev.ctrlKey && ev.keyCode == 81) {
                    _that.grid.restoreFocus();
                }
                switch (ev.keyCode) {
                    case 13:
                        ev.preventDefault();
                        break;
                    default:
                        _that.doFilter();
                }
            });
            return _this;
        }
        TGeonameFilter.prototype.doFilter = function () {
            var mode = CED.TGeonameFilterMode.MODE_BEFORE;
            var str = this.element.value;
            this._unhideRows();
            if (!str.length) {
                return false;
            }
            for (var r = 1; r < this.grid.table.rows.length; r++) {
                var cell = this.grid.table.rows[r].cells[0];
                switch (mode) {
                    case CED.TGeonameFilterMode.MODE_BEFORE:
                        if (cell.innerHTML.toLowerCase().substr(0, str.length) != str.toLowerCase()) {
                            cell.row.classList.add('hidden');
                        }
                        break;
                }
            }
        };
        TGeonameFilter.prototype._unhideRows = function () {
            for (var r = 1; r < this.grid.table.rows.length; r++) {
                this.grid.table.rows[r].classList.remove('hidden');
            }
        };
        return TGeonameFilter;
    }(mf.TBaseElement));
    CED.TGeonameFilter = TGeonameFilter;
    var TEditableCell = (function (_super) {
        __extends(TEditableCell, _super);
        function TEditableCell(options) {
            var _this = _super.call(this, options) || this;
            _this.element.classList.add('edited');
            _this.element.contentEditable = 'true';
            _this._oldValue = _this.element.value;
            var char = 1, sel, range;
            range = document.createRange();
            sel = window.getSelection();
            range.setStart(_this.element, 0);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            _this.element.focus();
            return _this;
        }
        TEditableCell.prototype.destroy = function () {
            this.element.classList.remove('edited');
            this.element.contentEditable = 'false';
            this.element.eventListener(this._keyDownEvent);
            this.element.eventListener(this._keyUpEvent);
            this.element.eventListener(this._focusEvent);
            this.element.eventListener(this._changeEvent);
            this.element.focus();
            delete this.element['__obj'];
            return this;
        };
        TEditableCell.prototype._initEvents = function () {
            var _that = this;
            this._keyUpEvent = this.element.eventListener('keyup', function (ev) {
                _that._innerVal();
            });
            this._keyDownEvent = this.element.eventListener('keydown', function (ev) {
                if ((ev.keyCode < 48 || ev.keyCode > 57) && (ev.keyCode < 96 || ev.keyCode > 105)
                    && (![13, 8, 46, 37, 38, 39, 40, 27].includes(ev.keyCode))) {
                    ev.preventDefault();
                    return false;
                }
                switch (ev.keyCode) {
                    case 13:
                        ev.stopPropagation();
                        ev.preventDefault();
                        if (_that.value != _that._oldValue) {
                            _that.element.stfire('changed', 'Event');
                        }
                        _that.destroy();
                        break;
                    case 27:
                        ev.stopPropagation();
                        ev.preventDefault();
                        _that.element.blur();
                        break;
                    case 37:
                    case 38:
                    case 39:
                    case 40:
                        ev.stopPropagation();
                        break;
                    default:
                        _that._innerVal();
                }
                return false;
            });
            this._focusEvent = this.element.eventListener('blur', function (ev) {
                if (_that.value != _that._oldValue && !mf.TConfirm.findModal()) {
                    mf.Confirm('Р—РЅР°С‡РµРЅРёРµ РёР·РјРµРЅРµРЅРѕ', 'РЎРѕС…СЂР°РЅРёС‚СЊ Р·РЅР°С‡РµРЅРёРµ?')
                        .then(function () {
                        _that.element.stfire('changed', 'Event');
                        return _that.destroy();
                    })
                        .catch(function () {
                        console.log(arguments);
                        _that.element.innerHTML = _that._oldValue;
                        return _that.destroy();
                    });
                }
                else {
                    _that.destroy();
                }
            });
            this._changeEvent = this.element.eventListener('change', function (ev) {
            });
        };
        TEditableCell.prototype._innerVal = function () {
            if (isNaN(parseInt(this.element.innerHTML)) || parseInt(this.element.innerHTML) == 0) {
                this.element.innerHTML = '';
            }
            if (this.element.innerHTML.substring(0, 1) == '0') {
                this.element.innerHTML = parseInt(this.element.innerHTML).toString();
            }
            this._checkChange();
        };
        Object.defineProperty(TEditableCell.prototype, "value", {
            get: function () {
                return this.element.value;
            },
            enumerable: true,
            configurable: true
        });
        TEditableCell.prototype._checkChange = function () {
            if (this.value != this._oldValue) {
                this.element.stfire('change', 'Event');
            }
        };
        return TEditableCell;
    }(mf.TBaseElement));
    CED.TEditableCell = TEditableCell;
    var TFixedBlock = (function (_super) {
        __extends(TFixedBlock, _super);
        function TFixedBlock(options) {
            var _this = _super.call(this, options) || this;
            _this.element = _this.grid.element.parentElement.parentElement.cloneNode(true);
            _this.element.classList.add('cloned');
            _this.element.querySelector('tbody').innerHTML = '';
            _this.grid.element.parentElement.parentElement.parentElement.appendChild(_this.element);
            return _this;
        }
        TFixedBlock.prototype.show = function () {
            this.element.style.display = 'block';
        };
        TFixedBlock.prototype.hide = function () {
            this.element.style.display = 'none';
        };
        return TFixedBlock;
    }(mf.TBaseElement));
    CED.TFixedBlock = TFixedBlock;
    var TPriceLimiter = (function (_super) {
        __extends(TPriceLimiter, _super);
        function TPriceLimiter(options) {
            var _this = _super.call(this, options) || this;
            _this.ancestor = CED.TPriceLimiter.ancestor;
            _this._geoFilter = new CED.TGeonameFilter({
                grid: _this,
            });
            _this.element.tabIndex = 0;
            _this.element.focus();
            _this.tabs();
            _this._fixedBlock = new CED.TFixedBlock({ grid: _this });
            _this.setFocusedCell(_this.table.getCell(1, 1));
            return _this;
        }
        TPriceLimiter.prototype._saveValue = function (cell) {
            var _that = this;
            var data = this._getParams(cell);
            data[yii.getCsrfParam()] = yii.getCsrfToken();
            _that.setSaveCell(cell);
            fetch(this.saveUrl, {
                method: mf.RequestMethods.POST,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(function (res) {
                return res.json();
            }).then(function (data) {
                _that.setSavedCell(cell);
            });
        };
        TPriceLimiter.prototype._getParams = function (cell) {
            var ret = {
                geonameid: Number(this.table.rows[cell.rowIndex].getAttribute('data-key')),
                proptype: this.table.rows[0].cells[cell.cellIndex].getAttribute('data-key'),
                value: cell.value
            };
            return ret;
        };
        TPriceLimiter.prototype._initEvents = function () {
            var _that = this;
            document.eventListener('scroll', function (ev) {
                var _r = _that.table.rows[0];
                var _o = _r.offsetFrom(_r.parentElement.parentElement.parentElement.parentElement.parentElement);
                var _b = _r.getBoundingClientRect();
                if (_b.top + _b.height < _o.topHeight) {
                    _that._fixedBlock.show();
                }
                else {
                    _that._fixedBlock.hide();
                }
            });
            this.on('changed', function (ev) {
                _that._saveValue(ev.target);
                _that.unsetSelectedCell(ev.target);
            });
            this.on('dblclick', function (ev) {
                var _el = ev.target.closest('td,th');
                if (_el instanceof HTMLTableCellElement) {
                    _that.setSelectedCell(_el);
                }
            });
            this.on('click', function (ev) {
                var _el = ev.target.closest('td,th');
                if (_el instanceof HTMLTableCellElement) {
                    _that.setFocusedCell(_el);
                }
                if (ev.target.closest('a[data-ancestor="run-process"]')) {
                    _that.runProcess(ev);
                }
            });
            this.on('keydown', function (ev) {
                var _el = ev.target.closest('td');
                switch (ev.keyCode) {
                    case 27:
                        _that.unsetSelectedCell(_el);
                        break;
                    case 36:
                        ev.preventDefault();
                        _that.setFocusedCell(_el.row.table.getCell(1, _el.cellIndex));
                        break;
                    case 35:
                        ev.preventDefault();
                        _that.setFocusedCell(_el.row.table.getCell(_el.row.table.rows.length - 1, _el.cellIndex));
                        break;
                    case 37:
                        ev.preventDefault();
                        _that.setFocusedCell(_el.neighbourCell('left'));
                        break;
                    case 38:
                        ev.preventDefault();
                        _that.setFocusedCell(_el.neighbourCell('top'));
                        break;
                    case 39:
                        ev.preventDefault();
                        _that.setFocusedCell(_el.neighbourCell('right'));
                        break;
                    case 40:
                        ev.preventDefault();
                        _that.setFocusedCell(_el.neighbourCell('bottom'));
                        break;
                    case 13:
                        ev.stopPropagation();
                        ev.preventDefault();
                        _that.setSelectedCell(_el);
                        break;
                    case 70:
                        if (ev.ctrlKey) {
                            ev.stopPropagation();
                            ev.preventDefault();
                            _that.bufferCell = _that._focusedCell;
                            _that._geoFilter.element.focus();
                        }
                        break;
                }
                return false;
            });
        };
        TPriceLimiter.prototype.runProcess = function (ev) {
            var geonameid = this.getRowID(ev.target);
            var geoname = ev.target.closest('td').innerText;
            mf.Confirm('Лимитатор цен', 'Вы собираетесь применить лимиты цен к региону «' + geoname + '». Действительно следует это сделать?')
                .then(function (res) {
                fetch('/pricelimiter/default/do-process-for-geoname', {
                    method: mf.RequestMethods.POST,
                    body: JSON.stringify({
                        geonameid: geonameid,
                        _csrf: yii.getCsrfToken()
                    }),
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                }).then(function (res) {
                    return res.json();
                }).then(function (data) {
                    switch (data.code) {
                        case mfResponseCodes.RESULT_CODE_ERROR:
                            mf.Alert(data.message, data.data);
                            break;
                        case mfResponseCodes.RESULT_CODE_SUCCESS:
                            mf.Success(data.message, data.data);
                            break;
                    }
                });
            })
                .catch(function (res) {
            });
        };
        TPriceLimiter.prototype.restoreFocus = function () {
            if (this.bufferCell.row.classList.contains('hidden')) {
                return this.defaultFocus();
            }
            this.setFocusedCell(this.bufferCell);
        };
        TPriceLimiter.prototype.defaultFocus = function () {
            this.setFocusedCell(this._getFirstVisibleRow().cells[1]);
        };
        TPriceLimiter.prototype._getFirstVisibleRow = function () {
            for (var r = 1; r < this.table.rows.length; r++) {
                if (!this.table.rows[r].classList.contains('hidden')) {
                    return this.table.rows[r];
                }
            }
        };
        TPriceLimiter.prototype.setFocusedCell = function (cell) {
            if (!cell) {
                return false;
            }
            if (cell.tagName == 'th' || cell.cellIndex == 0) {
                this.unsetFocusedCell(cell);
                return false;
            }
            this.unsetFocusedCell(cell);
            this._focusedCell = cell;
            this._focusedCell.focus();
            this._focusedCell.classList.add('sel');
        };
        TPriceLimiter.prototype.unsetFocusedCell = function (cell) {
            try {
                this._focusedCell.classList.remove('sel');
            }
            catch (err) {
            }
        };
        TPriceLimiter.prototype.setSelectedCell = function (cell) {
            if (cell.tagName == 'th' || cell.cellIndex == 0) {
                this.unsetSelectedCell(cell);
                return false;
            }
            this.unsetFocusedCell(cell);
            this.unsetSelectedCell(cell);
            this.setFocusedCell(cell);
            this._selectedCell = new CED.TEditableCell({ element: cell, grid: this });
        };
        TPriceLimiter.prototype.unsetSelectedCell = function (cell) {
            try {
                this._selectedCell.destroy();
            }
            catch (err) {
            }
        };
        TPriceLimiter.prototype.setSaveCell = function (cell) {
            cell.classList.add('save');
        };
        TPriceLimiter.prototype.setSavedCell = function (cell) {
            cell.classList.add('saved');
            cell.classList.remove('save');
            var int = setTimeout(function () {
                cell.classList.remove('saved');
                clearTimeout(int);
            }, 1000);
        };
        TPriceLimiter.createEditableCell = function (cell) {
            return new CED.TEditableCell({ element: cell });
        };
        TPriceLimiter.prototype.tabs = function () {
            var _that = this;
            var _l = _that.table.rows[0].cells.length;
            for (var i = 1; i < _l; i++) {
                _that.table.rows[0].cells[i].style.width = 80 / (_l - 1) + '%';
            }
            [].map.call(_that.element.querySelectorAll('td'), function (_el) {
                if (_el.cellIndex != 0) {
                    _el.tabIndex = -1;
                    if (!_el.hasOwnProperty('value')) {
                        Object.defineProperty(_el, 'value', {
                            get: function () {
                                try {
                                    return this.innerHTML;
                                }
                                catch (err) {
                                    console.error(err);
                                }
                            }
                        });
                    }
                    ;
                }
            });
        };
        Object.defineProperty(TPriceLimiter.prototype, "table", {
            get: function () {
                if (!this._table) {
                    this._table = this.element.querySelector('table');
                }
                return this._table;
            },
            enumerable: true,
            configurable: true
        });
        Object.defineProperty(TPriceLimiter.prototype, "filter", {
            get: function () {
                return document.querySelector(this.searchForm);
            },
            enumerable: true,
            configurable: true
        });
        TPriceLimiter.ancestor = 'LimiterGridView';
        return TPriceLimiter;
    }(CED.TmfGridView));
    CED.TPriceLimiter = TPriceLimiter;
})(CED || (CED = {}));
