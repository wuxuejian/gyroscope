<template>
  <div class="Modal">
    <div class="colLeft">
      <div class="Nav">
        <div class="trees-coadd">
          <div v-if="isPage" class="tree_tit" @click="addSort">
            <i class="el-icon-circle-plus"></i>
            添加分类
          </div>
          <div class="scollhide">
            <div :class="isPage ? 'tree' : 'isTree'">
              <el-tree
                :current-node-key="treeId"
                :data="treeData"
                :expand-on-click-node="false"
                default-expand-all
                highlight-current
                node-key="id"
                @node-click="appendBtn"
              >
                <span slot-scope="{ data }" class="custom-tree-node">
                  <span class="file-name">
                    <img v-if="!data.pid" class="icon" src="@/assets/images/file.jpg" />
                    <span class="name line1">{{ data.cate_name }}</span>
                  </span>
                  <span>
                    <el-dropdown @command="(command) => clickMenu(data, command)">
                      <i class="el-icon-more el-icon--right"></i>
                      <template slot="dropdown">
                        <el-dropdown-menu>
                          <el-dropdown-item command="1">新增分类</el-dropdown-item>
                          <el-dropdown-item v-if="data.id" command="2">编辑分类</el-dropdown-item>
                          <el-dropdown-item v-if="data.id" command="3">删除</el-dropdown-item>
                        </el-dropdown-menu>
                      </template>
                    </el-dropdown>
                  </span>
                </span>
              </el-tree>
            </div>
          </div>
        </div>
      </div>
      <div class="conter">
        <div class="bnt acea-row row-middle df-jcsb">
          <div>
            <el-button
              v-if="isShow !== 0"
              :disabled="checkPicList.length === 0"
              size="small"
              type="primary"
              @click="checkPics"
              >使用选中图片</el-button
            >
            <el-upload
              ref="upload"
              :action="fileUrl"
              :before-upload="beforeUpload"
              :data="uploadData"
              :headers="header"
              :limit="limit"
              :multiple="true"
              :on-change="fileChange"
              :on-success="uploadSuccess"
              :show-file-list="false"
              class="upload-demo mr10 mb15 upload-btn"
            >
              <el-button size="small" type="primary">上传图片</el-button>
            </el-upload>
            <el-button
              :disabled="!checkPicList.length && !ids.length"
              class="mr10"
              size="small"
              type="error"
              @click.stop="editPicList()"
              >删除图片</el-button
            >
            <el-cascader
              v-model="pids"
              :options="treeData2"
              :props="{ checkStrictly: true, emitPath: false, label: 'cate_name', value: 'id' }"
              class="treeSel"
              clearable
              placeholder="图片移动至"
              size="small"
              style="width: 150px"
              @visible-change="moveImg"
            ></el-cascader>
          </div>
          <div v-if="isPage">
            <el-input
              v-model="fileData.real_name"
              class="mr10"
              placeholder="请输入图片名"
              size="small"
              style="width: 150px"
            >
              <i slot="suffix" class="el-icon-search el-input__icon" @click="getFileList"></i>
            </el-input>
            <el-radio-group v-model="lietStyle" size="small" @input="radioChange">
              <el-radio-button label="list">
                <i class="el-icon-menu"></i>
              </el-radio-button>
              <el-radio-button label="table">
                <!-- <i class="el-icon-files"></i> -->
                <span class="iconfont iconliebiao"></span>
              </el-radio-button>
            </el-radio-group>
          </div>
        </div>
        <div :class="{ 'is-modal': !isPage }" class="pictrueList acea-row">
          <div v-if="lietStyle == 'list'" style="width: 100%">
            <div v-show="isShowPic" class="imagesNo">
              <i class="el-icon-picture" style="color: #dbdbdb; font-size: 60px"></i>
              <span class="imagesNo_sp">图片库为空</span>
            </div>
            <div ref="imgListBox" class="acea-row mb10">
              <div
                v-for="(item, index) in pictrueList"
                :key="index"
                :style="{ margin: picmargin }"
                class="pictrueList_pic mb10 mt10"
                @mouseenter="enterMouse(item)"
                @mouseleave="enterMouse(item)"
              >
                <p v-if="item.num > 0" class="number">
                  <el-badge :value="item.num" type="primary">
                    <a class="demo-badge" href="#"></a>
                  </el-badge>
                </p>
                <div :class="item.isSelect ? 'on' : ''" class="img">
                  <img v-lazy="item.att_dir" @click.stop="changImage(item, index, pictrueList)" />
                </div>

                <div class="operate-item" @mouseenter="enterLeave(item)" @mouseleave="enterLeave(item)">
                  <p v-if="!item.isEdit">
                    {{ item.editName }}
                  </p>
                  <el-input v-else v-model="item.real_name" size="small" type="text" @blur="bindTxt(item)" />
                  <div class="operate-height">
                    <span v-if="item.isShowEdit" class="operate mr10" @click="editPicList(item.id)">删除</span>
                    <span v-if="item.isShowEdit" class="operate mr10" @click="item.isEdit = !item.isEdit">改名</span>
                    <span v-if="item.isShowEdit" class="operate" @click="handlePictureCardPreview(item)">查看</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <el-table
            v-if="lietStyle == 'table'"
            ref="table"
            v-loading="loading"
            :data="pictrueList"
            :row-key="getRowKey"
            highlight-row
            no-data-text="暂无数据"
            no-filtered-data-text="暂无筛选结果"
            @selection-change="handleSelectRow"
          >
            <el-table-column :reserve-selection="true" type="selection" width="60"> </el-table-column>
            <el-table-column label="图片名称" min-width="190">
              <template slot-scope="scope">
                <div class="df-aic">
                  <div v-viewer class="tabBox_img mr10">
                    <img v-lazy="scope.row.att_dir" />
                  </div>
                  <span v-if="!scope.row.isEdit" class="line2 real-name">{{ scope.row.real_name }}</span>
                  <el-input
                    v-else
                    v-model="scope.row.real_name"
                    size="small"
                    style="width: 90%"
                    type="text"
                    @blur="bindTxt(scope.row)"
                  />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="上传时间" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.time }}</span>
              </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="170">
              <template slot-scope="scope">
                <a @click="editPicList(scope.row)">删除</a>
                <el-divider direction="vertical"></el-divider>
                <a @click="scope.row.isEdit = !scope.row.isEdit">{{ scope.row.isEdit ? '确定' : '重名命' }}</a>
                <el-divider direction="vertical"></el-divider>
                <a @click="handlePictureCardPreview(scope.row)">查看</a>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="footer acea-row row-right">
          <el-pagination
            v-if="total"
            :current-page="fileData.page"
            :page-size="fileData.limit"
            :pageCount="9"
            :total="total"
            layout="total, prev, pager, next"
            @current-change="pageChange"
          />
        </div>
      </div>
    </div>
    <el-image-viewer v-if="isImage" :initialIndex="imgIndex" :on-close="closeImageViewer" :url-list="imgList" />
  </div>
