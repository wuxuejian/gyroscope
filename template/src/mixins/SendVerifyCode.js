export default {
  data() {
    return {
      disabled: false,
      text: this.$t('login.code'),
    };
  },
  watch: {
    lang() {
      this.setOptions();
    },
  },
  computed: {
    lang: function () {
      return this.$store.getters.lang;
    },
  },
  methods: {
    setOptions() {
      this.text = this.$t('login.code');
    },
    sendCode() {
      if (this.disabled) return;
      this.disabled = true;
      let n = 60;
   
      this.text = '剩余 ' + n + 's';
      const run = setInterval(() => {
        n = n - 1;
        if (n < 0) {
          clearInterval(run);
        }
        this.text = '剩余 ' + n + 's';
        if (this.text < '剩余 ' + 0 + 's') {
          this.disabled = false;
          this.text = '重新获取';
        }
      }, 1000);
    },
  },
};
