var wpcsw_watermark = null;
(function ($) {
  wpcsw_watermark = {
    node: {},
    defaults: {
      watermark_text: "",
      watermark_type: "TopLeft",
      watermark_color: '#FFF',
      watermark_shade_color: '#000000',
      watermark_font_size: '14px',
      watermark_font_size_fullscreen : '24px',
      watermark_opacity: 1
    },
    watermark_types: {
      Top: {},
      TopLeft: {},
      TopRight: {},
      Center: {},
      Bottom: {},
      BottomLeft: {},
      BottomRight: {},
      Random: {},
      Rotating: {},
      Blinking: {},
    },
    head_style_added: false,
    add: function (node, options) {
      let that = this;

      that.config = $.extend({}, that.defaults, options || {});

      if (!$(node).length) {
        return false;
      }

      $(node).each(function () {
        that.processNode(this);
      });
    },

    processNode: function (node) {
      let that = this;

      let uid = that.uid();

      $(node).wrap('<div></div>');

      let watermark_text = that.config.watermark_text
        ? that.config.watermark_text
        : that.getText();

      let html_watermark = `<div class="wpcsw-watermark watermark-${that.config.watermark_type}" data-uid="${uid}">${watermark_text}</div>`;

      $(node).append(html_watermark);

      const watermark = $(node).find(".wpcsw-watermark");

      that.node[uid] = {
        parent : node,
        watermark : watermark,
        config: that.config
      };

      that.addWatermarkStyling(watermark, uid);
    },

    addWatermarkStyling: function (watermark, uid) {
      let that = this;

      const watermark_type = that.node[uid].config.watermark_type;
      const parent = that.node[uid].parent;
      const config = that.node[uid].config;

      if(config.watermark_color) {
        watermark.css('color', config.watermark_color);
      }

      if(config.watermark_shade_color) {
        watermark.css('text-shadow', '2px 2px 8px ' + config.watermark_shade_color);
      }

      if(config.watermark_font_size) {
        watermark.css('font-size', config.watermark_font_size);
      }

      if(config.watermark_opacity) {
        watermark.css('opacity', config.watermark_opacity);
      }

      let css =
        `<style type="text/css">
          .wpcsw-wrapper.fullscreen .wpcsw-watermark {
            font-size:${config.watermark_font_size_fullscreen} !important;
          }
        `;

      if (watermark_type == "Random") {
        setInterval(function () {
          that.waterMarkRandomPosition(uid);
        }, 3000);
      } else if (watermark_type == "Blinking") {
        watermark.css("visibility", "hidden");

        parent.one("video.played", function () {
          that.waterMarkStartBlinking(uid);
        });
      }

      css += ' </style>';

      if( ! that.head_style_added) {
        $("head").append(css);
        that.head_style_added = true;
      }
    },

    waterMarkRandomPosition: function (uid) {
      let that = this;
      const rand = Math.floor(Math.random() * 5) + 1;

      let node = that.node[uid].watermark;

      if (rand == 1) {
        node.css("left", "50px");
        node.css("top", "50px");
        node.css("right", "auto");
        node.css("bottom", "auto");
      } else if (rand == 2) {
        node.css("left", "auto");
        node.css("top", "50px");
        node.css("right", "50px");
        node.css("bottom", "auto");
      } else if (rand == 3) {
        node.css("left", "auto");
        node.css("top", "auto");
        node.css("right", "50px");
        node.css("bottom", "50px");
      } else if (rand == 4) {
        node.css("left", "50px");
        node.css("top", "auto");
        node.css("right", "auto");
        node.css("bottom", "50px");
      } else if (rand == 5) {
        node.css("left", "50%");
        node.css("top", "50%");
        node.css("right", "auto");
        node.css("bottom", "auto");
      }
    },

    waterMarkStartBlinking: function (uid) {
      let that = this;

      let watermark = that.node[uid].watermark;

      setInterval(function() {
        if (watermark.hasClass("watermark-TopLeft")) {
          watermark.removeClass("watermark-TopLeft");
          watermark.addClass("watermark-TopRight");
        } else if (watermark.hasClass("watermark-TopRight")) {
          watermark.removeClass("watermark-TopRight");
          watermark.addClass("watermark-BottomRight");
        } else if (watermark.hasClass("watermark-BottomRight")) {
          watermark.removeClass("watermark-BottomRight");
          watermark.addClass("watermark-BottomLeft");
        } else if (watermark.hasClass("watermark-BottomLeft")) {
          watermark.removeClass("watermark-BottomLeft");
          watermark.addClass("watermark-TopLeft");
        } else {
          watermark.addClass("watermark-TopLeft");
        }
        showElement(watermark, 3000);
      }, 5000);

      function showElement(elm, duration) {
        elm.css('visibility', 'visible');
        setTimeout(function () {
          elm.css('visibility', 'hidden');
        }, duration);
      }

      function blinkElement(elm, interval, duration) {
        elm.css('visibility',
          elm.css('visibility') === "hidden" ? "visible" : "hidden");

        if (duration > 0) {
          setTimeout(blinkElement, interval, elm, interval, duration - interval);
        } else {
          elm.css('visibility', "hidden");
        }
      }
    },

    uid: function () {
      return Date.now().toString(36) + Math.random().toString(36).substring(2);
    },

    getText: function () {
      return "watermark";
    },
  };
})(jQuery);