</template>

<script>
import {
  formatLstApi,
  attachmentCreateApi,
  attachmentUpdateApi,
  picNameEditApi,
  attachmentDeleteApi,
  attachmentListApi,
  picDeleteApi,
  categoryApi
} from '@/api/system'
import { VueTreeList } from 'vue-tree-list'
import { getToken } from '@/utils/auth'
import SettingMer from '@/libs/settingMer'
import Tips from '@/utils/tips'
import compressImg from '@/utils/compressImg'
export default {
  name: 'uploadPictures',
  components: {
    VueTreeList,
    ElImageViewer: () => import('element-ui/packages/image/src/image-viewer')
  },
  props: {
    isChoice: {
      type: String,
      default: ''
    },
    isPage: {
      type: Boolean,
      default: false
    },
    isIframe: {
      type: Boolean,
      default: false
    },
    gridBtn: {
      type: Object,
      default: null
    },
    gridPic: {
      type: Object,
      default: null
    },
    isShow: {
      type: Number,
      default: 1
    },
    pageLimit: {
      type: Number,
      default: 0
    },
    maxNum: {
      type: Number,
      default: 0
    },
    multiple: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      spinShow: false,
      fileUrl: `${SettingMer.https}/system/attach/upload`,
      modalPic: false,
      treeData: [],
      treeData2: [],
      pictrueList: [],
      uploadData: {}, // 上传参数
      checkPicList: [],
      uploadName: {
        name: '',
        all: 1
      },
      formValidate: { id: 0 },
      treeId: '',

      fileData: {
        cid: 0,
        real_name: '',
        page: 1,
        limit: this.pageLimit || 24
      },
      total: 0,
      pids: 0,
      list: [],
      isShowPic: false,
      header: {},
      ids: [], // 选中附件的id集合
      lietStyle: 'list',
      loading: false,
      multipleSelection: [],
      picmargin: '5px', //默认距离右边距离
      isImage: false,
      imgList: [],
      imgIndex: 0,
      ruleForm: {
        type: 0,
        region: '',
        imgList: []
      },
      limit: 20
    }
  },
  mounted() {
    if (this.isPage) {
      let hang = parseInt((document.body.clientHeight - this.$refs.imgListBox.clientHeight - 325) / 180) //计算行数
      let col = parseInt(this.$refs.imgListBox.clientWidth / 156) //计算列数
      this.fileData.limit = col * hang //计算分页数量
      this.picmargin = parseInt(this.$refs.imgListBox.clientWidth - col * 146) / (2 * col) + 'px' //平均分布计算margin距离
    }
    this.getToken()
    this.getList()
    this.getFileList()
  },
  methods: {
    radioChange() {
      this.initData()
    },
    async fileChange(file, fileList) {
      if (file.size >= 2097152) {
        await this.comImg(file.raw).then((res) => {
          fileList.map((e) => {
            if (e.uid === file.uid) {
              e.raw = res
            }
          })
          this.ruleForm.imgList = fileList
        })
      } else {
        this.ruleForm.imgList = fileList
      }
    },
    comImg(file) {
      return new Promise((resolve, reject) => {
        compressImg(file).then((res) => {
          resolve(res)
        })
      })
    },
    onDel(node) {
      let method = node.cate_id ? routeDel : routeCateDel
      this.$msgbox({
        title: '提示',
        message: '是否确定删除该菜单',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '删除',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel'
      })
        .then(() => {
          method(node.id)
            .then((res) => {
              this.$message.success(res.msg)
              node.remove()
            })
            .catch((err) => {
              this.$message.error(err)
            })
        })
        .catch(() => {})
    },
    // 添加分类
    addSort() {
      this.append({ id: this.treeId || 0 })
    },
    // 点击菜单
    clickMenu(data, name) {
      if (name == 1) {
        this.append(data)
      } else if (name == 2) {
        this.editPic(data)
      } else if (name == 3) {
        this.remove(data, '分类')
      }
    },
    uploadSuccess() {
      this.fileData.page = 1
      this.initData()
      this.getFileList()
    },
    uploadModal() {
      this.$refs.upload.uploadModal = true
    },
    enterMouse(item) {
      item.realName = !item.realName
    },
    enterLeave(item) {
      item.isShowEdit = !item.isShowEdit
    },
    // 上传头部token
    getToken() {
      this.header['Authorization'] = 'Bearer ' + getToken()
    },
    moveImg(status) {
      if (!status) {
        this.getMove()
      } else {
        if (!this.ids.toString()) {
          this.$message.warning('请先选择图片')
          return
        }
      }
    },
    searchImg() {},
    // 移动分类
    async getMove() {
      let data = {
        pid: this.pids,
        images: this.ids.toString()
      }
      if (!data.images) return
      await categoryApi(data)
      await this.getFileList()
      this.pids = 0
      this.checkPicList = []
      this.ids = []
    },
    delImg(id) {
      let ids = {
        ids: id
      }
      let delfromData = {
        title: '删除选中图片',
        url: `file/file/delete`,
        method: 'POST',
        ids: ids
      }
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg)
          this.getFileList()
          this.checkPicList = []
        })
        .catch((res) => {
          this.$message.error(res.msg)
        })
    },
    // 删除图片
    async editPicList(id) {
      let ids = {
        ids: id || this.ids.toString()
      }
      await Tips.confirm({ message: '此操作将永久删除该文件, 是否继续?' })
      await picDeleteApi(ids)
      await this.getFileList()
      await this.initData()
    },
    initData() {
      this.checkPicList = []
      this.ids = []
      this.multipleSelection = []
    },
    // 鼠标移入 移出
    onMouseOver(root, node, data) {
      event.preventDefault()
      data.flag = !data.flag
      if (data.flag2) {
        data.flag2 = false
      }
    },
    // 点击树
    appendBtn(data) {
      this.treeId = data.id
      this.fileData.page = 1
      this.getFileList()
    },
    // 点击添加
    append(data) {
      this.treeId = data.id
      this.getFrom()
    },
    // 删除分类
    async remove(data, tit) {
      await Tips.confirm({ message: '此操作将永久删除 [ ' + data.cate_name + ' ] ' + '分类, 是否继续?' })
      await attachmentDeleteApi(data.id)
      this.checkPicList = []
      await this.getList()
    },
    // 编辑树表单
    editPic(data) {
      this.$modalForm(attachmentUpdateApi(data.id)).then(() => this.getList())
    },
    // 搜索分类
    changePage() {
      this.getList('search')
    },
    // 分类列表树
    async getList(type) {
      const data = {
        cate_name: '全部图片',
        id: '',
        pid: 0
      }
      const res = await formatLstApi(this.uploadName)
      if (type !== 'search') {
        this.treeData2 = JSON.parse(JSON.stringify([...res.data]))
      }
      res.data.unshift(data)
      this.treeData = res.data
    },
    loadData(item, callback) {
      attachmentListApi({
        pid: item.id
      })
        .then(async (res) => {
          const data = res.data.list
          callback(data)
        })
        .catch((res) => {})
    },
    addFlag(treedata) {
      treedata.map((item) => {
        this.$set(item, 'flag', false)
        this.$set(item, 'flag2', false)
        item.children && this.addFlag(item.children)
      })
    },
    // 新建分类
    add() {
      this.treeId = 0
      this.getFrom()
    },
    // 文件列表
    async getFileList() {
      this.fileData.cid = this.treeId
      this.imgList = []
      const res = await attachmentListApi(this.fileData)
      res.data.list.forEach((el) => {
        el.isSelect = false
        el.isEdit = false
        el.isShowEdit = false
        el.realName = false
        el.num = 0
        this.editName(el)
        this.imgList.push(el.att_dir)
      })
      this.pictrueList = res.data.list

      if (this.pictrueList.length) {
        this.isShowPic = false
      } else {
        this.isShowPic = true
      }
      this.total = res.data.count
      this.$nextTick(() => {
        //确保dom加载完毕
        // this.showSelectData();
      })
    },
    handlePictureCardPreview(item) {
      this.$nextTick(() => {
        this.imgIndex = this.pictrueList.findIndex((e) => e.att_dir === item.att_dir)
      })
      this.isImage = true
    },
    closeImageViewer() {
      this.isImage = false
    },
    showSelectData() {
      if (this.multipleSelection.length > 0) {
        // 判断是否存在勾选过的数据
        this.pictrueList.forEach((row) => {
          // 获取数据列表接口请求到的数据
          this.multipleSelection.forEach((item) => {
            // 勾选到的数据
            if (row.att_id === item.id) {
              // this.$refs.table.toggleRowSelection(item, true); // 若有重合，则回显该条数据
            }
          })
        })
      }
    },
    getRowKey(row) {
      return row.att_id
    },
    //对象数组去重；
    unique(arr) {
      return arr.reduce((acc, curr) => {
        const x = acc.find((item) => item.att_id === curr.att_id)
        if (!x) {
          return acc.concat([curr])
        } else {
          return acc
        }
      }, [])
    },
    //  选中某一行
    handleSelectRow(selection) {
      let arr = this.unique(selection)
      const uniqueArr = []
      const ids = []
      for (let i = 0; i < arr.length; i++) {
        const item = arr[i]
        if (!ids.includes(item.att_id)) {
          uniqueArr.push(item)
          ids.push(item.att_id)
        }
      }
      this.ids = ids
      this.multipleSelection = uniqueArr
    },
    pageChange(index) {
      this.fileData.page = index
      this.getFileList()
      this.checkPicList = []
    },
    // 新建分类表单
    getFrom() {
      this.$modalForm(attachmentCreateApi({ id: this.treeId })).then((res) => {
        this.getList()
      })
    },
    // 上传之前
    beforeUpload(file) {
      if (!/image\/\w+/.test(file.type)) {
        this.$message.error('请上传以jpg、jpeg、png等结尾的图片文件') //FileExt.toLowerCase()
        return false
      }
      this.uploadData = {
        cid: this.treeId
      }
      return new Promise((resolve) => {
        this.$nextTick(function () {
          resolve(true)
        })
      })
    },
    // 上传成功
    handleSuccess(res, file, fileList) {
      if (res.status === 200) {
        this.$message.success(res.msg)
        this.fileData.page = 1
        this.getFileList()
      } else {
        this.$message.error(res.msg)
      }
    },
    // 关闭
    cancel() {
      this.$emit('changeCancel')
    },
    // 选中图片
    changImage(item, index, row) {
      let activeIndex = 0
      if (!item.isSelect) {
        item.isSelect = true
        this.checkPicList.push(item)
      } else {
        item.isSelect = false
        this.checkPicList.map((el, index) => {
          if (el.id == item.id) {
            activeIndex = index
          }
        })
        this.checkPicList.splice(activeIndex, 1)
      }

      this.ids = []
      this.checkPicList.map((item, i) => {
        this.ids.push(item.id)
      })
      this.pictrueList.map((el, i) => {
        if (el.isSelect) {
          this.checkPicList.filter((el2, j) => {
            if (el.id == el2.id) {
              el.num = j + 1
            }
          })
        } else {
          el.num = 0
        }
      })
    },
    // 点击使用选中图片
    checkPics() {
      if (this.$route && this.$route.query.field) {
        if (this.checkPicList.length > 1) {
          return this.$message.warning('最多只能选一张图片')
        }

        form_create_helper.set(this.$route.query.field, this.checkPicList[0].att_dir)
        form_create_helper.close(this.$route.query.field)
      }
      if (this.maxNum > 0) {
        if (this.checkPicList.length > this.maxNum) return this.$message.warning('最多只能选' + this.maxNum + '张图片')
        this.$emit('getImage', this.checkPicList)
      } else if (this.multiple) {
        let maxLength = this.$route.query.maxLength
        if (maxLength !== undefined && this.checkPicList.length > Number(maxLength))
          return this.$message.warning('最多只能选' + maxLength + '张图片')
        this.$emit('getImage', this.checkPicList)
      } else {
        if (this.checkPicList.length > 1) return this.$message.warning('最多只能选一张图片')
        this.$emit('getImage', this.checkPicList[0])
      }
    },
    editName(item) {
      let it = item.real_name.split('.')
      let it1 = it[1] == undefined ? [] : it[1]
      let len = it[0].length + it1.length
      item.editName = len < 10 ? item.real_name : item.real_name.substr(0, 4) + '...' + item.real_name.substr(-5, 5)
    },
    // 修改图片名称
    async bindTxt(item) {
      if (!item.real_name) {
        this.$message.error('请填写名称内容')
      }
      await picNameEditApi(item.id, { real_name: item.real_name })
      await this.editName(item)
      item.isEdit = false
    }
  }
}
</script>

