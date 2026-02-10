import { __ } from "@wordpress/i18n";
import { registerFormatType } from "@wordpress/rich-text";
import { Fragment } from "@wordpress/element";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  ComboboxControl,
  RangeControl,
} from "@wordpress/components";
import domReady from "@wordpress/dom-ready";

import IconMap from "./controls";

/**
 * Block constants
 */
const name = "jvm/insert-icons";

export const icon = {
  name,
  title: __("Insert Icon", "jvm-richtext-icons"),
  tagName: "i",
  className: null,
  edit({ isOpen, value, onChange, activeAttributes }) {
    return (
      <Fragment>
        <IconMap
          name={name}
          isOpen={isOpen}
          value={value}
          onChange={onChange}
          activeAttributes={activeAttributes}
        />
      </Fragment>
    );
  },
};

// Register the icon button
domReady(function () {
  [icon].forEach(({ name, ...settings }) => {
    if (name) {
      registerFormatType(name, settings);
    }
  });
});

registerBlockType("jvm/single-icon", {
  title: __("Single icon"),
  icon: "flag",
  category: "common",
  keywords: [__("Icon")],

  attributes: {
    icon: {
      type: "string",
    },

    fontSize: {
      type: "number",
    },
  },

  edit: (props) => {
    let icons = jvm_richtext_icon_settings.iconset;
    let options = [];
    let selectectValue = "";
    let currentFontSize = 32;
    let classPrefix = jvm_richtext_icon_settings.base_class;

    for (let icon of icons) {
      options.push({
        value: icon,
        label: icon,
      });
    }

    // Get the current or first icon
    if (props.attributes.icon !== undefined) {
      selectectValue = props.attributes.icon;
    } else {
      if (icons[0] !== undefined) {
        selectectValue = icons[0];
      }
    }

    // Get current font size
    if (props.attributes.fontSize !== undefined) {
      currentFontSize = props.attributes.fontSize;
    }

    // Update the proerties
    props.setAttributes({ icon: selectectValue, fontSize: currentFontSize });

    let cssClass = classPrefix + " " + props.attributes.icon;
    let cssStyle = { fontSize: props.attributes.fontSize + "px" };

    return [
      <InspectorControls>
        <PanelBody label={__("Icon")}>
          <ComboboxControl
            label={__("Icon")}
            value={selectectValue}
            onChange={(i) => {
              if (i) {
                props.setAttributes({ icon: i });
              }
            }}
            options={options}
            __experimentalRenderItem={(opt) => {
              let cssClass = classPrefix + " " + opt.item.value;
              return (
                <span>
                  <i class={cssClass} aria-hidden="true">
                    {" "}
                  </i>{" "}
                  {opt.item.value}
                </span>
              );
            }}
            isMulti="false"
          />
          <RangeControl
            label={__("Font Size (px)")}
            value={currentFontSize}
            min="10"
            max="200"
            onChange={(i) => {
              if (i) {
                props.setAttributes({ fontSize: i });
              }
            }}
          />
        </PanelBody>
      </InspectorControls>,

      <div className={props.className}>
        <i class={cssClass} aria-hidden="true" style={cssStyle}>
          {" "}
        </i>
      </div>,
    ];
  },

  save: (props) => {
    let classPrefix = jvm_richtext_icon_settings.base_class;
    let cssClass = classPrefix + " " + props.attributes.icon;
    let cssStyle = { fontSize: props.attributes.fontSize + "px" };
    return (
      <div className={props.className}>
        <i class={cssClass} aria-hidden="true" style={cssStyle}>
          {" "}
        </i>
      </div>
    );
  },
});
