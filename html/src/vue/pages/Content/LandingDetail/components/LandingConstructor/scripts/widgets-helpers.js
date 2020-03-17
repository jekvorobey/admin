import {escape_html} from "./helpers";

/**
 * Fill widget prop recursively.
 *
 * @param prop
 * @param value
 * @param propCode
 */
export const fill_prop_recursively = (prop, value, propCode) => {
    if (typeof value === "undefined") {
        if (prop && prop.multiple) {
            prop.array = [];
        }
        return;
    }

    if (prop.multiple) {
        prop.array = [];

        let childComponentsValues = value;

        if (Array.isArray(childComponentsValues)) {
            childComponentsValues = childComponentsValues.filter((childItem) => {
                return propCode === 'children' ? !!childItem.component : true;
            });

            childComponentsValues.forEach((childComplexesValues, i) => {
                if (!prop.array[i]) {
                    prop.array[i] = prop.array_item ? JSON.parse(JSON.stringify(prop.array_item)) : {};
                }
            });
        }

        prop.array.forEach((childComplex, i) => {
            for (let k in childComplex) {
                if (childComplex.hasOwnProperty(k) && childComponentsValues[i]) {
                    let childComponentValue;
                    if (prop.type === 'complex') {
                        childComponentValue = childComponentsValues[i][k];
                        if (!childComponentValue && childComponentsValues[i].props) {
                            childComponentValue = childComponentsValues[i].props;
                        }
                    } else {
                        childComponentValue = childComponentsValues[i];
                    }

                    fill_prop_recursively(childComplex[k], childComponentValue, k);
                }
            }
        });
    } else if (prop.type === "complex") {
        if (!prop.complex) {
            prop.complex = {};
        }

        for (let k in prop.complex) {
            if (prop.complex.hasOwnProperty(k)) {
                fill_prop_recursively(prop.complex[k], value[k], k);
            }
        }
    } else if (prop.type === "widget") {
        if (!prop.widget) {
            prop.widget = {};
        }

        if (!prop.widget.props) {
            prop.widget.props = {};
        }

        for (let k in prop.widget.props) {
            if (prop.widget.props.hasOwnProperty(k)) {
                fill_prop_recursively(prop.widget.props[k], value[k], k);
            }
        }
    } else {
        // primitive prop
        if (prop.is_price) {
            prop.value = value ? value.replace('.', ',') : value;
        } else if (prop.is_margin) {
            prop.value = value ? value.replace(/[^0-9-]/g, '') : value;
        } else {
            prop.value = value;
        }
    }
};

/**
 * Fill widget prop by default value recursively.
 *
 * @param prop
 */
export const fill_prop_recursively_with_default = (prop) => {
    if (!prop) {
        return;
    }

    if (prop.multiple) {
        if (!prop.array) {
            prop.array = [];
            if (prop.required || prop.min_count >= 1) {
                const min_count = prop.min_count >= 1 ? prop.min_count : 1;
                for (let mc = 0; mc < min_count; mc++) {
                    prop.array.push(prop.array_item ? JSON.parse(JSON.stringify(prop.array_item)) : {});
                }
            }
        }

        prop.array.forEach((childComplex) => {
            for (let k in childComplex) {
                if (childComplex.hasOwnProperty(k)) {
                    fill_prop_recursively_with_default(childComplex[k]);
                }
            }
        });
    } else if (prop.type === "complex") {
        if (!prop.complex) {
            prop.complex = {};
        }

        for (let k in prop.complex) {
            if (prop.complex.hasOwnProperty(k)) {
                fill_prop_recursively_with_default(prop.complex[k]);
            }
        }
    } else if (prop.type === "widget") {
        if (!prop.widget) {
            prop.widget = {};
        }

        if (!prop.widget.props) {
            prop.widget.props = {};
        }

        for (let k in prop.widget.props) {
            if (prop.widget.props.hasOwnProperty(k)) {
                fill_prop_recursively_with_default(prop.widget.props[k]);
            }
        }
    } else {
        prop.value = prop.default;
    }
};

/**
 * Fill widget prop values with null recursively.
 *
 * @param prop
 * @param propCode
 */
export const fill_prop_recursively_with_null = (prop, propCode) => {
    if (!prop) {
        return;
    }

    if (prop.multiple) {
        prop.array = [];
    } else if (prop.type === "complex") {
        if (prop.complex) {
            for (let k in prop.complex) {
                if (prop.complex.hasOwnProperty(k)) {
                    fill_prop_recursively_with_null(prop.complex[k], k);
                }
            }
        }
    } else if (prop.type === "widget") {
        if (prop.widget && prop.widget.props) {
            for (let k in prop.widget.props) {
                if (prop.widget.props.hasOwnProperty(k)) {
                    fill_prop_recursively_with_null(prop.widget.props[k], k);
                }
            }
        }
    } else {
        prop.value = null;
    }
};