<style lang="scss" scoped>
.nameStyle {
  position: absolute;
  white-space: nowrap;
  z-index: 9;
  background: #eee;
  height: 20px;
  line-height: 20px;
  color: #555;
  border: 1px solid #ebebeb;
  padding: 0 5px;
  left: 56px;
  bottom: -18px;
}

.iconbianji1 {
  font-size: 13px;
}

.selectTreeClass {
  background: #d5e8fc;
}
.tree_tit {
  padding-top: 7px;
}
.treeBox {
  width: 100%;
  height: 100%;
  max-width: 180px;
}
.is-modal .pictrueList_pic {
  width: 100px;
  margin: 10px 5px !important;
  .img {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    background-color: rgb(248, 248, 248);
    padding: 2px;
    img {
      max-width: 96px;
      max-height: 96px;
      // object-fit: cover;
    }
    .operate-height {
      bottom: -8px;
    }
  }
}
.pictrueList_pic {
  position: relative;
  width: 146px;
  cursor: pointer;
  // margin-right: 20px !important;
  .img {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 146px;
    height: 146px;
    background-color: rgb(248, 248, 248);
    padding: 3px;
    img {
      max-width: 140px;
      max-height: 140px;
      // object-fit: cover;
    }
  }

  p {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    height: 20px;
    font-size: 12px;
    color: #515a6d;
    text-align: center;
  }

  .number {
    height: 33px;
  }

  .number {
    position: absolute;
    right: 0;
    top: 0;
  }
  ::v-deep .el-badge__content.is-fixed {
    top: 13px;
    right: 25px;
  }
}
.Nav {
  width: 100%;
  border-right: 1px solid #eee;
  min-width: 220px;
  max-width: max-content;
}
.trees-coadd {
  width: 100%;
  border-radius: 4px;
  overflow: hidden;
  position: relative;

  .scollhide {
    overflow-x: hidden;
    overflow-y: scroll;
    padding: 0px 0 10px 0;
    box-sizing: border-box;

    .isTree {
      min-height: 374px;
      max-height: 550px;
      ::v-deep .file-name {
        display: flex;
        align-items: center;
        .name {
          max-width: 7em;
        }
        .icon {
          width: 12px;
          height: 12px;
          margin-right: 8px;
        }
      }
      ::v-deep .el-tree-node {
        margin-right: 16px;
      }
      ::v-deep .el-tree-node__children .el-tree-node {
        margin-right: 0;
      }
      ::v-deep .el-tree-node__content {
        width: 100%;
        height: 36px;
      }
      ::v-deep .custom-tree-node {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-right: 20px;
        font-size: 13px;
        font-weight: 400;
        color: rgba(0, 0, 0, 0.6);
        line-height: 13px;
      }
      ::v-deep .is-current {
        background: #f1f9ff !important;
        color: var(--prev-color-primary) !important;
      }
      ::v-deep .is-current .custom-tree-node {
        color: var(--prev-color-primary) !important;
      }
    }
  }

  .scollhide::-webkit-scrollbar {
    display: none;
  }
}

