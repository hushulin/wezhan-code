<?php include_once 'header.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row" style="margin-top: 20px;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>回复工具</th>
                                    <th>回复号码</th>
                                    <th>反馈内容</th>
                                    <th>创建时间</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php if (count($datas) == 0) { ?>
                                    <tr>
                                        <td colspan="5">暫時還沒有任何記錄</td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($datas as $item) { ?>
                                <tr>
                                    <td><?php echo $item->id; ?></td>
                                    <td><?php 
                                        if($item->body_tool == 'QQ') echo 'QQ';
                                        if($item->body_tool == 'WECHAT') echo '微信';
                                        if($item->body_tool == 'MOBILE') echo '手机';
                                    ?></td>
                                    <td><?php echo $item->body_number; ?></td>
                                    <td><?php echo $item->body_content	; ?></td>
                                    <td><?php echo $item->created_at; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" style="text-align: center;">
                                        <ul class="pagination">
                                            <li class="paginate_button previous"><a href="<?php echo $datas->previousPageUrl(); ?>">上一页</a></li>
                                            <li class="paginate_button active"><a href="#"><?php echo $datas->currentPage(); ?> / <?php echo $datas->lastPage(); ?>, 共 <?php echo $datas->total(); ?> 条记录</a></li>
                                            <li class="paginate_button next"><a href="<?php echo $datas->nextPageUrl(); ?>">下一页</a></li>
                                        </ul>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<?php include_once 'footer.php'; ?>