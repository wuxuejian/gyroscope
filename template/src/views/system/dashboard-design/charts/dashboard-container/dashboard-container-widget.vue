<template>
  <span>
    <smart-widget-grid
      :layout="gridLayout"
      v-if="widget.widgetList.length <= 0"
      :row-height="48"
      :margin="[15, 15]"
      :is-static="false"
      :auto-size="true"
    >
      <template #[defaultSlotName]>
        <smart-widget title="操作提示" refresh fullscreen>
          <div class="layout-center">
            <h3>选择左侧图表组件，鼠标点击即可加入画布</h3>
          </div>
        </smart-widget>
      </template>
    </smart-widget-grid>
    <smart-widget-grid
      :layout="gridLayout"
      v-if="widget.widgetList.length > 0"
      :row-height="48"
      :margin="[15, 15]"
      :is-static="false"
      :auto-size="true"
    >
      <template v-for="(item, index) in widget.widgetList" #[item.id]>
        <smart-widget
          :simple="!item.options.showHeader"
          :title="item.options.label"
          :refresh="item.options.showRefresh"
          :fullscreen="item.options.showFullscreen"
          :isActived="designer.selectedWidget && designer.selectedWidget.id == item.id"
          @on-refresh="onRefresh(item)"
          @on-fullscreen="onFullscreen(item)"
        >
          <div class="container-com">
            <template v-if="'container' === item.category">
              <component
                :is="item.type + '-widget'"
                :widget="item"
                :designer="designer"
                :key="item.id"
                :parent-list="widget.widgetList"
                :index-of-parent-list="index"
                :parent-widget="widget"
              ></component>
            </template>
            <template v-else>
              <component
                :layout="gridLayout"
                :is="item.type + '-widget'"
                :field="item"
                :designer="designer"
                :key="item.id"
                :parent-list="widget.widgetList"
                :index-of-parent-list="index"
                :parent-widget="widget"
                :design-state="true"
              ></component>
            </template>
            <span
              v-if="designer.selectedWidget && designer.selectedWidget.id == item.id"
              class="del-span"
              @click="removeSelectedChart(index, item.id)"
            >
              <el-icon class="el-icon-delete" size="20"></el-icon>
            </span>
          </div>
        </smart-widget>
      </template>
    </smart-widget-grid>
  </span>
</template>

<script>
import { traverseWidgetsOfContainer } from '../../utils'
export default {
  name: 'dashboard-container-widget',
  props: {
    widget: Object,
    parentWidget: Object,
    parentList: Array,
    indexOfParentList: Number,
    designer: Object
  },
  data() {
    return {
      defaultSlotName: '0'
    }
  },
  computed: {
    gridLayout() {
      if (this.widget.widgetList.length <= 0) {
        return [{ x: 0, y: 0, w: 4, h: 4, i: '0' }]
      } else {
        return this.widget.options.layout
      }
    }
  },
  methods: {
    onRefresh(item) {
      this.$set(item.options, 'isRefresh', !item.options.isRefresh)
    },
    onFullscreen(item) {
      setTimeout(() => {
        this.$set(item.options, 'isRefresh', !item.options.isRefresh)
      }, 200)
    },
    removeSelectedChart(chartIndex, chartId) {
      let delInx
      this.widget.options.layout.forEach((el, inx) => {
        if (el.i == chartId) {
          delInx = inx
        }
      })
      this.widget.options.layout.splice(delInx, 1)
      const chartList = this.widget.widgetList
      if (chartList.length > 0) {
        const widgetRefName = this.designer.selectedWidgetName
        const childrenRefNames = []
        const fwHandler = (fw) => {
          childrenRefNames.push(fw.options.name)
        }
        const cwHandler = (cw) => {
          childrenRefNames.push(cw.options.name)
        }
        traverseWidgetsOfContainer(this.designer.selectedWidget, fwHandler, cwHandler)

        let nextSelected = null
        if (chartList.length === 1) {
          //
        } else if (chartList.length === 1 + chartIndex) {
          nextSelected = chartList[chartIndex - 1]
        } else {
          nextSelected = chartList[chartIndex + 1]
        }

        this.$nextTick(() => {
          chartList.splice(chartIndex, 1)
          this.designer.setSelected(nextSelected)

          this.designer.formWidget.deleteWidgetRef(widgetRefName)
          this.designer.formWidget.deletedChildrenRef(childrenRefNames)
          this.designer.emitHistoryChange()
        })
      }
    }
  }
}
</script>

<style scoped lang="scss">
.container-com {
  width: 100%;
  height: 100%;
  position: relative;
  .del-span {
    position: absolute;
    right: -11px;
    bottom: -2px;
    color: #909399;
    cursor: pointer;
    &:hover {
      color: #606266;
    }
  }
}
</style>