.treeSel ::v-deep .ivu-select-dropdown-list {
  padding: 0 5px !important;
  box-sizing: border-box;
  width: 200px;
}
.imagesNo {
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: center;
  margin: 65px 0;

  .imagesNo_sp {
    font-size: 13px;
    color: #dbdbdb;
    line-height: 3;
  }
}

.Modal {
  width: 100%;
  height: 100%;
  background: #fff !important;
}
.fill-window {
  height: 100vh;
}
.colLeft {
  padding-right: 0 !important;
  height: 100%;
  display: flex;
  flex-wrap: nowrap;
}

.conter {
  width: 100%;
  height: 100%;
  margin-left: 20px !important;
  .iconliebiao {
    font-size: 12px;
  }
}

.conter .bnt {
  // justify-content: flex-end;
  width: 100%;
  height: 32px;
  margin-bottom: 20px;
  // padding: 0 13px 20px 0px;
  box-sizing: border-box;
  .upload-btn {
    display: inline-block;
  }
}

.conter .pictrueList {
  // width: 100%;
  overflow-x: hidden;
  overflow-y: auto;
  min-height: 463px;
}
.conter .pictrueList.is-modal {
  max-height: 480px;
}
.right-col {
  // flex: 1;
}
.conter .pictrueList img {
  max-width: 100%;
}
.conter .pictrueList .img.on {
  border: 2px solid var(--prev-color-primary);
}

.conter .footer {
  padding: 0 20px 10px 20px;
}
.tabBox_img {
  display: flex;
  align-items: center;
}
.real-name {
  flex: 1;
}
.df-aic {
  display: flex;
  align-items: center;
}
.demo-badge {
  width: 42px;
  height: 42px;
  background: transparent;
  border-radius: 6px;
  display: inline-block;
}

.bnt ::v-deep .ivu-tree-children {
  padding: 5px 0;
}

.card-tree {
  background: #fff;
  height: 72px;
  box-sizing: border-box;
  overflow-x: scroll; /* 设置溢出滚动 */
  white-space: nowrap;
  overflow-y: hidden;
  /* 隐藏滚动条 */
  border-radius: 4px;
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
}
.card-tree::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}
.tabs {
  background: #fff;
  padding-top: 10px;
  border-radius: 5px 5px 0 0;
}
.operate-item {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  margin: 5px 0;
}
.operate-height {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 16px;
  position: absolute;
  bottom: -10px;
}
.operate {
  color: var(--prev-color-primary);
  font-size: 12px;
  white-space: nowrap;
}
</style>
