Date.prototype.format = function (format) {
    var date = {
        "M+": this.getMonth() + 1,
        "d+": this.getDate(),
        "h+": this.getHours(),
        "m+": this.getMinutes(),
        "s+": this.getSeconds(),
        "q+": Math.floor((this.getMonth() + 3) / 3),
        "S+": this.getMilliseconds()
    };
    if (/(y+)/i.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
    }
    for (var k in date) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1
                            ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
        }
    }
    return format;
};

window.app = {
    instance: {
        body: null,
        controller: null
    },
    services: {
        api: function ($method, $url, $params, $callback_success, $callback_error) {
            return $.ajax({
                type: $method, cache: false, data: $params, url: '/api/' + $url,
                error: $callback_error,
                success: $callback_success
            });
        },
        dialog: {
            alert: function ($title, $content) {
                var html = $('#templet_dialog_alert').html();
                html = html.replace(/#TITLE#/, $title);
                html = html.replace(/#CONTENT#/, $content);
                app.instance.body.append(html);
            },
            confirm: function ($title, $content, $callback) {
                var html = $('#templet_dialog_confirm').html();
                html = html.replace(/#TITLE#/, $title);
                html = html.replace(/#CONTENT#/, $content);
                app.instance.body.append(html);
                $('a#app_dialog_callback').one('click', $callback);
            },
            toast: function ($content) {
                $('#contentToast p.weui_toast_content').html($content);
                $('#contentToast').show('fast');
                setTimeout(function () {
                    $('#contentToast').hide('fast');
                }, 2000);
            },
            remove: function () {
                $('div.app_dialog').remove();
            }
        }
    },
    controllers: {
        appController: {
            $liveRequest: null,
            $liveObjectID: 0,
            $liveObject: null,
            $liveData: [],
            $liveDataInterval: null,
            $liveChartMode: 'fs',
            $liveChartData: [],
            $liveChartDataMin: 0,
            $liveChartDataMax: 0,
            $liveChart: null,
            $liveBalance: 0,
            $orderStake: 50,
            $orderTime: 60,
            init: function () {
                this.instance.controller.$liveObjectID = $('select#select_objects').val();
                this.instance.body.on('touchend', 'a#app_dialog_close', this.services.dialog.remove);
                this.instance.body.on('change', 'select#select_objects', this.instance.controller.initLiveObject);
                this.instance.body.on('touchstart', 'a.action_button', this.instance.controller.renderButtonOnHover);
                this.instance.body.on('touchend', 'a.action_button', this.instance.controller.renderButtonClearHover);
                this.instance.body.on('touchend', 'a#stake_minus', this.instance.controller.clickedStakeMinus);
                this.instance.body.on('touchend', 'a#stake_plus', this.instance.controller.clickedStakePlus);
                this.instance.body.on('touchend', 'a#time_minus', this.instance.controller.clickedTimeMinus);
                this.instance.body.on('touchend', 'a#time_plus', this.instance.controller.clickedTimePlus);
                this.instance.body.on('touchend', 'a#order_up', this.instance.controller.clickedOrderUp);
                this.instance.body.on('touchend', 'a#order_down', this.instance.controller.clickedOrderDown);
                this.instance.body.on('touchend', 'div#actions .center', this.instance.controller.clickedBalance);
                this.instance.body.on('touchend', 'div#liveChartButton', this.instance.controller.clickedLiveChartButton);
                this.instance.controller.initLiveChart();
                this.instance.controller.initFetchLiveData();
                $('#liveChartButton').html('分时');
            },
            initLiveChart: function () {

                Highcharts.setOptions({ global: { useUTC: true} });

                if (app.instance.controller.$liveChartMode == 'fs') {

                    app.instance.controller.$liveChart = new Highcharts.StockChart({
                        chart: {
                            animation: false, renderTo: 'liveChart', type: 'area', margin: [0, 0, 0, 0], backgroundColor: '#fff',
                            zoomType: 'none', panning: false, pinchType: 'none',
                            events: {
                                load: function () {
                                    setInterval(function () {
                                        app.instance.controller.initLiveChartData();
                                    }, 1000);
                                }
                            }
                        },
                        credits: { enabled: false },
                        plotOptions: {
                            series: {
                                color: '#4b7d18',
                                fillColor: {
                                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                                    stops: [
                                        [0, 'rgba(172, 210, 182, 0.8)'],
                                        [1, 'rgba(172, 210, 182, 0.2)'],
                                    ]
                                },
                                marker: {
                                    radius: 0
                                },
                                lineWidth: 1,
                                states: {
                                    hover: {
                                        lineWidth: 1
                                    }
                                }
                            }
                        },
                        tooltip: { enabled: false },
                        legend: { enabled: false },
                        exporting: { enabled: false },
                        rangeSelector: { enabled: false },
                        navigator: { enabled: false },
                        scrollbar: { enabled: false },
                        xAxis: {
                            type: 'datetime',
                            tickColor: 'rgba(255, 255, 255, 0)',
                            gridLineWidth: 1,
                            gridLineColor: '#e5e5e5',
                            lineColor: 'rgba(255, 255, 255, 0)',
                            tickAmount: 6,
                            labels: {
                                y: -5,
                                style: {
                                    'font-size': '10px',
                                    'color': '#616262',
                                    'font-family': 'Microsoft Yahei, Helvetica, Tahoma'
                                }
                            }
                        },
                        yAxis: {
                            title: { text: null },
                            showFirstLabel: false,
                            showLastLabel: false,
                            gridLineColor: '#e5e5e5',
                            lineColor: 'rgba(255, 255, 255, 0)',
                            opposite: false,
                            tickAmount: 6,
                            labels: {
                                align: 'left', x: 5, y: 4,
                                useHTML: true,
                                style: {
                                    'font-size': '10px',
                                    'color': '#616262',
                                    'font-family': 'Microsoft Yahei, Helvetica, Tahoma'
                                }
                            }
                        },
                        series: [{
                            data: [],
                            threshold: null
                        }]
                    });

                } else {

                    app.instance.controller.$liveChart = new Highcharts.StockChart({
                        chart: {
                            animation: false, renderTo: 'liveChart', type: 'candlestick', margin: [0, 0, 0, 0], backgroundColor: '#fff',
                            zoomType: 'none', panning: false, pinchType: 'none',
                            events: {
                                load: function () {
                                    setInterval(function () {
                                        app.instance.controller.initLiveChartData();
                                    }, 1000);
                                }
                            }
                        },
                        xAxis: {
                            type: 'datetime',
                            tickColor: 'rgba(255, 255, 255, 0)',
                            gridLineWidth: 1,
                            gridLineColor: '#e5e5e5',
                            lineColor: 'rgba(255, 255, 255, 0)',
                            tickAmount: 6,
                            labels: {
                                y: -5,
                                style: {
                                    'font-size': '10px',
                                    'color': '#616262',
                                    'font-family': 'Microsoft Yahei, Helvetica, Tahoma'
                                }
                            }
                        },
                        yAxis: {
                            title: { text: null },
                            showFirstLabel: false,
                            showLastLabel: false,
                            gridLineColor: '#e5e5e5',
                            lineColor: 'rgba(255, 255, 255, 0)',
                            opposite: false,
                            tickAmount: 6,
                            labels: {
                                align: 'left', x: 5, y: 4,
                                useHTML: true,
                                style: {
                                    'font-size': '10px',
                                    'color': '#616262',
                                    'font-family': 'Microsoft Yahei, Helvetica, Tahoma'
                                }
                            }
                        },
                        credits: { enabled: false },
                        tooltip: {
            	            followTouchMove: true
                        },
                        legend: { enabled: false },
                        exporting: { enabled: false },
                        rangeSelector: { enabled: false },
                        navigator: { enabled: false },
                        scrollbar: { enabled: false },
                        series: [{
                            name: app.instance.controller.$liveObject.body_name,
                            data: [],
                            threshold: null
                        }]
                    });

                }

            },
            initFetchLiveData: function () {
                app.instance.controller.$liveDataInterval = setInterval(app.instance.controller.fetchLiveData, 1000);
            },
            initLiveChartData: function () {

                if (!app.instance.controller.$liveObject) return false;
                if (!app.instance.controller.$liveChart) return false;

                app.instance.controller.$liveChartData = [];

                if (app.instance.controller.$liveChartMode == 'fs') {

                    app.instance.controller.$liveChartDataMin = 0;
                    app.instance.controller.$liveChartDataMax = 0;

                    for (var $index = 0; $index < app.instance.controller.$liveObject.prices.length; $index++) {

                        if (app.instance.controller.$liveChartDataMin == 0 || app.instance.controller.$liveChartDataMin > parseFloat(app.instance.controller.$liveObject.prices[$index].body_price)) {
                            app.instance.controller.$liveChartDataMin = parseFloat(app.instance.controller.$liveObject.prices[$index].body_price);
                        }

                        if (app.instance.controller.$liveChartDataMax == 0 || app.instance.controller.$liveChartDataMax < parseFloat(app.instance.controller.$liveObject.prices[$index].body_price)) {
                            app.instance.controller.$liveChartDataMax = parseFloat(app.instance.controller.$liveObject.prices[$index].body_price);
                        }
                        app.instance.controller.$liveChartData.push([
                            (new Date(app.instance.controller.$liveObject.prices[$index].created_at.replace(/-/g, '/'))).getTime(),
                            parseFloat(app.instance.controller.$liveObject.prices[$index].body_price)
                        ]);

                    }

                    app.instance.controller.$liveChartData.reverse();
                    app.instance.controller.$liveChart.yAxis[0].setExtremes(app.instance.controller.$liveChartDataMin - Math.abs(app.instance.controller.$liveObject.body_price_interval) * 0.15, app.instance.controller.$liveChartDataMax + Math.abs(app.instance.controller.$liveObject.body_price_interval) * 0.15);

                } else {

                    for (var $index = 0; $index < app.instance.controller.$liveObject.prices.length; $index++) {
                        app.instance.controller.$liveChartData.push([
                            (new Date(app.instance.controller.$liveObject.prices[$index].d.replace(/-/g, '/'))).getTime(),
                            parseFloat(app.instance.controller.$liveObject.prices[$index].o),
                            parseFloat(app.instance.controller.$liveObject.prices[$index].h),
                            parseFloat(app.instance.controller.$liveObject.prices[$index].l),
                            parseFloat(app.instance.controller.$liveObject.prices[$index].c)
                        ]);
                    }

                }

                app.instance.controller.$liveChart.series[0].update({
                    name: app.instance.controller.$liveObject.body_name,
                    data: app.instance.controller.$liveChartData
                });
                app.instance.controller.$liveChart.yAxis[0].update({
                    labels: {
                        formatter: function () {
                            return parseFloat(this.value).toFixed(app.instance.controller.$liveObject.body_price_decimal);
                        }
                    }
                });
                app.instance.controller.$liveChart.xAxis[0].removePlotLine('orders');

                app.instance.controller.$liveChart.setTitle({
                    y: -10,
                    verticalAlign: 'bottom',
                    text: (new Date(parseInt(app.instance.controller.$liveData.timestamp) * 1000)).toUTCString(),
                    style: {
                        'color': '#616262',
                        'font-size': '10px',
                        'font-weight': 'normal',
                        'font-family': 'Microsoft Yahei, Helvetica, Tahoma'
                    }
                });
                app.instance.controller.$liveChart.yAxis[0].removePlotLine('currentPrice');

                if (app.instance.controller.$liveObject.prices.length > 1) {
                    app.instance.controller.$liveChart.yAxis[0].addPlotLine({
                        id: 'currentPrice',
                        value: parseFloat(app.instance.controller.$liveObject.body_price),
                        color: '#111',
                        dashStyle: 'shortdash',
                        width: 1,
                        zIndex: 900,
                        label: {
                            y: -8,
                            x: 0,
                            text: parseFloat(app.instance.controller.$liveObject.body_price),
                            align: 'center',
                            useHTML: true,
                            style: {
                                'font-size': '10px',
                                'font-family': 'Microsoft Yahei, Helvetica, Tahoma',
                                'background': '#0f7427',
                                'color': '#fff',
                                'border-radius': '4px 4px 0 0 ',
                                'padding': '3px 8px 2px 9px'
                            }
                        }
                    });
                }

                for (var $index = 0; $index < app.instance.controller.$liveObject.orders.length; $index++) {
                    var $labelText = (app.instance.controller.$liveObject.orders[$index].body_direction == 1) ? '買漲' : '買跌';
                    var $orderCreatedTime = (new Date(app.instance.controller.$liveObject.orders[$index].created_at.replace(/-/g, '/'))).getTime();
                    var $orderStrikedTime = $orderCreatedTime / 1000 + parseInt(app.instance.controller.$liveObject.orders[$index].body_time);
                    var $orderStrikedClock = Math.abs(parseInt(app.instance.controller.$liveData.timestamp) - $orderStrikedTime);
                    app.instance.controller.$liveChart.xAxis[0].addPlotLine({
                        id: 'orders',
                        value: (new Date(app.instance.controller.$liveObject.orders[$index].created_at.replace(/-/g, '/'))).getTime(),
                        color: (app.instance.controller.$liveObject.orders[$index].body_direction == 1) ? '#bb4336' : '#4b7d18',
                        dashStyle: 'shortdash',
                        width: 1,
                        zIndex: 800,
                        label: {
                            text: '下单 ' + parseFloat(app.instance.controller.$liveObject.orders[$index].body_price_buying).toFixed(app.instance.controller.$liveObject.body_price_decimal) + ' 结算 ' + $orderStrikedClock + ' 秒',
                            x: -15,
                            style: {
                                'font-size': '10px',
                                'font-family': 'Microsoft Yahei, Helvetica, Tahoma',
                                'color': (app.instance.controller.$liveObject.orders[$index].body_direction == 1) ? '#bb4336' : '#4b7d18'
                            }
                        }
                    });
                }

                app.instance.controller.renderLoading(false);

            },
            initLiveObject: function () {
                if (app.instance.controller.$liveRequest != null) app.instance.controller.$liveRequest.abort();
                if (app.instance.controller.$liveChart) app.instance.controller.$liveChart.yAxis[0].removePlotLine('currentPrice');
                app.instance.controller.renderLoading(true);
                app.instance.controller.$liveObjectID = $(this).val();
                app.instance.controller.$liveObject = null;
            },
            fetchLiveData: function () {
                if (app.instance.controller.$liveRequest != null) app.instance.controller.$liveRequest.abort();
                app.instance.controller.$liveRequest = app.services.api('GET', 'update', {
                    object: app.instance.controller.$liveObjectID,
                    mode: app.instance.controller.$liveChartMode
                }, function ($response) {
                    app.instance.controller.$liveData = $response;
                    app.instance.controller.$liveObject = app.instance.controller.$liveData.objects[0];
                    if (!app.instance.controller.$liveData.user) {
                        top.location.reload();
                    } else {
                        app.instance.controller.$liveBalance = app.instance.controller.$liveData.user.balance;
                        app.instance.controller.renderLiveObject();
                    }
                }, function () { });
            },
            renderLiveObject: function () {

                $('#account_balance').html('結餘 ￥' + app.instance.controller.$liveBalance);
                $('#order_stake').html(app.instance.controller.$orderStake);
                $('#order_time').html(app.instance.controller.$orderTime);
                $('#object_price').html(app.instance.controller.$liveObject.body_price);
                $('#object_name').html(app.instance.controller.$liveObject.body_name);
                $('#object_profit').html(parseInt(parseFloat(app.instance.controller.$liveObject.body_profit) * 100) + '%');
                $('#order_profit').html(app.instance.controller.$orderStake * app.instance.controller.$liveObject.body_profit);

                if (app.instance.controller.$liveData.user.latestStrikedOrder) {
                    if (!localStorage.getItem('toast_' + app.instance.controller.$liveData.user.latestStrikedOrder.id)) {
                        localStorage.setItem('toast_' + app.instance.controller.$liveData.user.latestStrikedOrder.id, true);
                        var bonus = 0;
                        if (app.instance.controller.$liveData.user.latestStrikedOrder.body_is_draw == 1) {
                            bonus = parseFloat(app.instance.controller.$liveData.user.latestStrikedOrder.body_stake);
                        } else if (app.instance.controller.$liveData.user.latestStrikedOrder.body_is_win == 1) {
                            bonus = parseFloat(app.instance.controller.$liveData.user.latestStrikedOrder.body_stake) + parseFloat(app.instance.controller.$liveData.user.latestStrikedOrder.body_bonus);
                        }
                        app.services.dialog.toast('订单结算，結餘增加 ' + bonus + ' 元');
                    }
                }

            },
            renderLoading: function ($bool) {
                if ($bool) {
                    $('#loading').css('visibility', 'visible').show();
                    $('#workspace').css('visibility', 'hidden').hide();
                } else {
                    $('#loading').css('visibility', 'hidden').hide();
                    $('#workspace').css('visibility', 'visible').show();
                }
            },
            renderButtonOnHover: function () {
                $(this).addClass('hover');
            },
            renderButtonClearHover: function () {
                $(this).removeClass('hover');
            },
            clickedLiveChartButton: function () {
                if (app.instance.controller.$liveChartMode == 'fs') {
                    app.instance.controller.$liveChartMode = 'kx';
                    $('#liveChartButton').html('K线');
                } else {
                    app.instance.controller.$liveChartMode = 'fs';
                    $('#liveChartButton').html('分时');
                }
                app.instance.controller.initLiveChart();
            },
            clickedStakeMinus: function () {
                switch (app.instance.controller.$orderStake) {
                    case 5:
                        app.instance.controller.$orderStake = 5;
                        break;
                    case 10:
                        app.instance.controller.$orderStake = 5;
                        break;
                    case 20:
                        app.instance.controller.$orderStake = 10;
                        break;
                    case 50:
                        app.instance.controller.$orderStake = 20;
                        break;
                    case 100:
                        app.instance.controller.$orderStake = 50;
                        break;
                    case 500:
                        app.instance.controller.$orderStake = 100;
                        break;
                    case 1000:
                        app.instance.controller.$orderStake = 500;
                        break;
                    case 3000:
                        app.instance.controller.$orderStake = 1000;
                        break;
                    case 5000:
                        app.instance.controller.$orderStake = 3000;
                        break;
                    case 10000:
                        app.instance.controller.$orderStake = 5000;
                        break;
                }
                app.instance.controller.renderLiveObject();
            },
            clickedStakePlus: function () {
                switch (app.instance.controller.$orderStake) {
                    case 5:
                        app.instance.controller.$orderStake = 10;
                        break;
                    case 10:
                        app.instance.controller.$orderStake = 20;
                        break;
                    case 20:
                        app.instance.controller.$orderStake = 50;
                        break;
                    case 50:
                        app.instance.controller.$orderStake = 100;
                        break;
                    case 100:
                        app.instance.controller.$orderStake = 500;
                        break;
                    case 500:
                        app.instance.controller.$orderStake = 1000;
                        break;
                    case 1000:
                        app.instance.controller.$orderStake = 3000;
                        break;
                    case 3000:
                        app.instance.controller.$orderStake = 5000;
                        break;
                    case 5000:
                        app.instance.controller.$orderStake = 10000;
                        break;
                    case 10000:
                        app.instance.controller.$orderStake = 10000;
                        break;
                }
                app.instance.controller.renderLiveObject();
            },
            clickedTimeMinus: function () {
                switch (app.instance.controller.$orderTime) {
                    case 60:
                        app.instance.controller.$orderTime = 60;
                        break;
                    case 120:
                        app.instance.controller.$orderTime = 60;
                        break;
                    case 180:
                        app.instance.controller.$orderTime = 120;
                        break;
                    case 240:
                        app.instance.controller.$orderTime = 180;
                        break;
                    case 300:
                        app.instance.controller.$orderTime = 240;
                        break;
                }
                app.instance.controller.renderLiveObject();
            },
            clickedTimePlus: function () {
                switch (app.instance.controller.$orderTime) {
                    case 60:
                        app.instance.controller.$orderTime = 120;
                        break;
                    case 120:
                        app.instance.controller.$orderTime = 180;
                        break;
                    case 180:
                        app.instance.controller.$orderTime = 240;
                        break;
                    case 240:
                        app.instance.controller.$orderTime = 300;
                        break;
                    case 300:
                        app.instance.controller.$orderTime = 300;
                        break;
                }
                app.instance.controller.renderLiveObject();
            },
            clickedOrderUp: function () {
                if (!app.instance.controller.checkBeforeOrder()) return false;
                app.services.dialog.confirm('投資確認', '若到期價格大於下單價格則盈利', function () {
                    app.services.dialog.remove();
                    app.instance.controller.runOrder(1);
                });
            },
            clickedOrderDown: function () {
                if (!app.instance.controller.checkBeforeOrder()) return false;
                app.services.dialog.confirm('投資確認', '若到期價格小於下單價格則盈利', function () {
                    app.services.dialog.remove();
                    app.instance.controller.runOrder(0);
                });
            },
            clickedBalance: function () {
                top.location.href = '/account';
            },
            checkBeforeOrder: function () {
                if (!app.instance.controller.$liveObject.status) {
                    app.services.dialog.alert('非常抱歉', '休市期間無法進行交易');
                    return false;
                } else return true;
            },
            runOrder: function ($direction) {
                $('#loadingToast').show();
                app.services.api('POST', 'order', {
                    object: app.instance.controller.$liveObjectID,
                    stake: app.instance.controller.$orderStake,
                    time: app.instance.controller.$orderTime,
                    direction: $direction
                }, function ($response) {
                    $('#loadingToast').hide();
                    if ($response.error) {
                        app.services.dialog.alert('非常抱歉', $response.error);
                    } else {
                        $('#doneToast').show();
                        setTimeout(function () {
                            $('#doneToast').hide();
                        }, 1000);
                    }
                }, function () {
                    $('#loadingToast').hide();
                    app.services.dialog.alert('非常抱歉', '暫時無法連接服務器，請稍後再試');
                });
            }
        },
        accountBindController: {
            init: function () {
                this.instance.body.on('touchend', 'a#sendSMS', this.instance.controller.clickedSendSMS);
            },
            clickedSendSMS: function () {
                if (!$(this).hasClass('weui_btn_disabled')) {
                    if (!/^0?1[3|4|5|7|8][0-9]\d{8}$/.test($('input[name=mobile]').val())) {
                        app.services.dialog.alert('非常抱歉', '請填寫正確的手機號碼');
                    } else {
                        $(this).addClass('weui_btn_disabled').html('已經發送');
                        app.services.api('POST', 'captcha', {
                            mobile: $('input[name=mobile]').val()
                        }, function () { }, function () {
                            $(this).removeClass('weui_btn_disabled').html('發送驗證短信');
                        });
                    }
                }
            },
            clickedSubmit: function () {
                if (!$('input[name=mobile]').val()) {
                    app.services.dialog.alert('非常抱歉', '請填寫您的手機號碼');
                } else if (!$('input[name=vcode]').val()) {
                    app.services.dialog.alert('非常抱歉', '請填寫您收到的短信驗證碼');
                } else {
                    $('form').submit();
                }
            }
        },
        accountPayController: {
            $stake: 100,
            $gateway: 'online',
            init: function () {
                this.instance.body.on('touchend', 'table.stacksTable a', this.instance.controller.clickedStacks);
            },
            clickedStacks: function () {
                $(this).parents('table').find('a.weui_btn_plain_primary').removeClass('weui_btn_plain_primary').addClass('weui_btn_plain_default');
                $(this).removeClass('weui_btn_plain_default').addClass('weui_btn_plain_primary');
                app.instance.controller.$stake = $(this).attr('data-stake');
                $('#input_stake').val(app.instance.controller.$stake);
            },
            clickedSubmit: function () {
                app.instance.controller.$gateway = $('div.weui_cells_radio input[name=gateway]:checked').val();
                if (app.instance.controller.$gateway == 'staff') {
                    top.location.href = '/account/pay/staff';
                } else {
                    $('#loadingToast').show();
                    document.getElementsByTagName('form')[0].submit();
                }
            }
        },
        accountPayWechatController: {
            $id: 0,
            init: function () {
                app.instance.controller.$id = $('body').attr('data-id');
                setInterval(function () {
                    app.services.api('GET', 'pay/' + app.instance.controller.$id, {}, function ($response) {
                        if ($response.result == 'OK') {
                            window.history.go(-2);
                        }
                    }, function () { });
                }, 1000);
            }
        },
        supportFAQController: {
            init: function () {
                this.instance.body.on('touchend', 'div.weui_cell_title', this.instance.controller.clickedItem);
            },
            clickedItem: function () {
                $('div.weui_cell_content').hide();
                $(this).next().show();
            }
        }
    },
    run: function () {
        this.instance.body = $('body');
        document.querySelector('.button_tap').addEventListener('touchstart', function ($event) {
            $event.preventDefault();
        });
        if (this.controllers[$('body').attr('data-controller')]) {
            this.instance.controller = this.controllers[$('body').attr('data-controller')];
            this.instance.controller.init.call(this);
        }
    }
};

$(document).ready(function(){
    window.app.run();
});