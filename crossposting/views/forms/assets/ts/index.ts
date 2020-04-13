document.eventListener('DOMContentLoaded', function () {
    let esf = document.querySelector('.export-settings-form');
    if (esf) {
        new CED.TExportSettings({
            element: '.export-settings-form'
        });
    }

    let isf = document.querySelector('.import-settings-form');
    if (isf) {
        new CED.TImportSettings({
            element: '.import-settings-form'
        });
    }

//    let cti = document.querySelector('.ced-transfer-index');
//    if (cti) {
//        new CED.TTransferList({
//            element: '.export-settings-form'
//        });
//    }

    let clist = document.querySelector('#companies_grid');
    if (clist) {
        new CED.TCompaniesList({
            element: clist
        });
    }
});