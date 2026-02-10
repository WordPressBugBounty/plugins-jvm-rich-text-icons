import { __ } from "@wordpress/i18n";
import { Component, Fragment } from "@wordpress/element";
import { compose } from "@wordpress/compose";
import { BlockControls } from "@wordpress/block-editor";
import { toggleFormat, insert, create } from "@wordpress/rich-text";
import {
  ToolbarGroup,
  Popover,
  ToolbarButton,
  Button,
  TextControl,
  Tooltip,
} from "@wordpress/components";

let Icons = jvm_richtext_icon_settings.iconset;
let classPrefix = jvm_richtext_icon_settings.base_class;

class IconMap extends Component {
  constructor() {
    super(...arguments);

    this.toggle = this.toggle.bind(this);

    this.state = {
      icons: Icons,
      isOpen: false,
      keyword: "",
    };
  }

  search(keyword) {
    let filtered = [];

    for (let icon of Icons) {
      if (icon.toLowerCase().search(keyword.toLowerCase()) !== -1) {
        filtered.push(icon);
      }
    }

    this.setState({ keyword, icons: filtered });
  }

  toggle() {
    this.setState((state) => ({
      isOpen: !state.isOpen,
    }));

    this.setState({ keyword: "", icons: Icons });
  }

  render() {
    const { isOpen, icons, keyword } = this.state;
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
                <TextControl
                  value={keyword}
                  placeholder={__("Search", "jvm-richtext-icons")}
                  onChange={(newKeyword) => {
                    this.search(newKeyword);
                  }}
                />
                <div className="jvm-richtext-icons-panel">
                  {icons.length > 0 ? (
                    <ul className="jvm-richtext-icons-list">
                      {icons.map((icon) => {
                        return (
                          <li data-key={icon}>
                            <Tooltip text={icon}>
                              <Button
                                isTertiary
                                onClick={() => {
                                  let temp = create({
                                    html:
                                      '<i class="' +
                                      classPrefix +
                                      " " +
                                      icon +
                                      '" aria-hidden="true"> </i>',
                                  });

                                  onChange(insert(value, temp));

                                  this.toggle();
                                }}
                              >
                                <i
                                  className={classPrefix + " " + icon}
                                  aria-hidden="true"
                                ></i>
                              </Button>
                            </Tooltip>
                          </li>
                        );
                      })}
                    </ul>
                  ) : (
                    <p>{__("No characters found.", "block-options")}</p>
                  )}
                </div>
              </Popover>
            )}
          </ToolbarGroup>
        </BlockControls>
      </Fragment>
    );
  }
}

export default compose()(IconMap);
