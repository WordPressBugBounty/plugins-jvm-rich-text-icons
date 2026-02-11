import { __ } from "@wordpress/i18n";
import { registerFormatType } from "@wordpress/rich-text";
import { Fragment } from "@wordpress/element";
import { registerBlockType } from "@wordpress/blocks";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { PanelBody, RangeControl } from "@wordpress/components";
import domReady from "@wordpress/dom-ready";

import IconMap from "./controls";
import IconPicker from "./icon-picker";

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

  supports: {
    color: {
      text: true,
      background: true,
    },
    spacing: {
      margin: true,
      padding: true,
    },
  },

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
    let selectectValue = "";
    let currentFontSize = 32;
    let classPrefix = jvm_richtext_icon_settings.base_class;

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

    // Update the properties
    props.setAttributes({ icon: selectectValue, fontSize: currentFontSize });

    let cssClass = classPrefix + " " + props.attributes.icon;
    let cssStyle = { fontSize: props.attributes.fontSize + "px" };

    const blockProps = useBlockProps();

    return (
      <>
        <InspectorControls>
          <PanelBody label={__("Icon")}>
            <IconPicker
              icons={icons}
              classPrefix={classPrefix}
              selectedIcon={selectectValue}
              onSelect={(iconName) => props.setAttributes({ icon: iconName })}
            />
            <RangeControl
              label={__("Font Size (px)")}
              value={currentFontSize}
              min={10}
              max={200}
              onChange={(size) => {
                if (size) {
                  props.setAttributes({ fontSize: size });
                }
              }}
            />
          </PanelBody>
        </InspectorControls>
        <div {...blockProps}>
          <i className={cssClass} aria-hidden="true" style={cssStyle}>
            {" "}
          </i>
        </div>
      </>
    );
  },

  save: (props) => {
    const blockProps = useBlockProps.save();
    let classPrefix = jvm_richtext_icon_settings.base_class;
    let cssClass = classPrefix + " " + props.attributes.icon;
    let cssStyle = { fontSize: props.attributes.fontSize + "px" };
    return (
      <div {...blockProps}>
        <i className={cssClass} aria-hidden="true" style={cssStyle}>
          {" "}
        </i>
      </div>
    );
  },
});
