<template>
  <el-form-item :label="i18nt('designer.setting.multiple')" v-if="!optionModel.disableMultiple">
    <el-switch v-model="optionModel.multiple"></el-switch>
  </el-form-item>
</template>

<script>
import i18n from '@/utils/i18n'
import propertyMixin from '@/components/form-designer/setting-panel/property-editor/propertyMixin'

export default {
  name: 'cascader-multiple-editor',
  mixins: [i18n, propertyMixin],
  props: {
    designer: Object,
    selectedWidget: Object,
    optionModel: Object
  }
}
</script>

<style scoped>
</style>
@/utils/i18ns
