<template>
  <div>
    <!-- <div class="text-ellipsis" v-if="column.type == 'Reference'">
      {{ row[column.prop].name }}
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'Status'">
      {{ row[column.prop].label }}
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'AreaSelect'">
      <span v-if="row[column.prop] && (row[column.prop][1] == '市辖区' || row[column.prop][1] == '县')"
        >{{ row[column.prop][0] }}-{{ row[column.prop][2] }}</span
      >
      <span v-else-if="row[column.prop] && (row[column.prop][1] != '市辖区' || row[column.prop][1] != '县')">{{
        row[column.prop].join('-')
      }}</span>
      <span v-else></span>
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'Percent'">
      {{ row[column.prop] ? row[column.prop] + '%' : '' }}
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'Option'">
      {{ row[column.prop] ? row[column.prop].label : '' }}
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'Boolean'">
      {{ row[column.prop] == null ? '' : row[column.prop] ? '是' : '否' }}
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'Money'">
      {{ numberToCurrencyNo(row[column.prop]) }}
    </div>
    <div class="text-ellipsis" v-else-if="column.type == 'Picture'" :title="'图片：' + row[column.prop].length">
      <img
        class="row-img"
        :src="formatUrl(img.url)"
        alt
        v-for="(img, inx) of row[column.prop]"
        :key="inx"
        @click.stop="previewImg(img.url, img.name)"
      />
    </div>

    <div class="text-ellipsis" v-else-if="column.type == 'File'" :title="'文件：' + row[column.prop].length">
      <template v-if="row[column.prop] && row[column.prop].length > 0">
        <span @click.stop="openFilesDialog(row[column.prop])" class="ml-a-span"
          >文件({{ row[column.prop].length }})</span
        >
      </template>

      <template v-else>
        <span
          class="ml-a-span"
          v-for="(field, inx) of row[column.prop]"
          :key="inx"
          @click.stop="downField(field.url, field.name)"
          >{{ field.name }}</span
        >
      </template>
    </div> -->
    <div class="text-ellipsis" v-if="column.type == 'cascader_radio'"">
      <span
          class="ml-a-span"
          v-for="(field, inx) of row[column.prop]"
          :key="inx"
          @click.stop="downField(field.url, field.name)"
          >{{ inx > 0 ? '/' : '' }}{{ field }}</span
        >
    </div>
    <div class="text-ellipsis" v-else>{{ row[column.prop] === undefined ? row.sumcol : row[column.prop] }}</div>

    <zbDialog v-model="filesDialog" title="文件下载" appendToBody width="400px">
      <el-scrollbar max-height="400px">
        <div v-for="(field, inx) of filesList" :key="inx" class="field-item">
          <span class="ml-a-span" @click.stop="downField(field.url, field.name)">{{ field.name }}</span>
        </div>
      </el-scrollbar>
    </zbDialog>
    <zbDialog v-model="previewDialog" title="点击图片预览" appendToBody width="400px">
      <div class="preview-box">
        <el-image
          :src="formatUrl(previewUrl)"
          :zoom-rate="1.2"
          :max-scale="7"
          :min-scale="0.2"
          :preview-src-list="[formatUrl(previewUrl)]"
          :initial-index="4"
          fit="cover"
        />
      </div>
    </zbDialog>
  </div>
</template>

<script>
import zbDialog from '@/components/zbDialog'
export default {
  components: {
    zbDialog
  },
  props: {
    row: {
      type: Object,
      default: () => ({})
    },
    column: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      filesDialog: false,
      filesList: [],
      previewUrl: '',
      previewDialog: false
    }
  },
  methods: {
    formatUrl(url) {
      return process.env.VUE_APP_BASE_API + url
    },
    downField(url, fileName) {
      window.open(process.env.VUE_APP_BASE_API + url + '?fileName=' + fileName)
    },
    openDetailDialog(row) {
      this.$emit('openDetailDialog', row)
    },
    openFilesDialog(list) {
      this.filesDialog = true
      this.filesList = [...list]
    },
    numberToCurrencyNo(value) {
      if (!value) return 0
      // 获取整数部分
      const intPart = Math.trunc(value)
      // 整数部分处理，增加,
      const intPartFormat = intPart.toString().replace(/(\d)(?=(?:\d{3})+$)/g, '$1,')
      // 预定义小数部分
      let floatPart = ''
      // 将数值截取为小数部分和整数部分
      const valueArray = value.toString().split('.')
      if (valueArray.length === 2) {
        // 有小数部分
        floatPart = valueArray[1].toString() // 取得小数部分
        return intPartFormat + '.' + floatPart
      }
      return intPartFormat + floatPart
    },
    previewImg(url) {
      this.previewUrl = url
      this.previewDialog = true
    }
  }
}
</script>

<style lang="scss" scoped>
.row-img {
  width: 40px;
  height: 40px;
  margin-right: 5px;
  cursor: pointer;
  border-radius: 5px;
  &:last-child {
    margin-right: 0px;
  }
}
.field-item {
  margin-bottom: 5px;
}
.preview-box {
  text-align: center;
}
</style>
