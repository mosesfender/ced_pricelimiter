<?php
/* @var $model \common\models\CedPartners */
?> 

<div class="company-detail">
    <?= $this->render("@backend/views/partners/_partnerDetails",
            ["model" => $model]);
    ?>
</div>