/**
 * Fill widget props isInShownList field.
 *
 * @param props
 */
export const fill_props_is_in_shown_list = (props) => {
    for (let k in props) {
        if (props.hasOwnProperty(k)) {
            let isInShownList = true;

            for (let j in props[k].conditions) {
                if (props[k].conditions.hasOwnProperty(j) && props[k].conditions[j].length) {
                    if (!props[j] || props[k].conditions[j].indexOf(props[j].value) < 0) {
                        isInShownList = false;
                        break;
                    }
                }
            }

            props[k].isInShownList = isInShownList;
        }
    }
};

/**
 * Fill widget props isInShownList field recursively.
 *
 * @param props
 */
export const fill_props_is_in_shown_list_recursively = (props) => {
    fill_props_is_in_shown_list(props);

    for (let k in props) {
        if (props.hasOwnProperty(k)) {
            if (props[k].multiple) {
                if (props[k].array) {
                    props[k].array.forEach((childComplex) => {
                        fill_props_is_in_shown_list_recursively(childComplex);
                    });
                }
            } else if (props[k].type === "complex") {
                if (props[k].complex) {
                    fill_props_is_in_shown_list_recursively(props[k].complex);
                }
            } else if (props[k].type === "widget") {
                if (props[k].widget && props[k].widget.props) {
                    fill_props_is_in_shown_list_recursively(props[k].widget.props);
                }
            }
        }
    }
};

/**
 * Transform widget object to content item.
 *
 * @param widget
 * @returns {{component: *, name: *, props, contentId, contentOrder}}
 */
export const transform_widget_to_content_item = (widget) => {
    const props = {};
    for (let k in widget.props) {
        if (widget.props.hasOwnProperty(k) && widget.props[k].isInShownList) {
            if (k === "mapping") {
                continue;
            }

            if (k === "children") {
                const children = transform_children_to_content(widget.props[k]);
                if (children) {
                    props[k] = children;
                }
            } else {
                const value = transform_prop_to_value(widget.props[k]);
                if (value) {
                    props[k] = value;
                }
            }
        }
    }

    props.widgetCode = widget.widgetCode;

    return {
        name: widget.component,
        component: widget.component,
        props: props,
        contentId: widget.contentId,
        contentOrder: widget.contentOrder,
    };
};

export const transform_children_to_content = (children) => {
    if (!children) {
        return null;
    }

    if (Array.isArray(children)) {
        return children.filter((childItem) => !!childItem);
    } else if (typeof children === "object") {
        if (children.multiple) {
            if (!children.array || !children.array.length) {
                return null;
            }

            const contentChildren = children.array.map((propArrayItem) => {
                const childrenInner = [];
                for (let k in propArrayItem) {
                    if (propArrayItem.hasOwnProperty(k)) {
                        const value = transform_item_to_content(propArrayItem[k]);
                        if (value) {
                            childrenInner.push(value);
                        }
                    }
                }

                return childrenInner;
            });

            return [].concat(...contentChildren);
        } else if (children.type === "complex") {
            if (!children.complex) {
                return null;
            }

            const contentChildren = [];
            for (let k in children.complex) {
                if (children.complex.hasOwnProperty(k)) {
                    const value = transform_item_to_content(children.complex[k]);
                    if (value) {
                        contentChildren.push(value);
                    }
                }
            }

            return contentChildren;
        } else if (children.type === "widget") {
            if (!children.widget || !children.widget.props) {
                return null;
            }

            return transform_widget_to_content_item(children.widget);
        } else {
            return escape_html(children.value);
        }
    } else {
        return children;
    }
};

/**
 * Transform item to content item.
 *
 * @param item
 * @return {*}
 */
export const transform_item_to_content = (item) => {
    if (!item) {
        return null;
    }

    if (item.widget) {
        return transform_widget_to_content_item(item.widget);
    } else if (item.type) {
        return item.value;
    } else {
        return item;
    }
};

/**
 * Transform widget prop to values object.
 *
 * @param prop
 * @returns {*}
 */
