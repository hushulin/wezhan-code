<?php include_once 'header.php'; ?>

<body>
<form method="post">

    <div class="weui_cells weui_cells_access">
        <a class="weui_cell" href="/account/records">
            <div class="weui_cell_bd weui_cell_primary">
                <p>当前结余</p>
            </div>
            <div class="weui_cell_ft"><?php echo $user->body_balance; ?> CNY</div>
        </a>
        <a class="weui_cell" href="/account/withdraw/records">
            <div class="weui_cell_bd weui_cell_primary">
                <p>提现记录</p>
            </div>
            <div class="weui_cell_ft">
            </div>
        </a>
    </div>

    <div class="weui_cells weui_cells_form">
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">姓名</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <input name="name" class="weui_input" type="text" placeholder="银行开户名">
            </div>
        </div>
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">卡号</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <input name="number" class="weui_input" type="number" placeholder="银行卡卡号">
            </div>
        </div>
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">銀行</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <select name="bank" class="weui_select bank_select">
                    <option value="ccb">建设银行</option>
                    <option value="icbc">工商银行</option>
                    <option value="boc">中国银行</option>
                    <option value="abc">农业银行</option>
                    <option value="comm">交通银行</option>
                    <option value="spdb">浦发银行</option>
                    <option value="ecb">光大银行</option>
                    <option value="cmbc">民生银行</option>
                    <option value="cib">兴业银行</option>
                    <option value="cmb">招商银行</option>
                    <option value="psbc">邮政储蓄</option>
                </select>
            </div>
        </div>
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">网点</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <input name="deposit" class="weui_input" type="text" placeholder="银行开户网点名称">
            </div>
        </div>
        <div class="weui_cell">
            <div class="weui_cell_hd"><label class="weui_label">金额</label></div>
            <div class="weui_cell_bd weui_cell_primary">
                <input name="stake" class="weui_input" type="number" placeholder="最低提现金额 100 元">
            </div>
        </div>
    </div>

    <div class="weui_btn_area">
        <a href="javascript:document.getElementsByTagName('form')[0].submit();" class="weui_btn weui_btn_primary">确认</a>
    </div>

</form>

<?php include_once 'footer.php'; ?>
