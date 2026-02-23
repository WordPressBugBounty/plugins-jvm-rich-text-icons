import { __ } from "@wordpress/i18n";
import { registerFormatType } from "@wordpress/rich-text";
import { Fragment, useState } from "@wordpress/element";
import { registerBlockType } from "@wordpress/blocks";
import {
  InspectorControls,
  BlockControls,
  useBlockProps,
  __experimentalLinkControl as LinkControl,
} from "@wordpress/block-editor";
import {
  PanelBody,
  RangeControl,
  ToolbarButton,
  ToolbarGroup,
  Popover,
} from "@wordpress/components";

const linkIcon = (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    width="24"
    height="24"
    aria-hidden="true"
    focusable="false"
  >
    <path d="M15.6 7.2H14v1.5h1.6c2 0 3.7 1.7 3.7 3.7s-1.7 3.7-3.7 3.7H14v1.5h1.6c2.8 0 5.2-2.3 5.2-5.2 0-2.9-2.3-5.2-5.2-5.2zm-7.2 0H10v1.5H8.4c-2 0-3.7 1.7-3.7 3.7s1.7 3.7 3.7 3.7H10v1.5H8.4C5.6 17.6 3.2 15.3 3.2 12.4c0-2.9 2.3-5.2 5.2-5.2zM8 11.5h8V13H8z" />
  </svg>
);

const linkOffIcon = (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    width="24"
    height="24"
    aria-hidden="true"
    focusable="false"
  >
    <path d="M17.031 4.703 15.576 4l-1.56 3H14v.03l-2.324 4.47H9.5V13h1.396l-1.502 2.889h-.95a3.694 3.694 0 0 1 0-7.389H10V7H8.444a5.194 5.194 0 1 0 0 10.389h.17L7.5 19.53l1.416.719L15.049 8.5h.507a3.694 3.694 0 0 1 0 7.39H14v1.5h1.556a5.194 5.194 0 0 0 .273-10.383l1.202-2.304Z" />
  </svg>
);
import domReady from "@wordpress/dom-ready";

import IconMap from "./controls";
import IconPicker from "./icon-picker";
import metadata from "./block.json";

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

registerBlockType(metadata.name, {
  ...metadata,

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
    const [isLinkPickerOpen, setIsLinkPickerOpen] = useState(false);

    const { url, linkTarget, rel } = props.attributes;
    const hasLink = !!url;
    const opensInNewTab = linkTarget === "_blank";

    function onToggleOpenInNewTab(value) {
      const newTarget = value ? "_blank" : undefined;
      const newRel = value ? "noreferrer noopener" : undefined;
      props.setAttributes({ linkTarget: newTarget, rel: newRel });
    }

    const iconElement = (
      <i className={cssClass} aria-hidden="true" style={cssStyle}>
        {" "}
      </i>
    );

    return (
      <>
        <BlockControls>
          <ToolbarGroup>
            <ToolbarButton
              icon={hasLink ? linkOffIcon : linkIcon}
              label={
                hasLink
                  ? __("Remove link", "jvm-rich-text-icons")
                  : __("Add link", "jvm-rich-text-icons")
              }
              onClick={() => {
                if (hasLink) {
                  props.setAttributes({
                    url: undefined,
                    linkTarget: undefined,
                    rel: undefined,
                  });
                  setIsLinkPickerOpen(false);
                } else {
                  setIsLinkPickerOpen(true);
                }
              }}
              isActive={hasLink}
            />
          </ToolbarGroup>
        </BlockControls>
        {isLinkPickerOpen && (
          <Popover
            placement="bottom"
            onClose={() => setIsLinkPickerOpen(false)}
            focusOnMount="firstElement"
          >
            <LinkControl
              value={{ url, opensInNewTab }}
              onChange={({ url: newUrl, opensInNewTab: newTab }) => {
                props.setAttributes({
                  url: newUrl,
                  linkTarget: newTab ? "_blank" : undefined,
                  rel: newTab ? "noreferrer noopener" : undefined,
                });
              }}
              onRemove={() => {
                props.setAttributes({
                  url: undefined,
                  linkTarget: undefined,
                  rel: undefined,
                });
                setIsLinkPickerOpen(false);
              }}
            />
          </Popover>
        )}
        <InspectorControls>
          <PanelBody label={__("Icon")}>
            <IconPicker
              icons={icons}
              classPrefix={classPrefix}
              selectedIcon={selectectValue}
              onSelect={(iconName) => props.setAttributes({ icon: iconName })}
            />
            <RangeControl
              __next40pxDefaultSize
              __nextHasNoMarginBottom
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
          {hasLink ? (
            <a
              href={url}
              target={linkTarget}
              rel={rel}
              onClick={(e) => e.preventDefault()}
            >
              {iconElement}
            </a>
          ) : (
            iconElement
          )}
        </div>
      </>
    );
  },

  save: (props) => {
    const blockProps = useBlockProps.save();
    let classPrefix = jvm_richtext_icon_settings.base_class;
    let cssClass = classPrefix + " " + props.attributes.icon;
    let cssStyle = { fontSize: props.attributes.fontSize + "px" };
    const { url, linkTarget, rel } = props.attributes;

    const iconElement = (
      <i className={cssClass} aria-hidden="true" style={cssStyle}>
        {" "}
      </i>
    );

    return (
      <div {...blockProps}>
        {url ? (
          <a href={url} target={linkTarget} rel={rel}>
            {iconElement}
          </a>
        ) : (
          iconElement
        )}
      </div>
    );
  },
});
