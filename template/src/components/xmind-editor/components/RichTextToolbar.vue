<template>
  <div class="richTextToolbar" ref="richTextToolbar" :style="style" @click.stop.passive v-show="showRichTextToolbar">
    <el-tooltip content="加粗" placement="top">
      <div class="btn" :class="{ active: formatInfo.bold }" @click="toggleBold">
        <span class="icon xmind-iconfont iconzitijiacu"></span>
      </div>
    </el-tooltip>

    <el-tooltip content="斜体" placement="top">
      <div class="btn" :class="{ active: formatInfo.italic }" @click="toggleItalic">
        <span class="icon xmind-iconfont iconzitixieti"></span>
      </div>
    </el-tooltip>

    <el-tooltip content="下划线" placement="top">
      <div class="btn" :class="{ active: formatInfo.underline }" @click="toggleUnderline">
        <span class="icon xmind-iconfont iconzitixiahuaxian"></span>
      </div>
    </el-tooltip>

    <el-tooltip content="删除线" placement="top">
      <div class="btn" :class="{ active: formatInfo.strike }" @click="toggleStrike">
        <span class="icon xmind-iconfont iconshanchuxian"></span>
      </div>
    </el-tooltip>

    <el-tooltip content="字体" placement="top">
      <el-popover placement="bottom" trigger="hover">
        <div class="fontOptionsList">
          <div class="fontOptionItem" v-for="item in fontFamilyList" :key="item.value"
            :style="{ fontFamily: item.value }" :class="{ active: formatInfo.font === item.value }"
            @click="changeFontFamily(item.value)">
            {{ item.name }}
          </div>
        </div>
        <div class="btn" slot="reference">
          <span class="icon xmind-iconfont iconxingzhuang-wenzi"></span>
        </div>
      </el-popover>
    </el-tooltip>

    <el-tooltip content="字号" placement="top">
      <el-popover placement="bottom" trigger="hover">
        <div class="fontOptionsList">
          <div class="fontOptionItem" v-for="item in fontSizeList" :key="item" :style="{
            fontSize: item + 'px',
            height: (item < 30 ? 30 : item + 10) + 'px'
          }" :class="{ active: formatInfo.size === item + 'px' }" @click="changeFontSize(item)">
            {{ item }}px
          </div>
        </div>
        <div class="btn" slot="reference">
          <span class="icon xmind-iconfont iconcase fontColor"></span>
        </div>
      </el-popover>
    </el-tooltip>

    <el-tooltip content="字体颜色" placement="top">
      <el-popover placement="bottom" trigger="hover">
        <Color :color="fontColor" @change="changeFontColor"></Color>
        <div class="btn" slot="reference" :style="{ color: formatInfo.color }">
          <span class="icon xmind-iconfont iconzitiyanse"></span>
        </div>
      </el-popover>
    </el-tooltip>

    <el-tooltip content="背景颜色" placement="top">
      <el-popover placement="bottom" trigger="hover">
        <Color :color="fontBackgroundColor" @change="changeFontBackgroundColor"></Color>
        <div class="btn" slot="reference">
          <span class="icon xmind-iconfont iconbeijingyanse"></span>
        </div>
      </el-popover>
    </el-tooltip>

    <el-tooltip content="清除样式" placement="top">
      <div class="btn" @click="removeFormat">
        <span class="icon xmind-iconfont iconqingchu"></span>
      </div>
    </el-tooltip>
  </div>
</template>

<script>
import { fontFamilyList, fontSizeList } from '../config'
import { RICH_TEXT_SELECTION_CHANGE } from '../event-constant';
import Color from './Color.vue'

export default {
  name: 'RichTextToolbar',
  components: {
    Color
  },
  props: {
    mindMap: {
      type: Object
    }
  },
  data() {
    return {
      fontSizeList,
      fontFamilyList,
      showRichTextToolbar: false,
      style: {
        left: 0,
        top: 0
      },
      fontColor: '',
      fontBackgroundColor: '',
      formatInfo: {}
    }
  },
  created() {
    this.$bus.$on(RICH_TEXT_SELECTION_CHANGE, this.onRichTextSelectionChange)
  },
  mounted() {
    document.body.append(this.$refs.richTextToolbar)
  },
  beforeDestroy() {
    this.$bus.$off(RICH_TEXT_SELECTION_CHANGE, this.onRichTextSelectionChange)
  },
  methods: {
    onRichTextSelectionChange(hasRange, rect, formatInfo) {
      if (hasRange) {
        this.style.left = rect.left + rect.width / 2 + 'px'
        this.style.top = rect.top - 60 + 'px'
        this.formatInfo = { ...(formatInfo || {}) }
      }
      this.showRichTextToolbar = hasRange
    },

    toggleBold() {
      this.formatInfo.bold = !this.formatInfo.bold
      this.mindMap.richText.formatText({
        bold: this.formatInfo.bold
      })
    },

    toggleItalic() {
      this.formatInfo.italic = !this.formatInfo.italic
      this.mindMap.richText.formatText({
        italic: this.formatInfo.italic
      })
    },

    toggleUnderline() {
      this.formatInfo.underline = !this.formatInfo.underline
      this.mindMap.richText.formatText({
        underline: this.formatInfo.underline
      })
    },

    toggleStrike() {
      this.formatInfo.strike = !this.formatInfo.strike
      this.mindMap.richText.formatText({
        strike: this.formatInfo.strike
      })
    },

    changeFontFamily(font) {
      this.formatInfo.font = font
      this.mindMap.richText.formatText({
        font
      })
    },

    changeFontSize(size) {
      this.formatInfo.size = size
      this.mindMap.richText.formatText({
        size: size + 'px'
      })
    },

    changeFontColor(color) {
      this.formatInfo.color = color
      this.mindMap.richText.formatText({
        color
      })
    },

    changeFontBackgroundColor(background) {
      this.formatInfo.background = background
      this.mindMap.richText.formatText({
        background
      })
    },

    removeFormat() {
      this.mindMap.richText.removeFormat()
    }
  }
}
</script>

<style lang="scss" scoped>
.richTextToolbar {
  position: fixed;
  z-index: 2000;
  height: 55px;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 8px;
  box-shadow: 0 2px 16px 0 rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  transform: translateX(-50%);

  .btn {
    width: 55px;
    height: 55px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;

    &:hover {
      background-color: #eefbed;
    }

    &.active {
      color: #12bb37;
    }

    .icon {
      font-size: 20px;

      &.fontColor {
        font-size: 26px;
      }
    }
  }
}

.fontOptionsList {
  width: 150px;



  .fontOptionItem {
    height: 30px;
    width: 100%;
    display: flex;
    align-items: center;
    cursor: pointer;

    &:hover {
      background-color: #f7f7f7;
    }

    &.active {
      color: #12bb37;
    }
  }
}
</style>
