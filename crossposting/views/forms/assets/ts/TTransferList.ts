module CED {
    export class TTransferList extends mf.TBaseElement {
        constructor(options?) {
            super(options);
        }

        protected _initEvents() {
            let _that = this;
            document.eventListener('click', function (ev: MouseEvent) {
                ev.stopPropagation();
                if ((ev.target as HTMLElement).closest('.btn-view-settings')) {
                    _that.doViewSettings(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-view-import-settings')) {
                    _that.doViewImportSettings(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-view-links')) {
                    _that.doViewLinks(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-popup-log')) {
                    _that.doPopupLog(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-edit')) {
                    _that.doEdit(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-view-export')) {
                    _that.doViewExport(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn.clipboard')) {
                    _that.doLinkToClipboard(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn.download')) {
                    _that.doLinkDownload(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-view-import')) {
                    _that.doViewImport(ev);
                }
                if ((ev.target as HTMLElement).closest('.btn-remove')) {
                    _that.doRemoveItem(ev);
                }
            });
        }

        get grid() {
            return this.element.querySelector('.grid-view') as HTMLElement;
        }

        protected doViewExport(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            window.location.href = '/crosspost/transfer/view-export?id=' + id;
        }

        protected doViewImport(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            window.location.href = '/crosspost/transfer/view-import?id=' + id;
        }

        protected doEdit(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            window.location.href = '/crosspost/transfer/edit-transfer?id=' + id;
        }

        protected doLinkDownload(ev: MouseEvent) {
            let input = (ev.target as HTMLElement).closest('.btn-row').previousElementSibling as HTMLInputElement;
            window.open(input.value);
        }

        protected doLinkToClipboard(ev: MouseEvent) {
            let input = (ev.target as HTMLElement).closest('.btn-row').previousElementSibling as HTMLElement;
            (input as HTMLInputElement).select();
            (input as HTMLInputElement).setSelectionRange(0, 99999);
            document.execCommand('copy');
        }

        protected doViewSettings(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            let url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-settings-popup', {id: id});
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        }

        protected doViewImportSettings(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            let url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-import-settings-popup', {id: id});
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: true,
                    CSS_wrapClass: 'popup-wrap view-settings',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        }

        protected doViewLinks(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            let url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-links-popup', {id: id});
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: false,
                    CSS_wrapClass: 'popup-wrap view-links',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        }

        protected doPopupLog(ev: MouseEvent) {
            let id = this.getRowID(ev.target as HTMLElement);
            let url, popup;
            if (id) {
                url = Objects.compileGetUrl('/crosspost/transfer/let-popup-log', {id: id});
                popup = new mf.TAjaxPopup({
                    initEvent: ev,
                    url: url,
                    showAfterLoad: false,
                    CSS_wrapClass: 'popup-wrap view-history',
                    onShow: this.onShow,
                    onHide: this.onHide,
                });
            }
        }

        protected doRemoveItem(ev: MouseEvent) {
            let _that = this;
            mf.Confirm('Удаление',
                sprintf('Вы собираетесь удалить %s. Удаление повлечёт с собой удаление элемента из очереди, и очистку его логов. Действительно следует удалить?',
                    (ev.target as HTMLElement).closest('tr').children[0].childNodes[1].textContent))
                .then(function () {
                    $.ajax({
                        url: '/crosspost/transfer/remove-transfer',
                        data: {id: _that.getRowID(ev.target as HTMLElement)},
                        method: 'post',
                        async: true,
                        success: function (data: TCedResult, status, xhr: JQuery.jqXHR) {
                            console.log(_that);
                            if (data.code == TCedResultCode.SUCCESS) {
                                $(_that.element).yiiGridView('applyFilter');
                            } else if (data.code == TCedResultCode.ERROR) {
                                mf.Alert('Удаление', data.message);
                            }
                        },
                        error: function (data: JQuery.jqXHR, textStatus: string, errorThrown: string) {
                            mf.Alert('Удаление', (data.responseJSON as TCedException).message);
                        }
                    });
                })
                .catch(function () {});
        }

        protected onHide(obj: mf.TAjaxPopup) {
            (obj.initEvent.target as HTMLElement).closest('a').classList.remove('popuped');
        }

        protected onShow(obj: mf.TAjaxPopup) {
            (obj.initEvent.target as HTMLElement).closest('a').classList.add('popuped');
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