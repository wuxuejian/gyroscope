<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>{{$Title}} - {{$Powered}}</title>
    <link rel="stylesheet" href="/install/css/install.css" />
    <link rel="stylesheet" href="/install/css/step3.css" />
    <link rel="stylesheet" href="/install/css/theme-chalk.css" />
    <script src="/install/js/vue2.6.11.js"></script>
    <script src="/install/js/element-ui.js"></script>
    <script src="/install/js/jquery.js"></script>
</head>
<body>
<div class="wrap" id="step3" v-cloak>
    <div class="title">
        创建数据
    </div>
    <section class="section">
        <div class="server"  ref="mianscroll">
            <el-form :model="form" :rules="rules" ref="ruleForm" label-width="120px">
                <p class="tip">数据库信息</p>
                <el-form-item label="数据库用户名:" prop="dbUser" class="label" :error="errorMsg.db_user">
                    <el-input v-model="form.dbUser" placeholder="请输入数据库用户名"></el-input>
                </el-form-item>
                <el-form-item label="数据库密码:" prop="dbPwd" class="label" :error="errorMsg.db_pwd">
                    <el-input v-model="form.dbPwd" placeholder="请输入数据库密码"></el-input>
                </el-form-item>
                <el-form-item label="数据库名:" prop="dbName" class="label" :error="errorMsg.db_name">
                    <el-input v-model="form.dbName" placeholder="请输入数据库名"></el-input>
                </el-form-item>
                <el-form-item label="高级设置:" class="label">
                    <el-switch v-model="value" active-color="#37CA71" inactive-color="#575869"></el-switch>
                </el-form-item>
                <el-form-item label="数据库服务器:" prop="dbHost" class="label" v-if="value" :error="errorMsg.db_host">
                    <el-input v-model="form.dbHost" placeholder="请输入数据库地址"></el-input>
                </el-form-item>
                <el-form-item label="数据库端口:" prop="dbPort" class="label" v-if="value" :error="errorMsg.db_port">
                    <el-input v-model="form.dbPort" placeholder="请输入数据库端口号"></el-input>
                </el-form-item>
{{--                <el-form-item label="数据表前缀:" prop="dbPrefix" class="label" v-if="value">--}}
{{--                    <el-input v-model="form.dbPrefix" placeholder="请输入数据表前缀"></el-input>--}}
{{--                </el-form-item>--}}
                <el-form-item label="演示数据:" class="label" v-if="value">
                    <el-checkbox v-model="form.initData"></el-checkbox>
                </el-form-item>
                <p class="tip">管理员信息</p>
                <el-form-item label="管理员帐号:" prop="account" class="label" :error="errorMsg.account">
                    <el-input v-model="form.account" placeholder="请输入管理员手机号"></el-input>
                </el-form-item>
                <el-form-item label="管理员密码:" prop="password" class="label">
                    <el-input v-model="form.password" placeholder="请输入密码（至少6个字符）"></el-input>
                </el-form-item>
                <el-form-item label="重复密码:" prop="checkPass" class="label">
                    <el-input v-model="form.checkPass" placeholder="请再次输入密码"></el-input>
                </el-form-item>
                <p class="tip">缓存设置</p>
                <el-form-item label="缓存方式:" class="label">
                    <el-radio-group v-model="form.cacheDriver" class="ml-4">
{{--                        <el-radio label="file" name="cache_type" id="cache_type1">文件缓存</el-radio>--}}
                        <el-radio label="redis" name="cache_type" id="cache_type2">redis缓存</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="服务器地址:" prop="rbHost" class="label" v-if="form.cacheDriver === 'redis'" :error="errorMsg.redis_host">
                    <el-input v-model="form.rbHost" placeholder="请输入redis服务器地址"></el-input>
                </el-form-item>
                <el-form-item label="端口号:" prop="rbPort" class="label" v-if="form.cacheDriver === 'redis'">
                    <el-input v-model="form.rbPort" placeholder="请输入redis服务器端口号"></el-input>
                </el-form-item>
                <el-form-item label="数据库:" prop="rbNum" class="label" v-if="form.cacheDriver === 'redis'">
                    <el-input v-model="form.rbNum" placeholder="请输入redis服务器数据库编号"></el-input>
                </el-form-item>
                <el-form-item label="数据库密码:" class="label" v-if="form.cacheDriver === 'redis'" :error="errorMsg.redis_pwd">
                    <el-input v-model="form.rbPwd" placeholder="请输入redis数据库密码"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <div class="bottom-btn">
            <div class="bottom tac up-btn">
                <a href="/install/index/2" class="btn">上一步</a>
            </div>
            <div class="bottom tac next">
                <a @click="submitForm('ruleForm')" class="btn" v-loading="loading">下一步</a>
            </div>
        </div>
    </section>
    <div style="width:0;height:0;overflow:hidden;"><img src="/install/images/pop_loading.gif"></div>
    <script>
        const form = <?php echo json_encode($form); ?>;
        new Vue({
            el: '#step3',
            data() {
                const validatePass = (rule, value, callback) => {
                    if (value === '') {
                        callback(new Error('请输入密码'));
                    } else {
                        if (this.form.checkPass !== '') {
                            this.$refs.ruleForm.validateField('checkPass');
                        }
                        callback();
                    }
                };
                const validatePass2 = (rule, value, callback) => {
                    if (value === '') {
                        callback(new Error('请再次输入密码'))
                    } else if (value !== this.form.password) {
                        callback(new Error("两次输入密码不一致!"))
                    } else {
                        callback()
                    }
                }
                return {
                    loading: false,
                    value: false,
                    radio: 0,
                    form: form,
                    rules:{
                        dbUser:[{ required: true, message: '请输入数据库用户名', trigger: 'blur' },],
                        dbHost:[{ required: true, message: '请输入数据库服务器', trigger: 'blur' },],
                        dbPort:[{ required: true, message: '请输入数据库端口号', trigger: 'blur' },],
                        dbName:[{ required: true, message: '请输入数据库名称', trigger: 'blur' },],
                        dbPwd:[{ required: true, message: '请输入数据库密码', trigger: 'blur' },],
                        dbPrefix:[{ required: true, message: '请输入数据表前缀', trigger: 'blur' },],
                        rbHost:[{ required: true, message: '请输入redis服务器地址', trigger: 'blur' },],
                        rbPort:[{ required: true, message: '请输入redis服务器端口号', trigger: 'blur' },],
                        rbNum:[{ required: true, message: '请输入redis数据库编号', trigger: 'blur' },],
                        account:[{ required: true, message: '请输入管理员手机号', trigger: 'blur' },{ validator: this.validateMobile, trigger: 'blur' },],
                        password:[{ required: true, message: '请输入管理员密码', trigger: 'blur' },{ required: true, message: '管理员密码必须6位字符以上', trigger: 'blur' },{ validator: validatePass, trigger: 'blur' }],
                        checkPass: [{ required: true, message: '请确认管理员密码', trigger: 'blur' },{ validator: validatePass2, trigger: 'blur' }],
                    },
                    errorMsg:{
                        db_host:'',
                        db_port:'',
                        db_name:'',
                        db_user:'',
                        db_pwd:'',
                        account:'',
                        redis_host:'',
                        redis_pwd:'',
                    }
                }
            },
            methods: {
                mysqlDbPwd() {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            type: "POST",
                            url: '/install/index/3',
                            data: this.form,
                            dataType: 'JSON',
                            success: (msg) => {
                                resolve(msg);
                            },
                            error: (err) => {
                                reject(err)
                            }
                        });
                    })
                },
                validateMobile(rule, value, callback) {
                    const reg = /^[1][3,4,5,6,7,8,9][0-9]{9}$/;
                    if (value === '' || reg.test(value)) {
                        callback();
                    } else {
                        callback(new Error('请输入正确的手机号'));
                    }
                },
                jumpButton(){
                    this.$refs.mianscroll.scrollTop = this.$refs.mianscroll.clientHeight
                },
                submitForm(formName) {
                    if (this.loading){
                        return;
                    }
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            this.loading = true
                            this.resetError()
                            this.mysqlDbPwd().then(res=>{
                                let code = res.data.code
                                this.loading = false
                                switch (code){
                                    case 2002:
                                        this.errorMsg.db_host = '数据库地址或端口错误'
                                        this.errorMsg.db_port = '数据库地址或端口错误'
                                        break;
                                    case 1045:
                                        this.errorMsg.db_user = '数据库用户名或密码错误'
                                        this.errorMsg.db_pwd = '数据库用户名或密码错误'
                                        break;
                                    case 1049:
                                        this.errorMsg.db_name = '请检查数据库是否存在'
                                        break;
                                    case -5:
                                        this.errorMsg.db_name = 'MySql数据库必须是5.7及以上版本'
                                        break;
                                    case -4:
                                        this.errorMsg.db_name = '数据库不为空，请更换一个数据库'
                                        break;
                                    case -3:
                                        this.errorMsg.redis_pwd = 'Redis数据库没有启动或者配置错误'
                                        break;
                                    case -2:
                                        this.errorMsg.account = '请填写正确的手机号'
                                        break;
                                    case -1:
                                        this.errorMsg.redis_host = 'Redis扩展没有安装'
                                        break;
                                    case 1:
                                        window.location.href = '/install/index/4'
                                        break;
                                    case 61:
                                        this.errorMsg.redis_host = 'Connection failed: Connection refused!'
                                        break;
                                    case 60:
                                        this.errorMsg.redis_host = 'Connection failed: Operation timed out!'
                                        break;
                                    case 0:
                                        this.errorMsg.redis_host = 'Connection failed: `AUTH` failed!'
                                        break;
                                    default:
                                        this.errorMsg.redis_host = 'Redis连接失败'
                                }
                            }).catch(err=>{
                                this.loading = false
                            })
                        } else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                resetError(){
                    this.errorMsg.db_host = ''
                    this.errorMsg.db_port = ''
                    this.errorMsg.db_name = ''
                    this.errorMsg.db_user = ''
                    this.errorMsg.db_pwd = ''
                    this.errorMsg.redis_host = ''
                    this.errorMsg.redis_pwd = ''
                }
            }
        })


    </script>
</div>
@include('install/footer')
</body>
<style>
    .server .label{
        color: #fff;
        margin-bottom: 0;
    }
    .tip{
        font-size: 14px;
        width: 105px;
        text-align: right;
        color: #fff;
        padding-right: 15px;
    }
    .el-form-item {
        height: 50px;
        line-height: 50px;
    }
    .el-form-item__label{
        font-weight: normal;
        text-align: right;
        padding: 2px 15px;
        color: #fff;
        font-size: 12px;
        width: 120px;
    }
    .el-input{
        width: 200px;
        height: 30px;
    }
    .el-input__inner{
        border: none;
        border-radius: 20px;
        background-color: rgba(0,0,0,0.1);
        padding: 0px 15px;
        width: 200px;
        height: 30px;
        color: #fff;
    }
    .el-form-item__error{
        position: relative;
        font-size: 12px;
        top: -30px;
        left: 210px;
    }
    .el-loading-mask{
        border-radius: 137px;
        font-size: 14px;
    }
    .el-loading-spinner{
        margin-top: -15px;
    }
    .el-loading-spinner .circular{
        width: 30px;
        height: 30px;
    }
</style>
</html>
