<template>
  <form-item-wrapper
    :designer="designer"
    :field="field"
    :rules="rules"
    :design-state="designState"
    :parent-widget="parentWidget"
    :parent-list="parentList"
    :index-of-parent-list="indexOfParentList"
    :sub-form-row-index="subFormRowIndex"
    :sub-form-col-index="subFormColIndex"
    :sub-form-row-id="subFormRowId"
  >
    <uploadFile
      ref="fieldEditor"
      :only-image="false"
      :value="fieldModel"
      v-model="fieldModel"
      :max-length="Number(field.options.fileMaxSize)"
      :max-size="Number(field.options.fileMaxSize)"
      :fileTypes="field.options.fileTypes"
      url=""
      @input="handleSuccess"
    ></uploadFile>
  </form-item-wrapper>
</template>

<script>
import FormItemWrapper from './form-item-wrapper'
import emitter from '@/utils/emitter'
import i18n, { translate } from '@/utils/i18n'
import { deepClone, evalFn } from '@/utils/util'
import fieldMixin from '@/components/form-designer/form-widget/field-widget/fieldMixin'
import SettingMer from '@/libs/settingMer'
import { getToken } from '@/utils/auth'
import uploadFile from '@/components/form-common/oa-upload'

let selectFileText = '选择文本'

