declare module CED {
    enum consts {
        EVENT_CHANGE_EXPORT_LIST = "ev_CompanyListChanged",
        EVENT_OVERLOAD_CHOOSED_ITEMS = "ev_OverloadChoosedItems",
        EVENT_CHANGE_VIEW_MODE = "ev_ChangeViewMode",
        ATTR_INPUT_COUNT = "data-count",
        CLASS_OVERLOADER_CHOOSED_ITEMS = "overloaded",
        STAGE_PRE = 1,
        STAGE_POST = 2,
        STAGE_CHANGE_VIEWMODE = 4,
    }
    class TExportSettings extends mf.TBaseElement {
        protected _choosedCount: number;
        constructor(options: any);
        protected _initEvents(): void;
        protected _submitForm(): void;
        protected _changeViewMode(): void;
        protected _changeCountItems(): void;
        readonly form: HTMLFormElement;
        protected switchInterval(): void;
        readonly sequenceSwitcher: HTMLFormField;
        readonly sheduleSwitcher: HTMLFormField;
        readonly intervalSelector: HTMLFormField;
        readonly limitIndicator: HTMLFormField;
        readonly choosedItemsIndicator: HTMLSpanElement;
        readonly submitButton: HTMLButtonElement;
        choosedCount: number;
        overloaded: boolean;
    }
}
declare module CED {
    class TImportSettings extends mf.TBaseElement {
        constructor(options?: any);
        protected _initEvents(): void;
        protected switchInterval(): void;
        protected _submitForm(): void;
        readonly submitButton: HTMLButtonElement;
        readonly sequenceSwitcher: HTMLFormField;
        readonly sheduleSwitcher: HTMLFormField;
        readonly intervalSelector: HTMLFormField;
        readonly form: HTMLFormElement;
    }
}
declare module CED {
    class TTransferList extends mf.TBaseElement {
        constructor(options?: any);
        protected _initEvents(): void;
        readonly grid: HTMLElement;
        protected doViewExport(ev: MouseEvent): void;
        protected doViewImport(ev: MouseEvent): void;
        protected doEdit(ev: MouseEvent): void;
        protected doLinkDownload(ev: MouseEvent): void;
        protected doLinkToClipboard(ev: MouseEvent): void;
        protected doViewSettings(ev: MouseEvent): void;
        protected doViewImportSettings(ev: MouseEvent): void;
        protected doViewLinks(ev: MouseEvent): void;
        protected doPopupLog(ev: MouseEvent): void;
        protected doRemoveItem(ev: MouseEvent): void;
        protected onHide(obj: mf.TAjaxPopup): void;
        protected onShow(obj: mf.TAjaxPopup): void;
        protected getRowID(el: HTMLElement): string;
    }
}
declare module CED {
    class TCompaniesList extends mf.TBaseElement {
        protected _initEvents(): void;
        protected getRowID(el: HTMLElement): string;
    }
}
declare module CED {
    class TSequenceList extends mf.TBaseElement {
        constructor(options?: any);
        protected _initEvents(): void;
        protected popupTransfer(ev: MouseEvent): void;
        readonly grid: HTMLElement;
        readonly gridObject: TmfGridView;
    }
}
declare module CED {
    class TItemStat extends mf.TBaseElement {
        constructor(options?: any);
    }
    interface IStatData {
        supposedNum: number;
        totalNum: number;
        crossposted: number;
    }
    interface IProgressData {
        begin_at: number;
        created_at: number;
        diff: number;
        end_at: number;
        filename: string;
        id: number;
        shedule_interval: number;
        t: number;
        transfert_id: string;
        _flags: number;
        pcaption: string;
        __stat: IStatData;
    }
    class TSequenceGrid extends CED.TmfGridView {
        protected _sequenceRefreshInterval: any;
        sequenceRefresh: number;
        sequenceRefreshUrl: string;
        protected _dropTR: HTMLTableRowElement;
        protected _dragTR: HTMLTableRowElement;
        constructor(options?: any);
        protected _initEvents(): void;
        protected _cleanDrags(): void;
        protected _cleanRows(): void;
        protected popupTransfer(ev: MouseEvent): void;
        protected popupChangeStartTime(ev: MouseEvent): void;
        protected _doChangeTime(ev: MouseEvent): void;
        protected _doSequenceProgressData(): void;
        protected _doGridDataProgress(data: Array<CED.IProgressData>): void;
        protected _doMoveRow(rowID: number, idx: number): void;
    }
}
