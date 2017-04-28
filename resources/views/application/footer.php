    <div id="doneToast" style="display: none;">
        <div class="weui_mask_transparent"></div>
        <div class="weui_toast">
            <i class="weui_icon_toast"></i>
            <p class="weui_toast_content">成功</p>
        </div>
    </div>
    <div id="contentToast" style="display: none;">
        <div class="weui_mask_transparent"></div>
        <div class="weui_toast">
            <p class="weui_toast_content"></p>
        </div>
    </div>
    <div id="loadingToast" class="weui_loading_toast" style="display: none;">
        <div class="weui_mask_transparent"></div>
        <div class="weui_toast">
            <div class="weui_loading">
                <div class="weui_loading_leaf weui_loading_leaf_0"></div>
                <div class="weui_loading_leaf weui_loading_leaf_1"></div>
                <div class="weui_loading_leaf weui_loading_leaf_2"></div>
                <div class="weui_loading_leaf weui_loading_leaf_3"></div>
                <div class="weui_loading_leaf weui_loading_leaf_4"></div>
                <div class="weui_loading_leaf weui_loading_leaf_5"></div>
                <div class="weui_loading_leaf weui_loading_leaf_6"></div>
                <div class="weui_loading_leaf weui_loading_leaf_7"></div>
                <div class="weui_loading_leaf weui_loading_leaf_8"></div>
                <div class="weui_loading_leaf weui_loading_leaf_9"></div>
                <div class="weui_loading_leaf weui_loading_leaf_10"></div>
                <div class="weui_loading_leaf weui_loading_leaf_11"></div>
            </div>
            <p class="weui_toast_content">請稍後</p>
        </div>
    </div>
    <script type="text/html" id="templet_dialog_alert">
        <div class="app_dialog weui_dialog_alert">
            <div class="weui_mask"></div>
            <div class="weui_dialog">
                <div class="weui_dialog_hd"><strong class="weui_dialog_title">#TITLE#</strong></div>
                <div class="weui_dialog_bd">#CONTENT#</div>
                <div class="weui_dialog_ft">
                    <a id="app_dialog_close" href="javascript:app.services.dialog.remove();" class="weui_btn_dialog primary">我知道了</a>
                </div>
            </div>
        </div>
    </script>
    <script type="text/html" id="templet_dialog_confirm">
        <div class="app_dialog weui_dialog_confirm">
            <div class="weui_mask"></div>
            <div class="weui_dialog">
                <div class="weui_dialog_hd"><strong class="weui_dialog_title">#TITLE#</strong></div>
                <div class="weui_dialog_bd">#CONTENT#</div>
                <div class="weui_dialog_ft">
                    <a id="app_dialog_close" href="javascript:app.services.dialog.remove();" class="weui_btn_dialog default">取消</a>
                    <a id="app_dialog_callback" class="weui_btn_dialog primary">确定</a>
                </div>
            </div>
        </div>
    </script>
</body>
</html>