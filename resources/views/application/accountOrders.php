<?php include_once 'header.php'; ?>

<body data-controller="accountOrdersController">

    <table border="0">
        <tbody>
        <?php if (count($orders) == 0) { ?>
            <tr>
                <td>暫時還沒有任何記錄</td>
            </tr>
        <?php } ?>
        <?php foreach ($orders as $order) { 
                $object = $order->object;
                $is_striked = 1;
                if ($order->striked_at == '0000-00-00 00:00:00') $is_striked = 0;
        ?>
            <tr class="orderDetail" style="border-left: 4px solid <?php
                if ($is_striked) {
                    if ($order->body_is_win == 1) echo '#F43530';
                    else if ($order->body_is_draw == 1) echo '#D0B628';
                    else echo '#04BE02';
                } else {
                    echo '#D0B628';
                }                                   
            ?>">
                <td>
                    <strong><span style="color: <?php
                if ($is_striked) {
                    if ($order->body_is_win == 1) echo '#F43530';
                    else if ($order->body_is_draw == 1) echo '#D0B628';
                    else echo '#04BE02';
                } else {
                    echo '#D0B628';
                }                                   
            ?>"><?php
                if ($is_striked) {
                    if ($order->body_is_win == 1) echo '盈利';
                    else if ($order->body_is_draw == 1) echo '平局';
                    else echo '亏损';
                } else {
                    echo '等待';
                }      

            ?></span> <?php echo $object->body_name; ?> <?php echo $order->body_time;?> 秒</strong>
                    <p>已于 <?php echo date('Y-m-d H:i:s', strtotime($order->created_at)); ?> 以 <?php echo $order->body_price_buying; ?> 買入 <?php echo $order->body_stake;?> 元 <?php echo $order->body_direction ? '看漲' : '看跌'; ?></p>
                    <?php if ($is_striked) { ?>
                    <p>已于 <?php echo date('Y-m-d H:i:s', strtotime($order->created_at) + intval($order->body_time)); ?> 以 <?php echo $order->body_price_striked; ?> 結算</p>
                    <?php } else { ?>
                    <p>将于 <?php echo date('Y-m-d H:i:s', strtotime($order->created_at) + intval($order->body_time)); ?> 結算</p>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <?php if($orders->hasMorePages()) { ?>
        <tfoot>
            <tr>
                <td>
                    <a href="<?php echo $orders->nextPageUrl();?>" class="weui_btn weui_btn_primary" style="margin: 20px auto; font-size: 14px; width: 200px;">下一页</a>
                </td>
            </tr>
        </tfoot>
        <?php } ?>
    </table>

<?php include_once 'footer.php'; ?>
