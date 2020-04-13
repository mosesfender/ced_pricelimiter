<?php

namespace common\modules\crossposting\widgets\LogGrid;

class Grid extends \common\widgets\mfGridView\MFGridView {

    public $layout     = "{items}";
    public $showHeader = false;
    public $options = [
        "class" => "log-grid",
    ];
}
