<?php include_once 'header.php'; ?>

<body data-controller="accountPayController">
<form method="post">
    <input id="input_stake" type="hidden" name="stake" value="100" />
    <table class="stacksTable">
        <tr>
            <td><a data-stake="100" class="button_tap weui_btn weui_btn_plain_primary">100 元</a></td>
            <td><a data-stake="200" class="button_tap weui_btn weui_btn_plain_default">200 元</a></td>
            <td><a data-stake="500" class="button_tap weui_btn weui_btn_plain_default">500 元</a></td>
        </tr>
        <tr>
            <td><a data-stake="1000" class="button_tap weui_btn weui_btn_plain_default">1000 元</a></td>
            <td><a data-stake="2000" class="button_tap weui_btn weui_btn_plain_default">2000 元</a></td>
            <td><a data-stake="5000" class="button_tap weui_btn weui_btn_plain_default">5000 元</a></td>
        </tr>
    </table>
    <div class="weui_cells_title">請選擇充值渠道</div>
    <div class="weui_cells weui_cells_radio">
        <label class="weui_cell weui_check_label" for="online">
            <div class="weui_cell_bd weui_cell_primary">
                <p>在线支付</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" value="online" checked="checked" class="weui_check" name="gateway" id="online">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="staff">
            <div class="weui_cell_bd weui_cell_primary">
                <p>人工充值</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" value="staff" class="weui_check" name="gateway" id="staff">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
    </div>

    <div class="weui_btn_area">
        <a href="javascript:app.instance.controller.clickedSubmit();" class="weui_btn weui_btn_primary">確認</a>
    </div>

</form>
<?php include_once 'footer.php'; ?>
