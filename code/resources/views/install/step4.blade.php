<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>{{$Title}} - {{$Powered}}</title>
    <link rel="stylesheet" href="/install/css/install.css" />
    <link rel="stylesheet" href="/install/css/step4.css" />
    <link rel="stylesheet" href="/install/css/theme-chalk.css" />
    <script src="/install/js/vue2.6.11.js"></script>
    <script src="/install/js/element-ui.js"></script>
    <script src="/install/js/jquery.js"></script>
</head>
<body>
<div class="wrap" id="step4">
    <div class="title">
        安装进度
    </div>
    <section class="section">
        <div class="title">
            <h1>系统安装中，请稍等片刻...</h1>
        </div>
        <div class="progress">
            <el-progress :percentage="percentage" color="#37CA71" define-back-color="rgba(255,255,255,0.5)" :stroke-width="8" status="success"></el-progress>
            <div class="progress-msg" v-if="!isShow">
                <div id="loginner_item" class="msg p8" v-text="installList[installList.length]"></div>
                <!--                <div class="open" @click="openList">查看详情</div>-->
            </div>
        </div>
        <div class="install" ref="install" id="log" v-show="isShow">
            <div id="loginner" class="item" v-for="(item,index) in installList" :key="index">
                <span v-text="item.msg"></span>
                <span v-text="item.time"></span>
            </div>
        </div>
        <div class="bottom tac"><a href="javascript:;" class="btn_old mid">
            <img class="shuaxin" src="/install/images/shuaxin.png"/>&nbsp;正在安装...</a>
        </div>
    </section>
</div>
@include('install/footer')
</body>
<script type="text/javascript">
    $.ajaxSetup({cache: false});
    new Vue({
        el: '#step4',
        data() {
            return {
                percentage: 0,
                isShow: 0,
                installList: [],
                n:0
            }
        },
        mounted() {
            this.reloads(this.n);
        },
        methods: {
            reloads(num) {
                var url = location.href+'?n='+num;
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {n:num},
                    dataType: 'JSON',
                    cache: false,
                    beforeSend: () => {
                    },
                    success: (res) => {
                        this.percentage = Math.round((res.data.n / res.data.count) * 100) > 100 ? 100 : Math.round((res.data.n / res.data.count) * 100)

                        if (res.data.n >= 0) {
                            if (res.data.msg){
                                $('#loginner_item').html(res.data.msg);
                                this.installList.push({
                                    msg: res.data.msg,
                                    time: res.data.time
                                })
                                this.$nextTick(e => {
                                    this.$refs.install.scrollTop = this.$refs.install.scrollHeight;
                                })
                            }
                            if (res.data.n === 99999) {
                                setTimeout(e => {
                                    this.gonext()
                                }, 1000);
                                return false;
                            } else {
                                this.reloads(res.data.n);
                            }
                        } else {
                            //alert('指定的数据库不存在，系统也无法创建，请先通过其他方式建立好数据库！');
                            alert(res.data.msg);
                        }

                    }
                });
            },
            openList() {
                this.isShow = true
                this.$nextTick(e => {
                    this.$refs.install.scrollTop = this.$refs.install.scrollHeight;
                })
            },
            gonext() {
                window.location.href = '/install/index/5';
            }
        }
    })
</script>
</html>
