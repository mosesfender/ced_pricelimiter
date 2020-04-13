module CED {

    export class TSequenceList extends mf.TBaseElement {
        constructor(options?) {
            super(options);
        }

        protected _initEvents() {
            let _that = this;
            this.on('click', function (ev: MouseEvent) {
                if ((ev.target as HTMLElement).closest('.btn-popup-transfert')) {
                    _that.popupTransfer(ev);
                }
            });
        }

        protected popupTransfer(ev: MouseEvent) {
            let rowid = this.gridObject.getRowID(ev.target as HTMLElement);
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

        get grid() {
            return this._element.querySelector('[data-ancestor="' + CED.TmfGridView.ancestor + '"]') as HTMLElement;
        }

        get gridObject() {
            return this.grid._getObj() as CED.TmfGridView;
        }
    }
}

