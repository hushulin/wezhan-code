<?php include_once 'header.php'; ?>

<body data-controller="accountPayWechatController" data-id="<?php echo $payRequest; ?>">

    <div class="hd">
        <img class="qrcode" src="<?php echo $qr; ?>">
    </div>

    <div class="weui_cells">
        <div class="weui_cell">
            <div class="weui_cell_bd weui_cell_primary" style="text-align: center;">
                <p>请长按上方的二维码、点击识别图中二维码</p>
            </div>
        </div>
    </div>

<?php include_once 'footer.php'; ?>
