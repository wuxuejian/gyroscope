<!-- 申请发票填写信息组件 -->
<template>
  <div class="station">
    <el-drawer
      :title="formData.title"
      :visible.sync="drawer"
      :direction="direction"
      :modal="true"
      :show-close="true"
      :wrapper-closable="true"
      :append-to-body="true"
      :before-close="handleClose"
      :size="formData.width"
    >
      <div class="invoice-title" slot="title">
        <el-row class="invoice-header">
          <el-col class="invoice-left">
            <div class="invoice-logo"><i class="icon iconfont iconfapiao"></i></div>
          </el-col>
          <el-col v-if="drawer">
            <div class="txt1">{{ delData.title }}</div>
            <div class="txt2">
              <span class="title1">发票状态：</span>
              <span class="tab-btn" :class="getInvoiceColor(delData.status)">
                {{ getInvoiceStatus(delData.status) }}
              </span>

              <span class="title">{{ $t('customer.invoicingpay') }}：</span
              ><span class="info2">{{ delData.amount }}</span>
            </div>
          </el-col>
        </el-row>
      </div>
      <el-tabs v-model="tabIndex" type="border-card" @tab-click="handleClick" :tab-position="tabPosition">
        <el-tab-pane label="发票信息" name="1"></el-tab-pane>
        <el-tab-pane label="关联付款单" name="2"></el-tab-pane>
        <el-tab-pane label="操作记录" name="3"></el-tab-pane>
      </el-tabs>
      <div class="contract-body">
        <!-- 发票信息 -->
        <el-form label-width="110px" v-if="tabIndex == 1" class="invoice-body">
          <div class="from-item-title mb15">
            <span>{{ $t('setting.info.essentialinformation') }}</span>
          </div>
          <div class="form-box">
            <div class="form-item">
              <el-form-item>
                <span slot="label">客户名称：</span>
                <p>{{ delData.client ? delData.client.name : '--' }}</p>
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.treaty">
              <el-form-item>
                <span slot="label">合同名称：</span>
                <p>{{ delData.treaty ? delData.treaty.contract_name : '--' }}</p>
              </el-form-item>
            </div>
            <div class="form-item">
              <el-form-item>
                <span slot="label">付款金额(元)：</span>
                <p>{{ delData.price }}</p>
              </el-form-item>
            </div>
            <div class="form-item">
              <el-form-item>
                <span slot="label" class="item-label">发票类目：</span>
                <p>{{ delData.category ? delData.category.name : '' }}</p>
              </el-form-item>
            </div>

            <div class="form-item">
              <el-form-item>
                <span slot="label">{{ $t('customer.invoicingdate') }}：</span>
                <p>{{ delData.bill_date }}</p>
              </el-form-item>
            </div>
            <div class="form-item">
              <el-form-item>
                <span slot="label">{{ $t('public.remarks') }}：</span>
                <p>{{ delData.mark || '--' }}</p>
              </el-form-item>
            </div>
          </div>
          <div class="line" />

          <div class="from-item-title mt15 mb15">
            <span>{{ $t('customer.invoiceinformation') }}</span>
          </div>
          <div class="form-box">
            <div class="form-item">
              <el-form-item>
                <span slot="label">{{ $t('customer.invoicingpay') }}：</span>
                <p>{{ delData.amount }}</p>
              </el-form-item>
            </div>
            <div class="form-item">
              <el-form-item>
                <span slot="label">{{ $t('customer.headerinformation') }}：</span>
                <p>{{ getInvoiceTitle(Number(delData.types)) }}</p>
              </el-form-item>
            </div>
            <div class="form-item">
              <el-form-item>
                <span slot="label">{{ $t('customer.invoiceheader') }}：</span>
                <p>{{ delData.title }}</p>
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.types > 1">
              <el-form-item>
                <span slot="label">{{ $t('customer.paytaxes') }}：</span>
                <p>{{ delData.ident }}</p>
              </el-form-item>
            </div>

            <div class="form-item" v-if="delData.types > 2">
              <el-form-item>
                <span slot="label">地址、电话：</span>
                <p>{{ delData.address }} {{ delData.tel }}</p>
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.types > 2">
              <el-form-item>
                <span slot="label">开户行及账号：</span>
                <p>{{ delData.bank }} {{ delData.account }}</p>
              </el-form-item>
            </div>
          </div>

          <div class="line" />

          <!-- 邮寄信息 -->
          <div class="from-item-title mt15 mb15">
            <span>{{ $t('customer.mailinginformation') }}</span>
          </div>
          <div class="form-box">
            <div class="form-item">
              <el-form-item>
                <span slot="label">开票要求：</span>
                <p>
                  {{ delData.collect_type == 'mail' ? '电子' : '纸质' }}
                </p>
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.status !== 0 && delData.status !== -1">
              <el-form-item>
                <span slot="label">发送方式：</span>
                <p>
                  {{ delData.invoice_type == 'mail' ? '邮箱' : '快递' }}
                </p>
              </el-form-item>
            </div>
            <template v-if="delData.status !== 0 && delData.status !== -1">
              <div class="form-item" v-if="delData.invoice_type !== 'mail'">
                <el-form-item>
                  <span slot="label">{{ $t('customer.contacts') }}：</span>
                  <p>{{ delData.collect_name }}</p>
                </el-form-item>
              </div>
              <div class="form-item" v-if="delData.invoice_type !== 'mail'">
                <el-form-item>
                  <span slot="label">{{ $t('customer.contactnumber') }}：</span>
                  <p>{{ delData.collect_tel }}</p>
                </el-form-item>
              </div>

              <div class="form-item">
                <el-form-item v-if="delData.invoice_type === 'mail'">
                  <span slot="label"> {{ $t('customer.emailaddress') }} ：</span>
                  <p>{{ delData.invoice_address }}</p>
                </el-form-item>
                <el-form-item v-else>
                  <span slot="label">邮箱信息：</span>
                  <p>{{ delData.invoice_address }}</p>
                </el-form-item>
              </div>
            </template>
            <template v-else>
              <div class="form-item" v-if="delData.collect_type !== 'mail'">
                <el-form-item>
                  <span slot="label">{{ $t('customer.contacts') }}：</span>
                  <p>{{ delData.collect_name }}</p>
                </el-form-item>
              </div>
              <div class="form-item" v-if="delData.collect_type !== 'mail'">
                <el-form-item>
                  <span slot="label">{{ $t('customer.contactnumber') }}：</span>
                  <p>{{ delData.collect_tel }}</p>
                </el-form-item>
              </div>

              <div class="form-item">
                <el-form-item v-if="delData.collect_type === 'mail'">
                  <span slot="label"> {{ $t('customer.emailaddress') }} ：</span>
                  <p>{{ delData.collect_email }}</p>
                </el-form-item>
                <el-form-item v-else>
                  <span slot="label">邮箱信息：：</span>
                  <p>{{ delData.mail_address }}</p>
                </el-form-item>
              </div>
            </template>
          </div>

          <div class="line" />
          <!-- 开票信息 -->
          <div class="from-item-title mt15 mb15">
            <span>{{ $t('customer.billinginformation') }}</span>
          </div>
          <div class="form-box">
            <div class="form-item">
              <el-form-item>
                <span slot="label">开票结果：</span>
                <p>{{ getInvoiceStatus(delData.status) }}</p>
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.status !== 0 && delData.status !== -1">
              <el-form-item>
                <span slot="label">审核备注：</span>
                <p v-if="delData.status == -1">--</p>
                <p v-else>{{ delData.remark || '--' }}</p>
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.file && delData.file.length > 0">
              <el-form-item>
                <span slot="label">开票凭证：</span>
                <img :src="delData.file[0].thumb_dir" alt="" class="item-img" />
              </el-form-item>
            </div>
            <div class="form-item" v-if="delData.status !== 0">
              <el-form-item>
                <span slot="label">操作时间：</span>
                <p>{{ delData.updated_at || '--' }}</p>
              </el-form-item>
            </div>

            <div class="form-item">
              <el-form-item>
                <span slot="label">申请人：</span>
                <p>{{ delData.card ? delData.card.name : '--' }}</p>
              </el-form-item>
            </div>
            <div class="form-item">
              <el-form-item>
                <span slot="label">申请时间：</span>
                <p>{{ delData.created_at || '--' }}</p>
              </el-form-item>
            </div>
          </div>
        </el-form>

        <!-- 关联付款单 -->
        <div v-if="tabIndex == 2" class="invoice-body mr20">
          <el-table :data="tableData" style="width: 100%;">
            <el-table-column prop="bill_no" label="付款单号" min-width="180"> </el-table-column>
            <el-table-column prop="treaty.contract_name" label="合同名称" min-width="150"> </el-table-column>

            <el-table-column prop="types" label="业务类型" min-width="200">
              <template slot-scope="scope">
                <span v-if="scope.row.types === 0">回款记录</span>
                <span v-if="scope.row.types === 1">续费记录 - {{ scope.row.renew.title }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="num" label="付款金额(元)" min-width="150"> </el-table-column>

            <el-table-column prop="card.name" label="创建人" min-width="90"> </el-table-column>
            <el-table-column prop="created_at" label="创建时间" min-width="180"> </el-table-column>
          </el-table>
        </div>

        <!-- 操作记录 -->
        <div class="invoice-body1" v-if="tabIndex == 3">
          <div class="default" v-if="recordList.length == 0">
            <img src="../../../../assets/images/defd.png" alt="" class="img" />
            <div class="text">暂无操作记录</div>
          </div>
          <el-steps direction="vertical" space="100" class="set" :active="1" v-if="recordList.length !== 0">
            <el-step v-for="(item, index) in recordList" :key="index">
              <div slot="icon">
                <span class="iconfont iconfapiaoxiangqing-caozuojilu"></span>
              </div>
              <div slot="description">
                <div class="operationBox" :class="item.operation_name == '申请开票' ? 'removeBorderLine' : ''">
                  <div class="header">
                    <div class="left">{{ item.operation_name }}</div>
                    <div class="right">
                      {{ item.card.name }}
                      <el-divider direction="vertical" />
                      {{ item.created_at }}
                    </div>
                  </div>

                  <div class="footer" v-if="item.operation_name !== '申请开票'">
                    <el-form label-width="110px" :row-style="{ height: '32px' }" class="description">
                      <el-form-item :label="details.name" v-for="(details, index) in item.operation" :key="index">
                        <span v-if="details.name !== '开票凭证：'" class="content">{{ details.val }}</span>
                        <img :src="details.val" alt="" v-else class="item-img" />
                      </el-form-item>
                    </el-form>
                  </div>
                </div>
              </div>
            </el-step>
          </el-steps>
        </div>
      </div>

      <div
        class="button from-foot-btn fix btn-shadow"
        v-if="formData.follType && formData.follType === 'fd' && delData.status === 0"
      >
        <el-button size="small" type="primary" @click="handleInvoicing">审核</el-button>
      </div>
      <el-image-viewer v-if="isImage" :on-close="closeImageViewer" :url-list="srcList" />
    </el-drawer>
    <invoicing-dialog ref="invoicingDialog" :config="invoicingDialog" @isOk="isOk" />
  </div>
</template>

<script>
import file from '@/utils/file'
import { paymentRecordApi, operationRecordApi } from '@/api/enterprise'
import Vue from 'vue'
Vue.use(file)
import { getInvoiceClassName, getInvoiceText, getInvoiceType } from '@/libs/customer'
export default {
  name: 'InvoiceView',
  props: {
    formData: {
      type: Object,
      default: () => {
        return {}
      }
    }
  },
  components: {
    ElImageViewer: () => import('element-ui/packages/image/src/image-viewer'),
    invoicingDialog: () => import('@/views/fd/invoice/components/invoicingDialog')
  },
  data() {
    return {
      tabIndex: '1',
      drawer: false,
      direction: 'rtl',
      isImage: false,
      srcList: [],
      delData: {},
      tableData: [],
      tabPosition: 'top',
      recordList: [],
      list: [],
      invoicingDialog: {}
    }
  },
  watch: {
    formData: {
      handler(nVal, oVal) {
        this.delData = nVal.data
      },
      deep: true
    }
  },
  methods: {
    handleClose() {
      this.drawer = false
      this.tabIndex = '1'
    },
    openBox() {
      this.drawer = true
    },
    // 开票
    handleInvoicing() {
      this.invoicingDialog = {
        title: '开票审核',
        data: this.formData.data
      }
      this.$refs.invoicingDialog.openBox()
    },
    isOk() {
      this.handleClose()
    },
    getInvoiceStatus(status) {
      return getInvoiceText(status)
    },
    getInvoiceColor(status) {
      return getInvoiceClassName(status)
    },
    getInvoiceTitle(id) {
      return getInvoiceType(id)
    },
    // 关联付款单
    paymentRecordApi() {
      let data = {
        invoice_id: this.delData.id,
        eid: this.delData.eid
      }
      paymentRecordApi(data).then((res) => {
        this.tableData = res.data.list
      })
    },
    // 获取操作记录
    operationRecord() {
      operationRecordApi(this.formData.data.id).then((res) => {
        this.recordList = res.data.list
      })
    },
    handleClick(e) {
      if (this.tabIndex == 2) {
        this.paymentRecordApi()
      }
      if (this.tabIndex == 3) {
        this.operationRecord()
      }
    },
    handlePictureCardPreview(row) {
      this.srcList.push(row)
      this.isImage = true
    },
    closeImageViewer() {
      this.isImage = false
      this.srcList = []
    }
  }
}
</script>

<style lang="scss" scoped>
.station /deep/.el-drawer__body {
  padding: 20px 20px 50px 20px;
}
.item-img {
  width: 40px;
  height: 40px;
  display: block;
}

.def {
  width: 100%;
  display: flex;
  flex-direction: column;

  align-items: center;
  .def-img {
    width: 200px;
    height: 150px;
  }
  .def-text {
    color: #c0c4cc;
  }
}
.default {
  width: 800px;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 200px;
  .img {
    width: 200px;
    height: 150px;
  }
  .text {
    font-size: 14px;
    font-family: PingFangSC-Regular, PingFang SC;
    font-weight: 400;
    color: #c0c4cc;
  }
}
.set {
  /deep/ .el-step__icon.is-text {
    border: none;
  }
  .iconfapiaoxiangqing-caozuojilu {
    font-size: 13px;
    color: #1890ff;
  }
  /deep/ .el-step__line {
    width: 1px;
    background-color: #ebeef4;
  }
  /deep/ .el-step__icon {
    margin-top: 20px;
    height: 12px;
  }
  /deep/ .el-step.is-vertical .el-step__line {
    top: 20px;
    bottom: -18px;
  }
}

.description /deep/ .el-form-item {
  margin-bottom: 0px;
}
.removeBorderLine {
  border: none !important;
}

.name {
  width: 100%;
  font-size: 13px;
  font-weight: 400;
  color: #909399;
}
.content {
  font-size: 13px;
  font-weight: 400;
  color: #303133;
  line-height: 12px;
}
/deep/ .el-step.is-vertical .el-step__main {
  width: 800px;
}

/deep/ .el-tabs__item.is-active {
  border-right-color: transparent !important;
  border-left-color: transparent !important;
  &::after {
    content: '';
    height: 2px;
    width: 100%;
    background-color: #1890ff;
    position: absolute;
    left: 0;
    top: 0;
  }
}
.mr20 {
  margin-right: 20px;
}
.img {
  width: 40px;
  height: 40px;
}
.invoice-body1 {
  margin: 20px 30px 30px 15px;
  width: 100%;
}

.operationBox {
  margin-bottom: 35px;
  width: 827px;

  border-radius: 4px 4px 4px 4px;
  border: 1px solid #eaf4ff;
  .header {
    padding: 13px 20px;
    height: 46px;
    background: #f7fbff;
    display: flex;
    justify-content: space-between;

    .left {
      font-size: 14px;
      font-family: PingFang SC-中黑体, PingFang SC;
      font-weight: 600;
      color: #303133;
    }
    .right {
      font-size: 13px;
      font-family: PingFang SC-常规体, PingFang SC;
      font-weight: normal;
      color: #909399;
    }
  }
  .footer {
    padding: 20px 20px 8px 0px;
    /deep/ .el-form-item {
      margin-bottom: 12px;
    }

    /deep/ .el-form-item__label {
      line-height: 18px;
    }
    /deep/.el-form-item__content {
      line-height: 18px;
    }
  }
}
/deep/ .el-drawer__header {
  height: 85px !important;
  padding: 14px 18px;
}
/deep/ .el-tabs__header {
  background-color: #f7fbff;
  border-bottom: none;
}
/deep/ .el-tabs__content {
  padding: 0;
}
/deep/ .el-tabs__nav-wrap::after {
  height: 0;
}
/deep/ .el-tabs__active-bar {
  top: 0;
}
.el-tabs--border-card {
  height: 39px;
  position: fixed;
  top: 85px;
  width: 100%;
  z-index: 4;
  background-color: transparent;
  border: none;
  box-shadow: none;
}
/deep/ .el-step.is-vertical .el-step__title {
  font-size: 14px;
  font-family: PingFangSC-Medium, PingFang SC;
  font-weight: 500;
  color: #303133;
  margin-left: 10px;
}
/deep/ .el-form--inline .el-form-item {
  display: flex;
}
/deep/ .el-input-number--medium {
  width: 100%;
  .el-input__inner {
    text-align: left;
  }
}
/deep/ .el-date-editor {
  width: 100%;
}
.invoice-body {
  margin: 20px;
  width: 100%;
}
.invoice-title {
  .invoice-header {
    display: flex;
    align-items: center;
    .invoice-left {
      width: 48px;
      margin-right: 10px;
      .invoice-logo {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #00c050;
        border-radius: 4px;
        i {
          color: #ffffff;
          font-size: 30px;
          margin-top: 12px;
        }
      }
    }
    .txt1 {
      font-size: 14px;
      font-weight: bold;
      color: rgba(0, 0, 0, 0.85);
    }
    .title1 {
      color: #999999;
    }
    .title {
      color: #999999;
      padding-left: 20px;
    }
    .txt2 {
      margin-top: 10px;
      font-size: 13px;

      .tab-btn {
        display: inline;
        &.blue {
          color: #1890ff;

          border: none;
        }

        &.yellow {
          color: #ff9900;

          border: none;
        }
        &.red {
          color: #ed4014;
          border: none;
        }

        &.green {
          color: #00c050;
          border: none;
        }

        &.gray {
          color: #999999;
          border: none;
        }
      }
    }
  }
}
.from-item-title {
  border-left: 3px solid #1890ff;
  span {
    padding-left: 10px;
    font-weight: bold;
    font-size: 14px;
  }
}
.contract-body {
  padding: 32px 20px 0 0px;
  height: calc(100% - 14px);
  display: flex;
  .contract-info {
    width: 100%;
  }
  .contract-list {
    width: 100%;
    height: 100%;
    /deep/ .el-button--medium {
      font-size: 13px;
    }
    .icon-cover {
      font-size: 28px;
    }
  }
}
.line {
  width: 100%;
  height: 4px;
  border-bottom: 1px dashed #dcdfe6;
  margin-bottom: 30px;
  margin-top: 10px;
}
.form-box {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  .form-item {
    width: 49%;
    /deep/ .el-form-item__content {
      width: calc(100% - 110px);
    }
    /deep/ .el-select--medium {
      width: 100%;
    }
    /deep/ .el-form-item {
      margin-bottom: 0;
    }
    /deep/ .el-textarea__inner {
      resize: none;
    }

    p {
      margin: 0;
      padding: 0;

      font-weight: 400 !important;
      color: #303133;
      font-size: 12px !important;
      margin-top: 10px;
      line-height: 18px;
    }
  }
}
.examine-card {
  div {
    display: inline-block;
  }
  /deep/ .el-upload--picture-card {
    width: 98px;
    height: 98px;
    line-height: 102px;
  }
  .upload-icon {
    font-size: 98px;
  }
  /deep/ .el-upload-list--picture-card .el-upload-list__item {
    width: 98px;
    height: 98px;
    line-height: 1;
    .el-image {
      width: 100%;
      height: 100%;
    }
  }
}
.el-upload-list__item-actions {
  i {
    color: #ffffff;
    font-size: 24px;
  }
}
.img {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-top: 10px;
}
.dialog-footer {
  padding-top: 20px;
  border-top: 1px solid #e6ebf5;
  text-align: right;
}
/deep/ .el-form-item__label {
  font-size: 12px !important;
  font-weight: 400;
  color: #909399 !important;
}
</style>
