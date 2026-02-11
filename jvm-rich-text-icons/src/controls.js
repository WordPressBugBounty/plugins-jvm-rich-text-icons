import { __ } from "@wordpress/i18n";
import { Component, Fragment } from "@wordpress/element";
import { compose } from "@wordpress/compose";
import { BlockControls } from "@wordpress/block-editor";
import { toggleFormat, insert, create } from "@wordpress/rich-text";
import { ToolbarGroup, Popover, ToolbarButton } from "@wordpress/components";

import IconPicker from "./icon-picker";

let Icons = jvm_richtext_icon_settings.iconset;
let classPrefix = jvm_richtext_icon_settings.base_class;

class IconMap extends Component {
  constructor() {
    super(...arguments);

    this.toggle = this.toggle.bind(this);

    this.state = {
      isOpen: false,
    };
  }

  toggle() {
    this.setState((state) => ({
      isOpen: !state.isOpen,
    }));
  }

  render() {
    const { isOpen } = this.state;
    const { name, value, onChange } = this.props;

    return (
      <Fragment>
        <BlockControls>
          <ToolbarGroup>
            <ToolbarButton
              icon={"flag"}
              aria-haspopup="true"
              label={__("Insert Icon", "jvm-richtext-icons")}
              onClick={this.toggle}
            ></ToolbarButton>

            {isOpen && (
              <Popover
                className="jvm-richtext-icons-popover"
                position="bottom left"
                key="icon-popover"
                onClick={() => {}}
                expandOnMobile={false}
                headerTitle={__("Insert Icon", "jvm-richtext-icons")}
                onClose={() => {
                  onChange(toggleFormat(value, { type: name }));
                }}
              >
                <IconPicker
                  icons={Icons}
                  classPrefix={classPrefix}
                  onSelect={(iconName) => {
                    let temp = create({
                      html:
                        '<i class="' +
                        classPrefix +
                        " " +
                        iconName +
                        '" aria-hidden="true"> </i>',
                    });
                    onChange(insert(value, temp));
                    this.toggle();
                  }}
                />
              </Popover>
            )}
          </ToolbarGroup>
        </BlockControls>
      </Fragment>
    );
  }
}

export default compose()(IconMap);
