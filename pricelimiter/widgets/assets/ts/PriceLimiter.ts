declare interface HTMLTablePriceLimiterCell extends HTMLTableCellElement {
    value: number;
}

module CED {

    export enum TGeonameFilterMode {
        MODE_LIKE = 0x1,
        MODE_BEFORE = 0x2,
        MODE_END = 0x3,
    }

    export class TGeonameFilter extends mf.TBaseElement {
        public grid: CED.TPriceLimiter;

        constructor(options?) {
            super(options);
            let _that = this;
            this.element = Html.createElementEx('textarea', this.grid.table.getCell(0, 0), {rows: 1, spellcheck: false}) as HTMLInputElement;
            this.element.tabIndex = 0;
            this.element.eventListener('click', function (ev: KeyboardEvent) {
                _that.grid.table.blur();
                _that.element.focus();
            });
            this.element.eventListener('keyup', function (ev: KeyboardEvent) {
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
        }

        protected doFilter() {
            let mode = CED.TGeonameFilterMode.MODE_BEFORE;
            let str = (this.element as HTMLInputElement).value;
            this._unhideRows();
            if (!str.length) {
                return false;
            }

            for (let r = 1; r < this.grid.table.rows.length; r++) {
                let cell = this.grid.table.rows[r].cells[0];
                switch (mode) {
                    case CED.TGeonameFilterMode.MODE_BEFORE:
                        if (cell.innerHTML.toLowerCase().substr(0, str.length) != str.toLowerCase()) {
                            cell.row.classList.add('hidden');
                        }
                        break;
                }
            }
        }

        protected _unhideRows() {
            for (let r = 1; r < this.grid.table.rows.length; r++) {
                this.grid.table.rows[r].classList.remove('hidden');
            }
        }
    }

    export class TEditableCell extends mf.TBaseElement {

        private _keyDownEvent: KeyboardEvent;
        private _keyUpEvent: KeyboardEvent;
        private _focusEvent: FocusEvent;
        private _changeEvent: Event;

        protected _oldValue: any;
        public grid: CED.TPriceLimiter;

        constructor(options) {
            super(options);
            this.element.classList.add('edited');
            this.element.contentEditable = 'true';
            this._oldValue = (this.element as HTMLTablePriceLimiterCell).value;
            let char = 1, sel: Selection, range: Range;
            range = document.createRange();
            sel = window.getSelection();
            range.setStart(this.element, 0);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
            this.element.focus();
        }

        destroy() {
            this.element.classList.remove('edited');
            this.element.contentEditable = 'false';
            this.element.eventListener(this._keyDownEvent);
            this.element.eventListener(this._keyUpEvent);
            this.element.eventListener(this._focusEvent);
            this.element.eventListener(this._changeEvent);
            this.element.focus();
            delete this.element['__obj'];
            return this;
        }

        protected _initEvents() {
            let _that = this;
            this._keyUpEvent = this.element.eventListener('keyup', function (ev: KeyboardEvent) {
                _that._innerVal();
            });
            this._keyDownEvent = this.element.eventListener('keydown', function (ev: KeyboardEvent) {
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
            this._focusEvent = this.element.eventListener('blur', function (ev: FocusEvent) {
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
                } else {
                    _that.destroy();
                }
            });
            this._changeEvent = this.element.eventListener('change', function (ev) {
                //console.log(this);
            });
        }

        protected _innerVal() {
            if (isNaN(parseInt(this.element.innerHTML)) || parseInt(this.element.innerHTML) == 0) {
                this.element.innerHTML = '';
            }
            if (this.element.innerHTML.substring(0, 1) == '0') {
                this.element.innerHTML = parseInt(this.element.innerHTML).toString();
            }
            this._checkChange();
        }

        get value() {
            return (this.element as HTMLTablePriceLimiterCell).value;
        }

        protected _checkChange() {
            if (this.value != this._oldValue) {
                this.element.stfire('change', 'Event');
            }
        }

    }

    export class TFixedBlock extends mf.TBaseElement {
        public grid: CED.TPriceLimiter;
        constructor(options?) {
            super(options);
            this.element = this.grid.element.parentElement.parentElement.cloneNode(true) as HTMLElement;
            this.element.classList.add('cloned');
            this.element.querySelector('tbody').innerHTML = '';
            //this.element.removeChild(this.element.querySelector('[data-ancestor="mfAsidePanel"]'));
            this.grid.element.parentElement.parentElement.parentElement.appendChild(this.element);
        }

        public show() {
            this.element.style.display = 'block';
        }

        public hide() {
            this.element.style.display = 'none';
        }
    }

    export interface IPriceCoords {
        geonameid: number,
        proptype: string,
        value?: any
    }

    export class TPriceLimiter extends CED.TmfGridView {
        static ancestor: string = 'LimiterGridView';

        protected _geoFilter: CED.TGeonameFilter;
        protected _fixedBlock: CED.TFixedBlock;

        public saveUrl: string;
        protected _table: HTMLTableElement;

        protected _focusedCell: HTMLTableCellElement;
        protected _selectedCell: CED.TEditableCell;

        public bufferCell: HTMLTableCellElement;

        constructor(options?) {
            super(options);
            this.ancestor = CED.TPriceLimiter.ancestor;
            this._geoFilter = new CED.TGeonameFilter({
                grid: this,
            });
            this.element.tabIndex = 0;
            this.element.focus();
            this.tabs();
            this._fixedBlock = new CED.TFixedBlock({grid: this});
            this.setFocusedCell(this.table.getCell(1, 1));
        }

        protected _saveValue(cell: HTMLTablePriceLimiterCell) {
            let _that = this;
            let data: IPriceCoords = this._getParams(cell);
            data[yii.getCsrfParam()] = yii.getCsrfToken();
            _that.setSaveCell(cell);
            fetch(this.saveUrl, {
                method: mf.RequestMethods.POST,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(function (res: Response) {
                return res.json();
            }).then(function (data: mf.TResponse) {
                //cell.innerHTML
                _that.setSavedCell(cell);
            });
        }

        protected _getParams(cell: HTMLTablePriceLimiterCell) {
            let ret = <IPriceCoords> {
                geonameid: Number(this.table.rows[cell.rowIndex].getAttribute('data-key')),
                proptype: this.table.rows[0].cells[cell.cellIndex].getAttribute('data-key'),
                value: cell.value
            };
            return ret;
        }

        protected _initEvents() {
            let _that = this;

            document.eventListener('scroll', function (ev: Event) {
                let _r = _that.table.rows[0];
                let _o = _r.offsetFrom(_r.parentElement.parentElement.parentElement.parentElement.parentElement);
                let _b = _r.getBoundingClientRect();
                if (_b.top + _b.height < _o.topHeight) {
                    _that._fixedBlock.show();
                } else {
                    _that._fixedBlock.hide();
                }
            });

            this.on('changed', function (ev: Event) {
                _that._saveValue(ev.target as HTMLTablePriceLimiterCell);
                _that.unsetSelectedCell(ev.target as HTMLTableCellElement);
            });
            this.on('dblclick', function (ev: MouseEvent) {
                let _el = (ev.target as HTMLElement).closest('td,th');
                if (_el instanceof HTMLTableCellElement) {
                    _that.setSelectedCell(_el);
                }
            });
            this.on('click', function (ev: MouseEvent) {
                let _el = (ev.target as HTMLElement).closest('td,th');
                if (_el instanceof HTMLTableCellElement) {
                    _that.setFocusedCell(_el);
                }
                if ((ev.target as HTMLElement).closest('a[data-ancestor="run-process"]')) {
                    _that.runProcess(ev);
                }
            });
            this.on('keydown', function (ev: KeyboardEvent) {
                let _el = (ev.target as HTMLElement).closest('td') as HTMLTableCellElement;
                switch (ev.keyCode) {
                    case 27:
                        /* Escape */
                        _that.unsetSelectedCell(_el);
                        break;
                    case 36:
                        /* Home */
                        ev.preventDefault();
                        _that.setFocusedCell(_el.row.table.getCell(1, _el.cellIndex));
                        break;
                    case 35:
                        /* End */
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
                        /* Ctrl + f */
                        if (ev.ctrlKey) {
                            ev.stopPropagation();
                            ev.preventDefault();
                            _that.bufferCell = _that._focusedCell;
                            _that._geoFilter.element.focus();
                        }
                        break;
                }
                //                console.log(ev);
                return false;
            });
        }

        protected runProcess(ev: MouseEvent) {
            let geonameid = this.getRowID(ev.target as HTMLElement);
            let geoname = ((ev.target as HTMLElement).closest('td') as HTMLTableCellElement).innerText;
            mf.Confirm('Лимитатор цен', 'Вы собираетесь применить лимиты цен к региону «' + geoname + '». Действительно следует это сделать?')
                .then(function (res: mf.TConfirm) {
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
                    }).then(function (res: Response) {
                        return res.json();
                    }).then(function (data: mfResponse) {
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
                .catch(function (res: mf.TConfirm) {
                });
        }

        public restoreFocus() {
            if (this.bufferCell.row.classList.contains('hidden')) {
                return this.defaultFocus();
            }
            this.setFocusedCell(this.bufferCell);
        }

        public defaultFocus() {
            this.setFocusedCell(this._getFirstVisibleRow().cells[1]);
        }

        protected _getFirstVisibleRow() {
            for (let r = 1; r < this.table.rows.length; r++) {
                if (!this.table.rows[r].classList.contains('hidden')) {
                    return this.table.rows[r] as HTMLTableRowElement;
                }
            }
        }

        protected setFocusedCell(cell: HTMLTableCellElement) {
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
        }

        protected unsetFocusedCell(cell: HTMLTableCellElement) {
            try {
                this._focusedCell.classList.remove('sel');
            } catch (err) {

            }
        }

        protected setSelectedCell(cell: HTMLTableCellElement) {
            if (cell.tagName == 'th' || cell.cellIndex == 0) {
                this.unsetSelectedCell(cell);
                return false;
            }
            this.unsetFocusedCell(cell);
            this.unsetSelectedCell(cell);
            this.setFocusedCell(cell);
            this._selectedCell = new CED.TEditableCell({element: cell, grid: this});
        }

        protected unsetSelectedCell(cell: HTMLTableCellElement) {
            try {
                //console.log(cell, this._selectedCell);
                this._selectedCell.destroy();
            } catch (err) {

            }
        }

        protected setSaveCell(cell: HTMLTableCellElement) {
            cell.classList.add('save');
        }

        protected setSavedCell(cell: HTMLTableCellElement) {
            cell.classList.add('saved');
            cell.classList.remove('save');
            let int = setTimeout(function () {
                cell.classList.remove('saved');
                clearTimeout(int);
            }, 1000);
        }

        static createEditableCell(cell: HTMLTableCellElement) {
            return new CED.TEditableCell({element: cell});
        }

        protected tabs() {
            let _that = this;
            let _l = _that.table.rows[0].cells.length;
            for (let i = 1; i < _l; i++) {
                _that.table.rows[0].cells[i].style.width = 80 / (_l - 1) + '%';
            }
            [].map.call(_that.element.querySelectorAll('td'), function (_el: HTMLTableCellElement) {
                if (_el.cellIndex != 0) {
                    _el.tabIndex = -1;
                    if (!_el.hasOwnProperty('value')) {
                        Object.defineProperty(_el, 'value', {
                            get: function () {
                                try {
                                    return (this as HTMLElement).innerHTML;
                                } catch (err) {
                                    console.error(err);
                                }
                            }
                        });
                    };
                }
            });
        }

        get table() {
            if (!this._table) {
                this._table = this.element.querySelector('table') as HTMLTableElement;
            }
            return this._table;
        }

        get filter() {
            return document.querySelector(this.searchForm) as HTMLFormElement;
        }


    }
}

