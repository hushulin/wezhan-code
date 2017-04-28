<?php include_once 'header.php'; ?>

<div class="head clearfix">
    <div class="left">
        <div class="wrapper">
            <i>用户</i>
            <span class="user_body_phone"><?php echo($user->body_phone); ?></span>
        </div>
    </div>
    <div class="right">
        <div class="wrapper">
            <i>可用余额</i>
            <span class="user_body_balance"><?php echo($user->body_balance); ?> CNY</span>
        </div>
    </div>
</div>

<div class="container orders">
    <table class="orders">
        <thead>
            <tr>
                <td width="50%">商品</td>
                <td>额度</td>
                <td>盈亏</td>
            </tr>
        </thead>
        <tbody>
            <tr data-id="<?php echo $item->id; ?>" class="clearLine">
                <td><?php echo $item->object->body_name; ?>
                    <span style="color: <?php echo $item->body_direction ? '#ed0000' : '#00ff0a'; ?>;"><?php echo $item->body_direction ? '看涨' : '看跌'; ?></span>
                </td>
                <td><?php echo intval($item->body_stake); ?></td>
                <td class="price <?php 
                    if ($item->body_is_win == 1) echo 'red';
                    else if ($item->body_is_draw == 1) echo '';
                    else echo 'green';
                ?>"><?php
                    if ($item->body_is_win == 1) echo $item->body_bonus;
                    else if ($item->body_is_draw == 1) echo '0';
                    else echo $item->body_stake;
                ?></td>
            </tr>
            <tr>
                <td colspan="3">
                    <p>时间: <?php echo date('Y-m-d H:i:s', strtotime($item->created_at)); ?>&nbsp;&nbsp;周期: <?php 
 if($item->body_time == 60) echo '1M';
 if($item->body_time == 300) echo '5M';
 if($item->body_time == 900) echo '15M';
 if($item->body_time == 1800) echo '30M';
 if($item->body_time == 3600) echo '1H';
 ?></p>
                    <p>收益率: <?php echo $item->object->body_profit * 100; ?>%</p>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <p style="margin-top: 12px;">开仓价格: <?php echo sprintf('%.' . $item->object->body_price_decimal . 'f', $item->body_price_buying); ?></p>
                    <p>结算价格: <span class="price_now"><?php echo sprintf('%.' . $item->object->body_price_decimal . 'f', $item->body_price_striked); ?></span></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php include_once 'footer.php'; ?>
