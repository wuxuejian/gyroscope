(function () {
  // 常量定义
  const SCREEN_MODES = {
    MINI: "mini-screen",
    MEDIUM: "medium-screen",
    FULL: "full-screen"
  };

  const isMobile = /Mobi|Android|iPhone/i.test(navigator.userAgent);

  const STORAGE_KEYS = {
    FLOAT_POSITION: "chat-iframe-float-position"
  };

  const DEFAULT_OPTIONS = {
    zIndex: 1000,
    defaultShow: true,
    permissions: ["clipboard-read", "clipboard-write"].join("; ")
  };

  // 工具函数
  const storage = {
    get: (key) => {
      try {
        return JSON.parse(localStorage.getItem(key));
      } catch {
        return null;
      }
    },
    set: (key, value) => localStorage.setItem(key, JSON.stringify(value))
  };

  const getFloatPosition = () => {
    const position = storage.get(STORAGE_KEYS.FLOAT_POSITION);
    return position?.top ? `--top-size: ${position.top};` : "";
  };

  const saveFloatPosition = (position) => {
    storage.set(STORAGE_KEYS.FLOAT_POSITION, position);
  };

  // 拖拽处理类
  class DragHandler {
    constructor(iframe) {
      this.iframe = iframe;
      this.isDragging = false;
      this.hasMoved = false;
      this.startPos = { x: 0, y: 0, height: 0 };

      this.handleMouseDown = this.handleMouseDown.bind(this);
      this.handleMouseMove = this.handleMouseMove.bind(this);
      this.handleMouseUp = this.handleMouseUp.bind(this);

      this.init();
    }

    init() {
      const { containerEl } = this.iframe;

      if (isMobile) {
        containerEl.addEventListener("touchstart", this.handleMouseDown);
        containerEl.addEventListener("touchend", this.handleMouseUp);
        window.addEventListener("touchmove", this.handleMouseMove);
        window.addEventListener("touchend", this.handleMouseUp);
      } else {
        containerEl.addEventListener("mousedown", this.handleMouseDown);
        containerEl.addEventListener("mouseup", this.handleMouseUp);
        window.addEventListener("mousemove", this.handleMouseMove);
        window.addEventListener("mouseup", this.handleMouseUp);
      }
    }

    destroy() {
      const { containerEl } = this.iframe;

      if (isMobile) {
        containerEl.removeEventListener("touchstart", this.handleMouseDown);
        containerEl.removeEventListener("touchend", this.handleMouseUp);
        window.removeEventListener("touchmove", this.handleMouseMove);
        window.removeEventListener("touchend", this.handleMouseUp);
      } else {
        containerEl.removeEventListener("mousedown", this.handleMouseDown);
        containerEl.removeEventListener("mouseup", this.handleMouseUp);
        window.removeEventListener("mousemove", this.handleMouseMove);
        window.removeEventListener("mouseup", this.handleMouseUp);
      }

      this.iframe = null;
    }

    canDrag() {
      return this.iframe.screenState === SCREEN_MODES.MINI;
    }

    getStartPos(e) {
      if (isMobile) {
        const rect = this.iframe.containerEl.getBoundingClientRect();
        return {
          x: e.touches[0].clientX - rect.left,
          y: e.touches[0].clientY - rect.top,
          height: rect.height
        };
      } else {
        return {
          x: e.offsetX,
          y: e.offsetY,
          height: this.iframe.containerEl.offsetHeight
        };
      }
    }

    handleMouseDown(e) {
      if (!this.canDrag()) return;
      if (isMobile) {
        // 移动端在悬浮球移动时禁止页面进行滚动，防止和悬浮球移动发生冲突
        document.body.classList.add("not-allow-scroll");
      }
      this.iframe.containerEl.classList.add("moving");
      this.isDragging = true;
      this.startPos = this.getStartPos(e);
    }

    getMoveDelta(e) {
      if (isMobile) {
        const { clientY } = e.touches[0];
        return {
          clientY
        };
      } else {
        const { clientY } = e;
        return {
          clientY
        };
      }
    }

    handleMouseMove(e) {
      if (!this.canDrag() || !this.isDragging) return;

      this.hasMoved = true;
      const { clientY } = this.getMoveDelta(e);
      const { y, height } = this.startPos;

      let deltaY = Math.max(0, Math.min(clientY - y, window.innerHeight - height));
      this.iframe.containerEl.style.setProperty("--top-size", `${deltaY}px`);
    }

    handleMouseUp() {
      this.isDragging = false;
      if (!this.canDrag()) return;
      if (isMobile) {
        document.body.classList.remove("not-allow-scroll");
      }

      if (this.hasMoved) {
        this.iframe.containerEl.classList.remove("moving");
        this.iframe.allowShowIframe = false;
        requestAnimationFrame(() => {
          this.iframe.allowShowIframe = true;
        });

        saveFloatPosition({
          top: this.iframe.containerEl.style.getPropertyValue("--top-size")
        });

        this.hasMoved = false;
      }
    }
  }

  // 主类
  class ChatIframe {
    static isCssInjected = false;

    positions = {
      [SCREEN_MODES.MINI]: { top: "initial", bottom: "30vh" },
      [SCREEN_MODES.MEDIUM]: { top: "initial", bottom: "20px" },
      [SCREEN_MODES.FULL]: { top: "0", bottom: "0" }
    };

    /**
     * url: 必填，iframe 的 url
     * floatIcon: 必填，悬浮球图标
     * zIndex: 可选，z-index 值，默认 1000
     * defaultShow: 可选，是否默认显示，默认 true
     * query: 可选，query 参数，传递 token 和 scene 信息
     * permissions: 可选，iframe 的权限，默认 ["clipboard-read", "clipboard-write"]
     */
    constructor(options) {
      this.options = {
        ...DEFAULT_OPTIONS,
        ...options,
        url: this.buildUrl(options.url, {
          ...options.query,
          prefix: this.prefix = Math.random().toString(36).slice(2, 15)
        })
      };

      this.screenState = SCREEN_MODES.MINI;
      this.allowShowIframe = true;
      this.scene = options.query?.scene || "";

      this.handleIframeMessage = this.handleIframeMessage.bind(this);
      this.handleShowIframe = this.handleShowIframe.bind(this);

      this.init();
    }

    buildUrl(url, query) {
      const urlObj = new URL(url);
      Object.entries(query).forEach(([key, value]) => {
        if (value !== undefined) {
          urlObj.searchParams.set(key, value);
        }
      });
      return urlObj.toString();
    }

    injectCss() {
      if (ChatIframe.isCssInjected) return;

      const scriptSrc = new URL(this.options.url).origin;
      const cssHref = new URL("/chat/entry/style/iframe.css", scriptSrc).href;

      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = cssHref;
      document.head.appendChild(link);

      ChatIframe.isCssInjected = true;
    }

    init() {
      const { url, floatIcon } = this.options;
      if (!url || !floatIcon) {
        console.error("url and floatIcon are required");
        return;
      }

      this.injectCss();

      const init = () => {
        this.createIframe();
        this.dragHandler = new DragHandler(this);
      };

      if (document.readyState !== "loading") {
        init();
      } else {
        window.addEventListener("DOMContentLoaded", init);
      }
    }

    createIframe() {
      const { url, zIndex, floatIcon, defaultShow, permissions } = this.options;
      const containerId = `chat-${this.prefix}`;

      const template = document.createElement("template");
      template.innerHTML = `
      <div id="${containerId}" 
           class="chat-iframe-container ${defaultShow ? "" : "hide"}" 
           style="z-index: ${zIndex}; ${getFloatPosition()}"
           data-mode="${this.screenState}"
           data-scene="${this.scene}">
        <iframe allow="${permissions}" src="${url}" class="chat-iframe"></iframe>
        <img draggable="false" src="${floatIcon}" class="chat-iframe-float-icon" />
      </div>
    `;

      document.body.appendChild(template.content);

      this.containerEl = document.querySelector(`#${containerId}`);
      this.iframeEl = this.containerEl.querySelector(".chat-iframe");
      this.floatIconEl = this.containerEl.querySelector(".chat-iframe-float-icon");

      window.addEventListener("message", this.handleIframeMessage);
      this.floatIconEl.addEventListener("click", this.handleShowIframe);
    }

    destroy() {
      window.removeEventListener("message", this.handleIframeMessage);
      this.floatIconEl?.removeEventListener("click", this.handleShowIframe);
      this.dragHandler?.destroy();
      this.containerEl?.remove();
    }

    refreshAppList() {
      this.sendMessageToIframe("refresh-app-list");
    }

    openApp(appId) {
      if (!appId) {
        console.error("appId is required");
        return;
      }

      if (this.containerEl.classList.contains("hide")) {
        this.show();
        requestAnimationFrame(() => {
          this.sendMessageToIframe("open-app", { appId });
        });
      } else {
        this.sendMessageToIframe("open-app", { appId });
      }
    }

    show() {
      this.containerEl.classList.remove("hide");
    }

    hide() {
      this.containerEl.classList.add("hide");
    }

    handleShowIframe() {
      if (!this.allowShowIframe) return;
      this.sendMessageToIframe("show-iframe");
    }

    handleSetMinimize() {
      this.sendMessageToIframe("set-minimize");
    }

    handleSetIframeScreenState({ state }) {
      if (!Object.values(SCREEN_MODES).includes(state)) return;

      // 广播消息，用来通知移动端 Web 应用更新状态
      window.postMessage({
        source: "entry",
        action: "set-screen-state",
        data: {
          state
        }
      });
      this.saveFloatPosition();

      requestAnimationFrame(() => {
        this.containerEl.dataset.mode = state;
        this.screenState = state;

        const position = this.getPositionByMode(state);
        this.containerEl.style.setProperty("--top-size", position.top);
        this.containerEl.style.setProperty("--bottom-size", position.bottom);
      });
    }

    getPositionByMode(mode) {
      return this.positions[mode];
    }

    saveFloatPosition() {
      const style = window.getComputedStyle(this.containerEl);
      this.containerEl.style.setProperty("--top-size", style.top);
      this.containerEl.style.setProperty("--bottom-size", style.bottom);

      this.positions[this.screenState] = {
        top: style.top,
        bottom: style.bottom,
      };
    }

    sendMessageToIframe(action, data) {
      this.iframeEl.contentWindow.postMessage({
        source: "ai-chat-parent",
        action,
        data
      }, "*");
    }

    handleIframeMessage(event) {
      const { data } = event;
      if (data.channel !== this.prefix || data.source !== "ai-chat") return;

      switch (data.action) {
        case "set-iframe-screen-state":
          this.handleSetIframeScreenState(data.data);
          break;
      }
    }
  }

  window.ChatIframe = ChatIframe;
})();

/**
 * 使用方法：
 *
 * 1. 引入 /entry/index.js
 * 2. new ChatIframe 创建实例
 * 3. chatIframe 参数
 *    url: 必填，iframe 的 url
 *    floatIcon: 必填，悬浮球图标
 *    zIndex: 可选，z-index 值，默认 1000
 *    defaultShow: 可选，是否默认显示，默认 true
 *    query: 可选，query 参数，传递 token 和 scene 信息
 *    permissions: 可选，iframe 的权限，默认 ["clipboard-read", "clipboard-write"]
 *
 * 实例方法：
 *
 * show: 显示悬浮球
 * hide: 隐藏悬浮球
 * refreshAppList: 刷新应用列表
 * openApp: 打开应用 openApp(appId)
 * destroy: 销毁实例
 *
 */