export const transform_prop_to_value = (prop) => {
    if (!prop || !prop.isInShownList) {
        return null;
    }

    if (prop.multiple) {
        if (!prop.array || !prop.array.length) {
            return null;
        }

        let resultValue;
        if (prop.type === "complex") {
            resultValue = prop.array.map((propArrayItem, index) => {
                const props = {};
                for (let k in propArrayItem) {
                    if (propArrayItem.hasOwnProperty(k)) {
                        const value = transform_prop_to_value(propArrayItem[k]);
                        if (value) {
                            props[k] = value;
                        }
                    }
                }

                if (!Object.keys(props).length) {
                    return null;
                }

                props["key"] = index + 1;

                if (prop.autogenerated_fields) {
                    for (let field in prop.autogenerated_fields) {
                        if (prop.autogenerated_fields.hasOwnProperty(field)) {
                            props[field] = prop.autogenerated_fields[field] + (index + 1);
                        }
                    }
                }

                return props;
            });
        } else {
            resultValue = prop.array.map((propArrayItem, index) => {
                for (let k in propArrayItem) {
                    if (propArrayItem.hasOwnProperty(k)) {
                        const value = transform_prop_to_value(propArrayItem[k]);
                        if (value) {
                            return value;
                        }
                    }
                }

                return null;
            });
        }

        resultValue = resultValue.filter((v) => !!v);

        return resultValue.length ? resultValue : null;
    } else if (prop.type === "complex") {
        if (!prop.complex) {
            return null;
        }

        const props = {};
        for (let k in prop.complex) {
            if (prop.complex.hasOwnProperty(k)) {
                const value = transform_prop_to_value(prop.complex[k]);
                if (value) {
                    props[k] = value;
                }
            }
        }

        if (!Object.keys(props).length) {
            return null;
        }

        return props;
    } else if (prop.type === "widget") {
        if (!prop.widget || !prop.widget.props) {
            return null;
        }

        const props = {};
        for (let k in prop.widget.props) {
            if (prop.widget.props.hasOwnProperty(k)) {
                const value = transform_prop_to_value(prop.widget.props[k]);
                if (value) {
                    props[k] = value;
                }
            }
        }

        if (!Object.keys(props).length) {
            return null;
        }

        return props;
    } else {
        // primitive prop
        if (prop.is_numeric) {
            return prop.value ? prop.value.trim() : prop.value;
        } else if (prop.is_price) {
            return prop.value ? prop.value.replace(',', '.') : prop.value;
        } else if (prop.is_margin) {
            const clear_value = prop.value ? prop.value.replace(/[^0-9-]/g, '') : prop.value;
            return clear_value && !isNaN(clear_value) ? parseInt(clear_value).toString() + 'px' : clear_value;
        } else {
            return prop.value;
        }
    }
};

/**
 * Transform widget props to object with fieldsets (named groups of props) and props.
 *
 * @param props
 * @param parentProp
 * @return {Object}
 */
export const fetch_fieldsets_from_props = (props, parentProp = null) => {
    const result = {};

    for (let k in props) {
        if (props.hasOwnProperty(k)) {
            if (props[k].hasOwnProperty("fieldset") && props[k].fieldset
                    && (!parentProp || !parentProp.fieldset_group || props[k].fieldset.code !== parentProp.code)) {
                let j = props[k].fieldset["code"];
                if (!result[j]) {
                    result[j] = props[k].fieldset;
                    result[j].type = "complex";
                    result[j].complex = {};
                    result[j].fieldset_group = true;
                }

                result[j].complex[k] = props[k];
            } else {
                result[k] = props[k];
            }
        }
    }

    return result;
};

/**
 * @param prop
 * @returns {boolean}
 */
export const is_primitive = (prop) => {
    return prop.type !== 'complex' && !prop.multiple && prop.type !== 'widget';
};

/**
 * @param items Array to swap
 * @param moved Data for moving
 * @param moved.element Moved element
 * @param moved.oldIndex Old index of element
 * @param moved.newIndex New index of element
 * @param sortField Field to swap by
 * @returns {*}
 */
export const swap_items = (items, moved, sortField) => {
    const oldOrder = moved.element[sortField];
    const newOrder = oldOrder + moved.newIndex - moved.oldIndex;

    return items.map((item) => {
        if (item[sortField] === oldOrder) {
            return { ...item, [sortField]: newOrder };
        }

        if (oldOrder <= newOrder && item[sortField] > oldOrder && item[sortField] <= newOrder) {
            return { ...item, [sortField]: item[sortField] - 1 };
        }

        if (oldOrder > newOrder && item[sortField] >= newOrder && item[sortField] < oldOrder) {
            return { ...item, [sortField]: item[sortField] + 1 };
        }

        return item;
    }).sort((itemA, itemB) => itemA[sortField] - itemB[sortField]);
};
