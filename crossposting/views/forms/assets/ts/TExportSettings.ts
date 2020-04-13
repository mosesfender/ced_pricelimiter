module CED {
    export enum consts {
        EVENT_CHANGE_EXPORT_LIST = 'ev_CompanyListChanged',
        EVENT_OVERLOAD_CHOOSED_ITEMS = 'ev_OverloadChoosedItems',
        EVENT_CHANGE_VIEW_MODE = 'ev_ChangeViewMode',
        ATTR_INPUT_COUNT = 'data-count',
        CLASS_OVERLOADER_CHOOSED_ITEMS = 'overloaded',

        STAGE_PRE = 0x1,
        STAGE_POST = 0x2,
        STAGE_CHANGE_VIEWMODE = 0x4,
    }

    export class TExportSettings extends mf.TBaseElement {
        protected _choosedCount: number;

        constructor(options) {
            super(options);
            Objects.setDefinition(this, '_choosedCount', 0);
            this._changeCountItems();
            this.switchInterval();
        }

        protected _initEvents() {
            let _that = this;
            [].map.call(this.form.fields.findByName('exportCompaniesIds'), function (_el: HTMLFormField) {
                _el.fieldElement['_change'] = _el.fieldElement.eventListener('change', function () {
                    _that.fire(CED.consts.EVENT_CHANGE_EXPORT_LIST);
                });
                _el.fieldElement['getItemsCount'] = function () {
                    return parseInt(this.parentElement.getAttribute(CED.consts.ATTR_INPUT_COUNT));
                }
            });
            [].map.call(this.form.fields.findByName('viewMode'), function (_el: HTMLFormField) {
                _el.fieldElement['_change'] = _el.fieldElement.eventListener('change', function () {
                    _that._changeViewMode();
                });
            });
            this.element[CED.consts.EVENT_CHANGE_EXPORT_LIST]
                = this.element.eventListener(CED.consts.EVENT_CHANGE_EXPORT_LIST, function (ev: Event) {
                    _that._changeCountItems();
                });

            try {
                this.sheduleSwitcher.fieldElement
                    .eventListener('change', function (ev: Event) {
                        _that.switchInterval();
                    });
            } catch (err) {}

            this.submitButton.eventListener('click', function () {
                _that._submitForm();
            });

            $(this.form).on('beforeSubmit', function () {
                (_that.form.fields.findByName('itemsCount') as HTMLFormField).value = _that._choosedCount.toString();
            });
        }

        protected _submitForm() {
            (this.form.fields.findByName('stage') as HTMLFormField).value = CED.consts.STAGE_POST.toString();
            this.form.submit();
        }

        protected _changeViewMode() {
            (this.form.fields.findByName('stage') as HTMLFormField).value = CED.consts.STAGE_CHANGE_VIEWMODE.toString();
            this.form.submit();
        }

        protected _changeCountItems() {
            let _that = this;
            _that.choosedCount = 0;
            [].map.call(this.form.fields.findByName('exportCompaniesIds'), function (_el: HTMLFormField) {
                if ((_el.fieldElement as HTMLInputElement).checked) {
                    _that.choosedCount = _that._choosedCount + _el.fieldElement['getItemsCount']();
                    (_that.form.fields.findByName('itemsCount') as HTMLFormField).value = _that._choosedCount.toString();
                }
            });
        }

        get form() {
            return this.element.querySelector('form') as HTMLFormElement;
        }

        protected switchInterval() {
            let iSel = this.intervalSelector.fieldElement;
            let seqSw = this.sequenceSwitcher.fieldElement as HTMLInputElement;
            if ((this.sheduleSwitcher.fieldElement as HTMLInputElement).checked) {
                iSel.closest('.interval').classList.remove('hidden');
                iSel.disabled = false;
                seqSw.closest('.do-sequence').classList.add('hidden');
                seqSw.checked = false;
                seqSw.disabled = true;
            } else {
                iSel.closest('.interval').classList.add('hidden');
                iSel.disabled = true;
                seqSw.closest('.do-sequence').classList.remove('hidden');
                seqSw.disabled = false;
            }
        }

        get sequenceSwitcher() {
            return this.form.fields.findByName('doSequence', 'checkbox') as HTMLFormField;
        }

        get sheduleSwitcher() {
            return this.form.fields.findByName('sheduleTransfer', 'checkbox') as HTMLFormField;
        }

        get intervalSelector() {
            return this.form.fields.findByName('transferInterval') as HTMLFormField;
        }

        get limitIndicator() {
            return this.form.fields.findByName('exportItemsLimit') as HTMLFormField;
        }

        get choosedItemsIndicator() {
            return this.element.querySelector('.choosed-items') as HTMLSpanElement;
        }

        get submitButton() {
            return this.form.querySelector('.sbmt') as HTMLButtonElement;
        }

        set choosedCount(val: number) {
            this._choosedCount = val;
            this.choosedItemsIndicator.innerHTML = this._choosedCount.toString();
            if (this._choosedCount > parseInt(this.limitIndicator.value)) {
                this.overloaded = true;
            } else {
                this.overloaded = false;
            }
        }

        set overloaded(val: boolean) {
            if (val) {
                this.element.classList.add(CED.consts.CLASS_OVERLOADER_CHOOSED_ITEMS);
            } else {
                this.element.classList.remove(CED.consts.CLASS_OVERLOADER_CHOOSED_ITEMS);
            }
        }
    }
}


