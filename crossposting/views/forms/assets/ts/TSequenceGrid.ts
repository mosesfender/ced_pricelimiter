module CED {
    export class TItemStat extends mf.TBaseElement {

        constructor(options?) {
            super(options);
        }
    }

    export interface IStatData {
        supposedNum: number,
        totalNum: number,
        crossposted: number,
    }

    export interface IProgressData {
        begin_at: number,
        created_at: number,
        diff: number,
        end_at: number,
        filename: string,
        id: number,
        shedule_interval: number,
        t: number,
        transfert_id: string,
        _flags: number,
        pcaption: string,
        __stat: IStatData
    }

    export class TSequenceGrid extends CED.TmfGridView {
        protected _sequenceRefreshInterval;
        public sequenceRefresh: number;
        public sequenceRefreshUrl: string;

        protected _dropTR: HTMLTableRowElement;
        protected _dragTR: HTMLTableRowElement;

        constructor(options?) {
            super(options);
            [].map.call(this.table.tBodies.item(0).rows, function (_tr: HTMLTableRowElement) {
                _tr.draggable = true;
            });
            this._doSequenceProgressData();
        }

        protected _initEvents() {
            let _that = this;
            document.eventListener('click', function (ev: MouseEvent) {
                if ((ev.target as HTMLElement).closest('.save-start-time')) {
                    _that._doChangeTime(ev);
                }
            });

            this.on('click', function (ev: MouseEvent) {
                if ((ev.target as HTMLElement).closest('.btn-popup-transfert')) {
                    _that.popupTransfer(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-start-time')) {
                    _that.popupChangeStartTime(ev);
                }
            });

            this.on('dragstart', function (ev: DragEvent) {
                ev.dataTransfer.effectAllowed = "move";
                _that._dragTR = ev.target as HTMLTableRowElement;
            });

            this.on('dragend', function (ev: DragEvent) {
                _that._cleanRows();
            });

            this.on('dragover', function (ev: DragEvent) {
                ev.dataTransfer.effectAllowed = "move";
                _that._dropTR = (ev.target as HTMLElement).closest('tr') as HTMLTableRowElement;
                if (_that._dropTR) {
                    if (_that._dropTR == _that._dragTR) {
                        return false;
                    }
                    if (_that._dropTR.rowIndex == 1) {
                        _that._dropTR.table.tHead.rows.item(0).classList.add('over');
                    } else if (_that._dropTR.rowIndex == 0) {
                        return false;
                    } else {
                        _that._dropTR.previousElementSibling.classList.add('over');
                    }
                    ev.preventDefault();
                }
            });
            this.on('dragleave', function (ev: DragEvent) {
                let _overTR = (ev.target as HTMLElement).closest('tr') as HTMLTableRowElement;
                if (_overTR) {
                    if (_overTR.rowIndex == 1) {
                        _overTR.table.tHead.rows.item(0).classList.remove('over');
                    } else {
                        _overTR.previousElementSibling.classList.remove('over');
                    }
                }
            });

            this.on('drop', function (ev: DragEvent) {
                let time;
                if (_that._dropTR.rowIndex == 1) {
                    time = 'now';
                } else {
                    return false;
                }
                fetch(Objects.compileGetUrl('/crosspost/transfer/save-sequence-item-start-time', {item: _that.getRowID(_that._dragTR), time: time}))
                    .then(function (resp: Response) {
                        return resp.json();
                    }).then(function (resp: mfResponse) {
                        if (resp.code == mfResponseCodes.RESULT_CODE_SUCCESS) {
                            toastr.success(resp.message);
                        } else if (resp.code == mfResponseCodes.RESULT_CODE_ERROR) {
                            toastr.error(resp.message);
                        }
                        mf.TBasePopup.freePopups();
                    });
                _that._cleanDrags();
            });
        }

        protected _cleanDrags() {
            this._dragTR = null;
            this._dropTR = null;
        }

        protected _cleanRows() {
            [].map.call(this.table.rows, function (_tr: HTMLTableRowElement) {
                _tr.classList.remove('over');
            });
        }

        protected popupTransfer(ev: MouseEvent) {
            let rowid = this.getRowID(ev.target as HTMLElement);
            let url, popup;
            if (rowid) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-sequence-transfer-popup', {id: rowid});
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                });
            }
        }

        protected popupChangeStartTime(ev: MouseEvent) {
            let rowid = this.getRowID(ev.target as HTMLElement);
            let url, popup;
            if (rowid) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-sequence-item-change-start-time-popup', {id: rowid});
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-begin-time',
                });
            }
        }

        protected _doChangeTime(ev: MouseEvent) {
            let _that = this;
            let popup = ((ev.target as HTMLElement).closest('.view-begin-time') as HTMLElement)._getObj() as mf.TAjaxPopup;
            let itemID = popup.element.querySelector('[name="begin_time_item_id"]') as HTMLInputElement;
            let itemTime = popup.element.querySelector('[name="begin_time"]') as HTMLInputElement;
            fetch(Objects.compileGetUrl('/crosspost/transfer/save-sequence-item-start-time', {item: itemID.value, time: itemTime.value}))
                .then(function (resp: Response) {
                    return resp.json();
                }).then(function (resp: mfResponse) {
                    if (resp.code == mfResponseCodes.RESULT_CODE_SUCCESS) {
                        toastr.success(resp.message);
                    } else if (resp.code == mfResponseCodes.RESULT_CODE_ERROR) {
                        toastr.error(resp.message);
                    }
                    mf.TBasePopup.freePopups();
                });
        }

        protected _doSequenceProgressData() {
            let _that = this;
            if (this.sequenceRefresh) {
                this._sequenceRefreshInterval = setInterval(function () {
                    fetch(_that.sequenceRefreshUrl)
                        .then(function (resp: Response) {
                            return resp.json();
                        }).then(function (resp: mfResponse) {
                            _that._doGridDataProgress(resp.data);
                        });
                }, this.sequenceRefresh * 1000);
            }
        }

        protected _doGridDataProgress(data: Array<CED.IProgressData>) {
            for (let i = 0; i < data.length; i++) {
                this._doMoveRow(data[i].id, i + 1);
                let cell = this.findCellRow(data[i].id, '[role="progress"]');
                cell.innerHTML = data[i].pcaption;
                if (data[i].__stat) {
                    Html.createElementEx('div', cell, {}, data[i].__stat.crossposted + ' / ' + data[i].__stat.supposedNum);
                }
            }
        }

        protected _doMoveRow(rowID: number, idx: number) {
            let row = this.findRow(rowID);
            if (row.rowIndex != idx) {
                $(row).fadeOut();
                if (idx != 0) {
                    $(row).insertAfter(this.tbody.rows[idx - 1]);
                } else {
                    $(row).insertBefore(this.tbody.rows[0]);
                }
                $(row).fadeIn();
            }
        }
    }
}