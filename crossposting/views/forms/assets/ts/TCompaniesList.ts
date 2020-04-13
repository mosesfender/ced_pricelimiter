module CED {
    export class TCompaniesList extends mf.TBaseElement {
        protected _initEvents() {
            let _that = this;
            document.eventListener('click', function (ev: MouseEvent) {
                let _t = ev.target as HTMLElement;
                if (_t.hasAttribute('data-transfer-id')) {
                    let id = _t.getAttribute('data-transfer-id');
                    let url, popup;
                    if (id) {
                        url = Objects.compileGetUrl('/crosspost/transfer/let-import-settings-popup', {id: id});
                        popup = new mf.TAjaxPopup({
                            initEvent: ev,
                            url: url,
                            showAfterLoad: true,
                            CSS_wrapClass: 'popup-wrap view-settings',
                        });
                    }
                }
                if (_t.closest('.btn-view-company')) {
                    let rowid = _that.getRowID(_t);
                    let url, popup;
                    if (rowid) {
                        url = Objects.compileGetUrl('/crosspost/transfer/let-company-view-popup', {id: rowid});
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
        }

        protected getRowID(el: HTMLElement) {
            try {
                return el.closest('tr').getAttribute('data-key');
            } catch (err) {
                console.error(err);
            }
        }
    }
}


