(function () {
  const instances = new Map();
  let globalVerifyKeyHandler = null;

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.getAttribute("content") : "";
  }

  function buildCustomVerifyKey(verifyKeyUrl) {
    return async function customVerifyKey(apiKey) {
      try {
        const response = await fetch(verifyKeyUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
            "X-Requested-With": "XMLHttpRequest",
          },
          body: JSON.stringify({ apiKey }),
        });

        if (!response.ok) {
          return {
            success: false,
            message: "Verification request failed",
          };
        }

        const contentType = response.headers.get("content-type") || "";

        if (!contentType.includes("application/json")) {
          return {
            success: false,
            message: "Verification response was not JSON",
          };
        }

        try {
          return await response.json();
        } catch (error) {
          return {
            success: false,
            message: "Invalid JSON in verification response",
          };
        }
      } catch (error) {
        return {
          success: false,
          message: "Verification request failed",
        };
      }
    };
  }

  function resolveCustomVerifyKey(config) {
    if (typeof config.customVerifyKey === "function") {
      return config.customVerifyKey;
    }

    if (typeof globalVerifyKeyHandler === "function") {
      return globalVerifyKeyHandler;
    }

    if (config.verifyKeyUrl) {
      return buildCustomVerifyKey(config.verifyKeyUrl);
    }

    return undefined;
  }

  function dispatchEditorEvent(root, name, detail) {
    root.dispatchEvent(
      new CustomEvent("eddyter:" + name, {
        bubbles: true,
        detail: detail,
      })
    );
  }

  function parseConfigElement(root) {
    const configId = root.dataset.eddyterConfigId;

    if (!configId) {
      console.error(
        "[Eddyter Laravel] Missing data-eddyter-config-id for editor:",
        root.id
      );

      return null;
    }

    const configElement = document.getElementById(configId);

    if (!configElement) {
      console.error(
        "[Eddyter Laravel] JSON configuration element not found for editor:",
        root.id,
        "expected element id:",
        configId
      );

      return null;
    }

    try {
      return JSON.parse(configElement.textContent || "{}");
    } catch (error) {
      console.error(
        "[Eddyter Laravel] Invalid JSON configuration for editor:",
        root.id,
        error
      );

      return null;
    }
  }

  function buildInitOptions(root, config, textarea) {
    const {
      verifyKeyUrl: _verifyKeyUrl,
      customVerifyKey: _customVerifyKey,
      ...sdkConfig
    } = config;

    const customVerifyKey = resolveCustomVerifyKey(config);

    const options = {
      ...sdkConfig,
      container: root,

      onChange: function (html) {
        textarea.value = html;

        textarea.dispatchEvent(new Event("input", { bubbles: true }));
        textarea.dispatchEvent(new Event("change", { bubbles: true }));

        dispatchEditorEvent(root, "change", {
          html: html,
          instance: instances.get(root.id),
        });
      },

      onReady: function () {
        dispatchEditorEvent(root, "ready", {
          instance: instances.get(root.id),
        });
      },

      onFocus: function () {
        dispatchEditorEvent(root, "focus", {
          instance: instances.get(root.id),
        });
      },

      onBlur: function () {
        dispatchEditorEvent(root, "blur", {
          instance: instances.get(root.id),
        });
      },

      onHeightChange: function (height) {
        dispatchEditorEvent(root, "height-change", {
          height: height,
          instance: instances.get(root.id),
        });
      },

      onAuthSuccess: function () {
        dispatchEditorEvent(root, "auth-success", {
          instance: instances.get(root.id),
        });
      },

      onAuthError: function (error) {
        dispatchEditorEvent(root, "auth-error", {
          error: error,
          instance: instances.get(root.id),
        });
      },

      onPreviewClick: function () {
        dispatchEditorEvent(root, "preview-click", {
          instance: instances.get(root.id),
        });
      },
    };

    if (customVerifyKey) {
      options.customVerifyKey = customVerifyKey;
    }

    return options;
  }

  function initEddyterEditor(root) {
    if (root.dataset.eddyterInitialized === "true") {
      return instances.get(root.id) || null;
    }

    const textareaId = root.dataset.eddyterTextareaId;
    const textarea = textareaId ? document.getElementById(textareaId) : null;

    if (!textarea) {
      console.error(
        "[Eddyter Laravel] Linked textarea not found for editor:",
        root.id,
        textareaId ? "expected textarea id: " + textareaId : "missing data-eddyter-textarea-id"
      );

      return null;
    }

    if (!window.Eddyter || typeof window.Eddyter.init !== "function") {
      console.error(
        "[Eddyter Laravel] Eddyter SDK is not loaded. Include @eddyterScripts in your layout before initializing editors. Editor:",
        root.id
      );

      return null;
    }

    const config = parseConfigElement(root);

    if (!config) {
      return null;
    }

    if (!config.initialContent && textarea.value) {
      config.initialContent = textarea.value;
    }

    let instance;

    try {
      instance = window.Eddyter.init(buildInitOptions(root, config, textarea));
    } catch (error) {
      console.error(
        "[Eddyter Laravel] SDK initialization failed for editor:",
        root.id,
        error
      );

      return null;
    }

    root.dataset.eddyterInitialized = "true";
    root._eddyterInstance = instance;
    instances.set(root.id, instance);

    return instance;
  }

  function resolveRoot(target) {
    if (!target) {
      return null;
    }

    if (typeof target === "string") {
      return document.getElementById(target);
    }

    return target;
  }

  function initAll() {
    document.querySelectorAll("[data-eddyter-root]").forEach(function (root) {
      try {
        initEddyterEditor(root);
      } catch (error) {
        console.error(
          "[Eddyter Laravel] Failed to initialize editor:",
          root.id,
          error
        );
      }
    });
  }

  window.EddyterLaravel = {
    initAll: initAll,

    init: function (target) {
      const root = resolveRoot(target);

      if (!root) {
        return null;
      }

      try {
        return initEddyterEditor(root);
      } catch (error) {
        console.error(
          "[Eddyter Laravel] Failed to initialize editor:",
          root.id,
          error
        );

        return null;
      }
    },

    getInstance: function (target) {
      const root = resolveRoot(target);

      if (!root) {
        return null;
      }

      return instances.get(root.id) || root._eddyterInstance || null;
    },

    update: function (target, options) {
      const instance = this.getInstance(target);

      if (instance && typeof instance.update === "function") {
        instance.update(options);
      }

      return instance;
    },

    destroy: function (target) {
      const root = resolveRoot(target);

      if (!root) {
        return;
      }

      const instance = this.getInstance(root);

      if (instance && typeof instance.destroy === "function") {
        instance.destroy();
      }

      instances.delete(root.id);
      delete root.dataset.eddyterInitialized;
      delete root._eddyterInstance;
    },

    registerVerifyKeyHandler: function (handler) {
      globalVerifyKeyHandler = handler;
    },
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAll);
  } else {
    initAll();
  }
})();
