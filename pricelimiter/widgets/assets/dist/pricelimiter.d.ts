interface HTMLTablePriceLimiterCell extends HTMLTableCellElement {
    value: number;
}
declare module CED {
    enum TGeonameFilterMode {
        MODE_LIKE = 1,
        MODE_BEFORE = 2,
        MODE_END = 3,
    }
    class TGeonameFilter extends mf.TBaseElement {
        grid: CED.TPriceLimiter;
        constructor(options?: any);
        protected doFilter(): boolean;
        protected _unhideRows(): void;
    }
    class TEditableCell extends mf.TBaseElement {
        private _keyDownEvent;
        private _keyUpEvent;
        private _focusEvent;
        private _changeEvent;
        protected _oldValue: any;
        grid: CED.TPriceLimiter;
        constructor(options: any);
        destroy(): this;
        protected _initEvents(): void;
        protected _innerVal(): void;
        readonly value: number;
        protected _checkChange(): void;
    }
    class TFixedBlock extends mf.TBaseElement {
        grid: CED.TPriceLimiter;
        constructor(options?: any);
        show(): void;
        hide(): void;
    }
    interface IPriceCoords {
        geonameid: number;
        proptype: string;
        value?: any;
    }
    class TPriceLimiter extends CED.TmfGridView {
        static ancestor: string;
        protected _geoFilter: CED.TGeonameFilter;
        protected _fixedBlock: CED.TFixedBlock;
        saveUrl: string;
        protected _table: HTMLTableElement;
        protected _focusedCell: HTMLTableCellElement;
        protected _selectedCell: CED.TEditableCell;
        bufferCell: HTMLTableCellElement;
        constructor(options?: any);
        protected _saveValue(cell: HTMLTablePriceLimiterCell): void;
        protected _getParams(cell: HTMLTablePriceLimiterCell): IPriceCoords;
        protected _initEvents(): void;
        protected runProcess(ev: MouseEvent): void;
        restoreFocus(): void;
        defaultFocus(): void;
        protected _getFirstVisibleRow(): HTMLTableRowElement;
        protected setFocusedCell(cell: HTMLTableCellElement): boolean;
        protected unsetFocusedCell(cell: HTMLTableCellElement): void;
        protected setSelectedCell(cell: HTMLTableCellElement): boolean;
        protected unsetSelectedCell(cell: HTMLTableCellElement): void;
        protected setSaveCell(cell: HTMLTableCellElement): void;
        protected setSavedCell(cell: HTMLTableCellElement): void;
        static createEditableCell(cell: HTMLTableCellElement): TEditableCell;
        protected tabs(): void;
        readonly table: HTMLTableElement;
        readonly filter: HTMLFormElement;
    }
}
