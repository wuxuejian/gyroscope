<template>
  <form-create
    class="_fc-validate"
    :rule="rule"
    :option="option"
    :value="formValue"
    @update:value="onInput"
  ></form-create>
</template>

<script>
export default {
  name: 'Validate',
  props: {
    value: Array,
  },
  watch: {
    value(n) {
      this.formValue = this.parseValue(n);
    },
  },
  data() {
    return {
      formValue: {},
      option: {
        form: {
          labelPosition: 'top',
          size: 'mini',
          labelWidth: '90px',
        },
        submitBtn: false,
        formData: this.parseValue(this.value),
      },
      rule: [
        {
          type: 'select',
          field: 'type',
          value: undefined,
          title: '字段类型',
          options: [
            { value: undefined, label: '请选择' },
            { value: 'string', label: 'String' },
            { value: 'array', label: 'Array' },
            { value: 'number', label: 'Number' },
            { value: 'integer', label: 'Integer' },
            { value: 'float', label: 'Float' },
            { value: 'object', label: 'Object' },
            { value: 'date', label: 'Date' },
            { value: 'url', label: 'url' },
            { value: 'hex', label: 'hex' },
            { value: 'email', label: 'email' },
          ],
          control: [
            {
              handle: (v) => {
                return !!v;
              },
              rule: [
                {
                  type: 'group',
                  field: 'validate',
                  props: {
                    expand: 1,
                    rules: [
                      {
                        type: 'select',
                        title: '触发方式',
                        field: 'trigger',
                        value: 'change',
                        options: [
                          { label: 'change', value: 'change' },
                          { label: 'submit', value: 'submit' },
                          { label: 'blur', value: 'blur' },
                        ],
                      },
                      {
                        type: 'select',
                        title: '验证方式',
                        field: 'mode',
                        options: [
                          { value: 'required', label: '必填' },
                          { value: 'pattern', label: '正则表达式' },
                          { value: 'min', label: '最小值' },
                          { value: 'max', label: '最大值' },
                          { value: 'len', label: '长度' },
                        ],
                        value: 'required',
                        control: [
                          {
                            value: 'required',
                            rule: [
                              {
                                type: 'hidden',
                                field: 'required',
                                value: true,
                              },
                            ],
                          },
                          {
                            value: 'pattern',
                            rule: [
                              {
                                type: 'input',
                                field: 'pattern',
                                title: '正则表达式',
                              },
                            ],
                          },
                          {
                            value: 'min',
                            rule: [
                              {
                                type: 'inputNumber',
                                field: 'min',
                                title: '最小值',
                              },
                            ],
                          },
                          {
                            value: 'max',
                            rule: [
                              {
                                type: 'inputNumber',
                                field: 'max',
                                title: '最大值',
                              },
                            ],
                          },
                          {
                            value: 'len',
                            rule: [
                              {
                                type: 'inputNumber',
                                field: 'len',
                                title: '长度',
                              },
                            ],
                          },
                        ],
                      },
                      {
                        type: 'input',
                        title: '错误信息',
                        field: 'message',
                        value: '',
                      },
                    ],
                  },
                  value: [],
                },
              ],
            },
          ],
        },
      ],
    };
  },
  methods: {
    onInput: function (formData) {
      let val = [];
      const { validate, type } = formData;
      if (type && !validate.length) {
        return;
      } else if (type) {
        validate.forEach((v) => {
          v.type = type;
        });
        val = [...validate];
      }
      this.$emit('input', val);
    },
    parseValue(n) {
      let val = {
        validate: n ? [...n] : [],
        type: n.length ? n[0].type : undefined,
      };
      val.validate.forEach((v) => {
        if (!v.mode) {
          Object.keys(v).forEach((k) => {
            if (['message', 'type', 'trigger', 'mode'].indexOf(k) < 0) {
              v.mode = k;
            }
          });
        }
      });

      return val;
    },
  },
};
</script>

<style>
._fc-validate .form-create .el-form-item {
  margin-bottom: 22px !important;
}
</style>
