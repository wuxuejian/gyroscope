export default function field() {
  return [
    {
      type: 'input',
      field: 'field',
      value: '',
      title: '字段 ID',
      props: {
        disabled: true,
      },
    },
    {
      type: 'input',
      field: 'title',
      value: '',
      title: '标识名',
    },
    {
      type: 'input',
      field: 'info',
      value: '',
      title: '提示文字',
      props: {
        type: 'textarea',
      },
    },
    // {
    //       type: 'Struct',
    //       field: '_control',
    //       value: [],
    //       title: '联动数据',
    //       props: {
    //           defaultValue: [],
    //           validate(val) {
    //               if (!Array.isArray(val)) return false;
    //               if (!val.length) return true;
    //               return !val.some(({rule}) => {
    //                   return !Array.isArray(rule);
    //               });
    //           }
    //       }
    //   },
    // {
    //       type: 'col',
    //       props: {
    //           span: 24
    //       },
    //       children: [
    //           {
    //               type: 'el-button',
    //               props: {
    //                   type: 'primary',
    //                   size: 'mini',
    //                   icon: 'el-icon-delete',
    //               },
    //               inject: true,
    //               on: {
    //                   click({$f}) {
    //                       const rule = $f.activeRule;
    //                       if (rule) {
    //                           rule.__fc__.updateKey();
    //                           rule.value = undefined;
    //                           rule.__fc__.$api.sync(rule);
    //                       }
    //                   },
    //               },
    //               native: true,
    //               children: ['清空值']
    //           }, {
    //               type: 'el-button',
    //               props: {
    //                   type: 'success',
    //                   size: 'mini',
    //                   icon: 'iconfont iconqingchu',
    //               },
    //               inject: true,
    //               on: {
    //                   click({$f}) {
    //                       const rule = $f.activeRule;
    //                       if (rule) {
    //                           rule.__fc__.updateKey(true);
    //                           rule.__fc__.$api.sync(rule);
    //                       }
    //                   },
    //               },
    //               native: true,
    //               children: ['刷新']
    //           },
    //       ]
    //   }
  ];
}
