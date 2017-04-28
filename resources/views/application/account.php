<?php include_once 'header.php'; ?>

<body>

    <div class="weui_cells">
        <div class="weui_cell">
            <div class="weui_cell_bd weui_cell_primary">
                <p>當前結餘</p>
            </div>
            <div class="weui_cell_ft"><?php echo $user->body_balance; ?> CNY</div>
        </div>
    </div>

    <div class="weui_cells weui_cells_access">
        <a class="weui_cell" href="/account/pay">
            <div class="weui_cell_bd weui_cell_primary">
                <p>我要充值</p>
            </div>
            <div class="weui_cell_ft">
            </div>
        </a>
        <a class="weui_cell" href="/account/withdraw">
            <div class="weui_cell_bd weui_cell_primary">
                <p>我要提現</p>
            </div>
            <div class="weui_cell_ft">
            </div>
        </a>
        <a class="weui_cell" href="/account/records">
            <div class="weui_cell_bd weui_cell_primary">
                <p>資金記錄</p>
            </div>
            <div class="weui_cell_ft">
            </div>
        </a>
        <a class="weui_cell" href="/account/orders">
            <div class="weui_cell_bd weui_cell_primary">
                <p>交易記錄</p>
            </div>
            <div class="weui_cell_ft">
            </div>
        </a>
    </div>

    <div class="weui_cells weui_cells_access">
        <a class="weui_cell" href="/account/expand/<?php echo $user->id; ?>">
            <div class="weui_cell_bd weui_cell_primary">
                <p>我的推廣二維碼分享頁面</p>
            </div>
            <div class="weui_cell_ft"></div>
        </a>
    </div>

    <div class="weui_cells">
        <div class="weui_cell">
            <div class="weui_cell_bd weui_cell_primary">
                <p>我已推廣用戶</p>
            </div>
            <div class="weui_cell_ft"><?php echo $count_refers; ?> 位</div>
        </div>
        <div class="weui_cell">
            <div class="weui_cell_bd weui_cell_primary">
                <p>我已獲得獎金</p>
            </div>
            <div class="weui_cell_ft"><?php echo $count_bonus; ?> 元</div>
        </div>
    </div>

<?php include_once 'footer.php'; ?>
