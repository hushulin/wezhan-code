<?php include_once 'header.php'; ?>

<body>

    <table border="0" class="evenColor">
        <thead>
            <tr>
                <th>提现日期</th>
                <th>提现金额</th>
                <th>收款姓名</th>
                <th>处理状态</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($withdrawRequests) == 0) { ?>
            <tr>
                <td colspan="4">暂时还没有任何记录</td>
            </tr>
        <?php } ?>
        <?php foreach ($withdrawRequests as $withdrawRequest) { ?>
            <tr>
                <td><?php echo date('Y-m-d', strtotime($withdrawRequest->created_at)); ?></td>
                <td><?php echo $withdrawRequest->body_stake; ?> 元</td>
                <td><?php echo $withdrawRequest->body_name; ?></td>
                <td><?php 
                    if($withdrawRequest->body_transfer_number == 'PENDING'){
                        echo '正在处理';
                    } else if ($withdrawRequest->body_transfer_number == 'FAIL') {
                        echo '处理失败';
                    } else {
                        echo '处理成功';
                    }
                ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php include_once 'footer.php'; ?>
