module CED {
    export class TImportSettings extends mf.TBaseElement {

        constructor(options?) {
            super(options);
            this.switchInterval();
        }

        protected _initEvents() {
            let _that = this;
            try {
                this.sheduleSwitcher.fieldElement
                    .eventListener('change', function (ev: Event) {
                        _that.switchInterval();
                    });

                this.submitButton.eventListener('click', function () {
                    _that._submitForm();
                });
            } catch (err) {}
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

        protected _submitForm() {
            (this.form.fields.findByName('stage') as HTMLFormField).value = CED.consts.STAGE_POST.toString();
            this.form.submit();
        }

        get submitButton() {
            return this.form.querySelector('.sbmt') as HTMLButtonElement;
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

        get form() {
            return this.element.querySelector('form') as HTMLFormElement;
        }
    }
}