export default {
  name: 'file-widget',
  componentName: 'FieldWidget', //必须固定为FieldWidget，用于接收父级组件的broadcast事件
  mixins: [emitter, fieldMixin, i18n],
  props: {
    field: Object,
    parentWidget: Object,
    parentList: Array,
    indexOfParentList: Number,
    designer: Object,
    designState: {
      type: Boolean,
      default: false
    },

    subFormRowIndex: {
      /* 子表单组件行索引，从0开始计数 */ type: Number,
      default: -1
    },
    subFormColIndex: {
      /* 子表单组件列索引，从0开始计数 */ type: Number,
      default: -1
    },
    subFormRowId: {
      /* 子表单组件行Id，唯一id且不可变 */ type: String,
      default: ''
    }
  },
  components: {
    FormItemWrapper,
    uploadFile
  },
  inject: ['refList', 'formConfig', 'globalOptionData', 'globalModel'],
  data() {
    return {
      oldFieldValue: null, //field组件change之前的值
      fieldModel: [],
      rules: [],
      action: SettingMer.https + `/folder/upload`,
      myHeaders: {
        authorization: 'Bearer ' + getToken()
      },
      uploadHeaders: {},
      uploadData: {
        key: '' //七牛云上传文件名
        //token: '',  //七牛云上传token

        //policy: '',  //又拍云上传policy
        //authorization: '',  //又拍云上传签名
      },
      fileList: [], //上传文件列表
      uploadBtnHidden: false,

      styleVariables: {
        '--select-file-action': selectFileText
      }
    }
  },
  computed: {
    realUploadURL() {
      let uploadURL = this.field.options.uploadURL
      if (!!uploadURL && (uploadURL.indexOf('DSV.') > -1 || uploadURL.indexOf('DSV[') > -1)) {
        let DSV = this.getGlobalDsv()
        console.log('test DSV: ', DSV) //防止DSV被打包工具优化！！！
        return evalFn(this.field.options.uploadURL, DSV)
      }

      return this.field.options.uploadURL
    }
  },
  beforeCreate() {
    /* 这里不能访问方法和属性！！ */
  },

  created() {
    /* 注意：子组件mounted在父组件created之后、父组件mounted之前触发，故子组件mounted需要用到的prop
         需要在父组件created中初始化！！ */
    this.initFieldModel()
    this.registerToRefList()
    this.initEventHandler()
    this.buildFieldRules()
    this.handleOnCreated()
  },

  mounted() {
    this.handleOnMounted()
    if (!this.fieldModel) {
      this.fieldModel = []
    }
  },

  beforeDestroy() {
    this.unregisterFromRefList()
  },

  methods: {
    // 上传成功
    handleSuccess(fileList) {
      this.fieldModel = fileList
      let oldValue = deepClone(this.fieldModel)
      this.syncUpdateFormModel(this.fieldModel)
      this.emitFieldDataChange(this.fieldModel, oldValue)
      if (this.getReadMode()) {
        this.putUpdateField()
      }
    },

    handleFileExceed() {
      let uploadLimit = this.field.options.limit
      this.$message.warning(this.i18nt('render.hint.uploadExceed').replace('${uploadLimit}', uploadLimit))
    },

    beforeFileUpload(file) {
      let fileTypeCheckResult = false
      let extFileName = file.name.substring(file.name.lastIndexOf('.') + 1)
      if (!!this.field.options && !!this.field.options.fileTypes) {
        let uploadFileTypes = this.field.options.fileTypes
        if (uploadFileTypes.length > 0) {
          fileTypeCheckResult = uploadFileTypes.some((ft) => {
            return extFileName.toLowerCase() === ft.toLowerCase()
          })
        }
      }
      if (!fileTypeCheckResult) {
        this.$message.error(this.i18nt('render.hint.unsupportedFileType') + extFileName)
        return false
      }

      let fileSizeCheckResult = false
      let uploadFileMaxSize = 5 //5MB
      if (!!this.field.options && !!this.field.options.fileMaxSize) {
        uploadFileMaxSize = this.field.options.fileMaxSize
      }
      fileSizeCheckResult = file.size / 1024 / 1024 <= uploadFileMaxSize
      if (!fileSizeCheckResult) {
        this.$message.error(this.i18nt('render.hint.fileSizeExceed') + uploadFileMaxSize + 'MB')
        return false
      }

      this.uploadData.key = file.name
      return this.handleOnBeforeUpload(file)
    },

    handleOnBeforeUpload(file) {
      if (!!this.field.options.onBeforeUpload) {
        let bfFunc = new Function('file', this.field.options.onBeforeUpload)
        let result = bfFunc.call(this, file)
        if (typeof result === 'boolean') {
          return result
        } else {
          return true
        }
      }

      return true
    },

    updateFieldModelAndEmitDataChangeForUpload(fileList, customResult, defaultResult) {
      let oldValue = deepClone(this.fieldModel)
      if (!!customResult && !!customResult.name && !!customResult.url) {
        this.fieldModel.push({
          name: customResult.name,
          url: customResult.url
        })
      } else if (!!defaultResult && !!defaultResult.name && !!defaultResult.url) {
        this.fieldModel.push({
          name: defaultResult.name,
          url: defaultResult.url
        })
      } else {
        this.fieldModel = deepClone(fileList)
      }

      this.syncUpdateFormModel(this.fieldModel)
      this.emitFieldDataChange(this.fieldModel, oldValue)
    },

    handleFileUpload(res, file, fileList) {
      if (file.status === 'success') {
        let customResult = null
        if (!!this.field.options.onUploadSuccess) {
          let mountFunc = new Function('result', 'file', 'fileList', this.field.options.onUploadSuccess)
          customResult = mountFunc.call(this, res, file, fileList)
        }

        this.updateFieldModelAndEmitDataChangeForUpload(fileList, customResult, res)
        if (!!customResult && !!customResult.name) {
          file.name = customResult.name
        } else {
          file.name = file.name || res.name || res.fileName || res.filename
        }
        if (!!customResult && !!customResult.url) {
          file.url = customResult.url
        } else {
          file.url = file.url || res.url
        }
        this.fileList = deepClone(fileList)
        this.uploadBtnHidden = fileList.length >= this.field.options.limit
      }
    },

    updateFieldModelAndEmitDataChangeForRemove(deletedFileIdx, fileList) {
      let oldValue = deepClone(this.fieldModel)
      this.fieldModel.splice(deletedFileIdx, 1)
      this.syncUpdateFormModel(this.fieldModel)
      this.emitFieldDataChange(this.fieldModel, oldValue)
    },

    removeUploadFile(fileName, fileUrl, fileUid) {
      let foundIdx = -1
      let foundFile = null
      this.fileList.forEach((file, idx) => {
        if (file.name === fileName && (file.url === fileUrl || (!!fileUid && file.uid === fileUid))) {
          foundIdx = idx
          foundFile = file
        }
      })

      if (foundIdx >= 0) {
        this.fileList.splice(foundIdx, 1)
        this.updateFieldModelAndEmitDataChangeForRemove(foundIdx, this.fileList)
        this.uploadBtnHidden = this.fileList.length >= this.field.options.limit

        if (!!this.field.options.onFileRemove) {
          let customFn = new Function('file', 'fileList', this.field.options.onFileRemove)
          customFn.call(this, foundFile, this.fileList)
        }
      }
    },

    handleUploadError(err, file, fileList) {
      if (!!this.field.options.onUploadError) {
        let customFn = new Function('error', 'file', 'fileList', this.field.options.onUploadError)
        customFn.call(this, err, file, fileList)
      } else {
        this.$message({
          message: this.i18nt('render.hint.uploadError') + err,
          duration: 3000,
          type: 'error'
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../styles/global.scss'; //* form-item-wrapper已引入，还需要重复引入吗？ *//

.full-width-input {
  width: 100% !important;
}

.dynamicPseudoAfter ::v-deep .el-upload.el-upload--text {
  color: #409eff;
  font-size: 12px;
  .el-icon-plus:after {
    content: var(--select-file-action);
  }
}

.hideUploadDiv {
  ::v-deep div.el-upload--picture-card {
    /* 隐藏最后的图片上传按钮 */
    display: none;
  }

  ::v-deep div.el-upload--text {
    /* 隐藏最后的文件上传按钮 */
    display: none;
  }

  ::v-deep div.el-upload__tip {
    /* 隐藏最后的文件上传按钮 */
    display: none;
  }
}

.upload-file-list {
  font-size: 12px;

  .file-action {
    color: #409eff;
    margin-left: 5px;
    margin-right: 5px;
    cursor: pointer;
  }
}
</style>
@/utils/i18ns
