<template>
        <div class="container-fluid">
            <h2 v-if="!isFullScreenMode">{{ title }}</h2>
            <form method="POST" @submit.prevent="submit">

                <input type="hidden" name="_method" value="PUT" v-if="isEditMode"/>
                <input type="hidden" name="preview_uniqid" :value="landingPreview.uniqid" />
                <csrf-field></csrf-field>

                <div class="row mx-0 mb-3" :class="[isFullScreenMode ? 'mt-0' : 'mt-3']">
                    <div class="pl-0 col-md-9">
                        <ul class="nav nav-tabs">
                            <li class="nav-item" @click="switchTab('general')">
                                <a class="nav-link" :class="{active: tab === 'general'}" href="#">Общее</a>
                            </li>
                            <li class="nav-item" @click="switchTab('content')">
                                <a class="nav-link" :class="{active: tab === 'content'}" href="#">Контент</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-3 pr-0">
                        <button v-if="isEditMode" type="button" class="btn btn-danger" @click="deleteLanding" style="float:right">Удалить</button>
                        <button type="button" class="btn btn-link" @click="clickPreviewLandingButton" style="float:right; margin-right:10px">Превью</button>
                    </div>
                </div>


                <div v-show="tab === 'general'">
                    <div class="form-group required" :class="{ 'validation-error': errors.has('general.name') }">
                        <label class="control-label" for="landing-name">Название</label>
                        <input type="text"
                               v-model="landing.name"
                               data-vv-scope="general"
                               data-vv-name="name"
                               name="name"
                               class="form-control"
                               id="landing-name"
                               required
                               v-validate="{ required: true }"
                               placeholder="Введите название лэндинга">
                        <div class="validation-error-message" v-show="errors.has('general.name')">
                            {{ errors.first('general.name') }}
                        </div>
                    </div>

                    <div class="form-group required" :class="{ 'validation-error': errors.has('general.code') }">
                        <label class="control-label" for="landing-code">Символьный код</label>
                        <input type="text"
                               v-model="landing.code"
                               data-vv-scope="general"
                               data-vv-name="code"
                               name="code"
                               class="form-control"
                               id="landing-code"
                               required
                               v-validate="{ required: true, isCode: true }"
                               placeholder="Введите символьный код"
                               aria-describedby="codeHelp"
                        >
                        <div class="validation-error-message" v-show="errors.has('general.code')">
                            {{ errors.first('general.code') }}
                        </div>
                        <small id="codeHelp" class="form-text text-muted">
                            Будет использован в ссылке лэндинга - /promo/{code}/
                        </small>
                    </div>
                </div>

                <div v-show="tab === 'content'">
                    <div class="mb-3" v-show="contentTab === 'structure'">
                        <div class="container-fluid pl-0">
                            <div class="row landing-structure">
                                <div class="col-md-2 landing-structure__left">
                                    <div class="form-group mr-0 mb-2">
                                        <div class="input-group w-100">
                                            <input type="text" class="form-control" placeholder="поиск по списку" v-model="searchQuery" />
                                        </div>
                                    </div>
                                    <widgets-list
                                            :widgetsList="widgetsListFiltered"
                                            :cloneWidget="cloneWidgetToSequence"
                                            @swapWidgetsListItems="swapWidgetsListItems"
                                    ></widgets-list>
                                </div>
                                <div class="col-md-10 landing-structure__right">
                                    <div class="row widgets-sequence">
                                        <widgets-sequence
                                                :widgets="usedWidgets"
                                                @addContentItem="addContentItem"
                                                @swapContentItems="swapContentItems"
                                                @removeUsedWidget="removeUsedWidget"
                                                :selectedWidget="selectedWidget"
                                                @update:selectedWidget="selectWidget"
                                        ></widgets-sequence>
                                    </div>
                                    <widget-settings
                                            :widget.sync="selectedWidget"
                                            @resetSelectedWidget="resetSelectedWidget"
                                    ></widget-settings>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group landing-content" v-show="contentTab === 'raw'">
                        <label class="control-label" for="landing-content">Контент лэндинга</label>
                        <textarea class="form-control"
                                  name="content"
                                  rows="50"
                                  id="landing-content"
                                  v-model="landing.content"
                                  @input="handleChangeLandingContent"
                        ></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Сохранить</button>
                <button type="button" class="btn" @click="cancel">Отменить</button>

                <div v-if="tab === 'content'" class="btn-group" style="float: right;">
                    <button type="button" class="btn btn-outline-dark"
                            :class="{'active': contentTab === 'structure'}"
                            @click="switchContentTab('structure')">
                        Структура
                    </button>
                    <button type="button" class="btn btn-outline-dark"
                            :class="{'active': contentTab === 'raw'}"
                            @click="switchContentTab('raw')">
                        Raw
                    </button>
                </div>
            </form>
        </div>
</template>

<script>
    import './style.css';

    import Services from "../../../../../../scripts/services/services";
    import Datepicker from 'vue2-datepicker';
    import LandingFieldsStorage from "./landing-fields-storage";
    import {
        fill_prop_recursively_with_default,
        fill_prop_recursively,
        fill_props_is_in_shown_list_recursively,
        swap_items,
        transform_widget_to_content_item
    } from './widgets-helpers';
    import CsrfField from "./components/CsrfField.vue";
    import WidgetSettings from "./components/WidgetSettings.vue";
    import WidgetsList from "./components/WidgetsList.vue";
    import WidgetsSequence from "./components/WidgetsSequence.vue";
    import _ from "lodash";
    import modalMixin from "../../../../../mixins/modal";

    export default {
        components: {
            Datepicker,
            CsrfField,
            WidgetSettings,
            WidgetsList,
            WidgetsSequence
        },
        mixins: [modalMixin],
        props: [
            'iLanding',
            'iWidgetsList',
            'iAllWidgetsNames',
        ],
        data() {
            return {
                searchQuery: '',
                widgetsList: this.iWidgetsList,
                allWidgetsNames: this.iAllWidgetsNames,
                usedWidgets: [],
                contentItems: [],
                selectedWidget: null,
                landing: {
                    id: this.iLanding.id || '',
                    name: this.iLanding.name || '',
                    code: this.iLanding.code || '',
                    widgets: this.iLanding.widgets || [],
                },
                landingPreview: {
                    uniqid: '',
                },
                tab: 'general',
                contentTab: 'structure',
            }
        },
        methods: {
            loadContentItems(newContent = '') {
                newContent = newContent
                    ? newContent
                    : (this.landing && this.landing.widgets ? this.landing.widgets : []);

                this.contentItems = newContent.map((item, index) => {
                        item.contentOrder = index;
                        item.contentId = index;
                        return item;
                });
            },
            loadUsedWidgets() {
                let countByCodes = {};
                this.usedWidgets = this.contentItems
                    .filter((item) => item && item.component)
                    .map((widgetValues) => {
                        const { component, contentId, contentOrder, props: values } = widgetValues;
                        const widgetToClone = this.widgetsList.filter((item) => {
                            return values.widgetCode
                                ? (values.widgetCode === item.widgetCode)
                                : (component === item.component);
                        })[0];

                        if (!widgetToClone) return null;

                        const widget = JSON.parse(JSON.stringify(widgetToClone));

                        // fill by values from content
                        for (let k in widget.props) {
                            if (widget.props.hasOwnProperty(k)) {
                                fill_prop_recursively(widget.props[k], values[k], k, this.parser);
                            }
                        }

                        fill_props_is_in_shown_list_recursively(widget.props);

                        let counter = countByCodes[widget.widgetCode] ? countByCodes[widget.widgetCode] + 1 : 1;
                        countByCodes[widget.widgetCode] = counter;

                        delete widget.dragWidgetsListOrder;

                        return {
                            ...widget,
                            contentId,
                            contentOrder,
                            counter: counter,
                            id: widget.widgetCode + counter,
                            name: widget.name + ' [' + counter + ']',
                        };
                    })
                    .filter((widget) => !!widget);
            },
            validateFormAndRun(scope, successCallback) {
                this.$validator.validateAll(scope).then(valid => {
                    if (valid) {
                        if (successCallback) {
                            successCallback();
                        }
                    } else {
                        // Тут можно сделать скрол (window.scroll) до элемента с ошибкой $('.validation-error-message:visible')
                        // if ($('.validation-error-message:visible').length) {}
                    }
                });
            },
            switchTab(newTab) {
                this.validateFormAndRun(this.tab, () => {
                    this.tab = newTab;
                });
            },
            switchContentTab(newTab) {
                this.validateFormAndRun('content', () => {
                    this.contentTab = newTab;
                });
            },
            swapWidgetsListItems(moved) {
                const oldDragOrder = moved.element.dragWidgetsListOrder;
                const newDragOrder = oldDragOrder + moved.newIndex - moved.oldIndex;
                this.widgetsList = this.widgetsList.map((widget) => {
                    if (widget.dragWidgetsListOrder === oldDragOrder) {
                        return { ...widget, dragWidgetsListOrder: newDragOrder };
                    }

                    if (oldDragOrder <= newDragOrder && widget.dragWidgetsListOrder > oldDragOrder && widget.dragWidgetsListOrder <= newDragOrder) {
                        return { ...widget, dragWidgetsListOrder: widget.dragWidgetsListOrder - 1 };
                    }

                    if (oldDragOrder > newDragOrder && widget.dragWidgetsListOrder >= newDragOrder && widget.dragWidgetsListOrder < oldDragOrder) {
                        return { ...widget, dragWidgetsListOrder: widget.dragWidgetsListOrder + 1 };
                    }

                    return widget;
                });
            },
            cloneWidgetToSequence(widgetToClone) {
                const widget = JSON.parse(JSON.stringify(widgetToClone));

                // fill by default values
                for (let k in widget.props) {
                    if (widget.props.hasOwnProperty(k)) {
                        fill_prop_recursively_with_default(widget.props[k]);
                    }
                }

                fill_props_is_in_shown_list_recursively(widget.props);

                const usedWidgetsCounts = this.usedWidgets
                    .filter((usedWidget) => usedWidget.widgetCode === widget.widgetCode)
                    .map((usedWidget) => usedWidget.counter);
                const newCounter = usedWidgetsCounts.length ? Math.max.apply(null, usedWidgetsCounts) + 1 : 1;

                const newContentId = this.contentItems.length;

                delete widget.dragWidgetsListOrder;

                return {
                    ...widget,
                    contentId: newContentId,
                    counter: newCounter,
                    id: widget.widgetCode + newCounter,
                    name: widget.name + ' [' + newCounter + ']',
                };
            },
            selectWidget(widget) {
                if (this.selectedWidget && this.selectedWidget.id !== widget.id) {
                    this.validateFormAndRun('content', () => {
                        this.selectedWidget = widget;
                        this.setSelectedWidgetId();
                    });
                    return;
                }

                this.selectedWidget = widget;
                this.setSelectedWidgetId();
            },
            setSelectedWidgetId() {
                const value = (this.selectedWidget && this.selectedWidget.id) ? this.selectedWidget.id : '';
                LandingFieldsStorage.set(this.landing.id, 'selectedWidgetId', value);
            },
            resetSelectedWidget(validate = true) {
                if (validate) {
                    this.validateFormAndRun('content', () => {
                        this.selectedWidget = null;
                        this.setSelectedWidgetId();
                    });
                    return;
                }

                this.selectedWidget = null;
                this.setSelectedWidgetId();
            },
            updateUsedWidgetsContentOrder() {
                const contentItemsOrder = {};
                this.contentItems.forEach((item) => {
                    contentItemsOrder[item.contentId] = item.contentOrder;
                });

                this.usedWidgets = this.usedWidgets.map((widget) => ({
                    ...widget,
                    contentOrder: contentItemsOrder[widget.contentId],
                }));
            },
            addContentItem(added) {
                const newOrder = added.newIndex;
                this.contentItems = this.contentItems.map((item) => {
                    if (item.contentOrder >= newOrder) {
                        return { ...item, contentOrder: item.contentOrder + 1 };
                    }

                    return item;
                });

                const newItem = transform_widget_to_content_item(added.element);
                this.contentItems.push({ ...newItem, contentOrder: newOrder });

                this.contentItems.sort((itemA, itemB) => itemA.contentOrder - itemB.contentOrder);
                this.updateUsedWidgetsContentOrder();
                this.updateContent();
            },
            swapContentItems(moved) {
                this.contentItems = swap_items(this.contentItems, moved, 'contentOrder');
                this.updateUsedWidgetsContentOrder();
                this.updateContent();
            },
            removeUsedWidget(widgetToRemove) {
                this.resetSelectedWidget(false);
                this.usedWidgets = this.usedWidgets.filter((widget) => widget.id !== widgetToRemove.id);
                this.contentItems = this.contentItems
                    .filter((item) => item.contentId !== widgetToRemove.contentId)
                    .map((item, index) => ({ ...item, contentOrder: index }));
                this.updateUsedWidgetsContentOrder();
                this.updateContent();
            },
            updateContentItem(updatedItem) {
                const index = this.contentItems.findIndex((item) => item.contentId === updatedItem.contentId);
                const oldItemChildren = this.contentItems[index].children;
                if (oldItemChildren) {
                    const newItemChildren = updatedItem.children ? updatedItem.children.slice() : [];

                    let childComponentIndex = 0;
                    updatedItem.children = oldItemChildren.map((item) => {
                        if (item.component) {
                            childComponentIndex++;
                            return newItemChildren[childComponentIndex - 1];
                        }

                        return item;
                    });
                }
                this.contentItems[index] = updatedItem;
                this.updateContent();
            },
            updateContent() {
                if (!this.parser) return;

                this.landing.content = this.parser.convertToJSX(this.contentItems);
                this.updatePreviewLanding();
            },
            refreshContentAndWidgets(newContent) {
                this.loadContentItems(newContent);
                this.loadUsedWidgets();

                const updatedSelectedWidget = this.selectedWidget
                    ? this.usedWidgets.find((widget) => widget.id === this.selectedWidget.id)
                    : null;
                this.selectedWidget = null;
                if (updatedSelectedWidget) {
                    this.selectedWidget = updatedSelectedWidget;
                }
                LandingFieldsStorage.set(this.landing.id, 'selectedWidgetId',
                    this.selectedWidget && this.selectedWidget.id ? this.selectedWidget.id : '');
            },
            handleChangeLandingContent: _.debounce(function(e) {
                this.refreshContentAndWidgets(e.target.value);
            }, 700),
            onErrorJsxParser: _.debounce((error) => {
                this.notificationError("Допущена ошибка в разметке");
            }, 3000),
            updatePreviewLanding(callback) {
                let vm = this;
                if (this.landing && this.landing.name) {
                    // todo Реализовать в рамках #55634
                    axios.post(`/ajax/landings-preview/`, {
                        landing: this.landing,
                        landingPreview: this.landingPreview,
                    }).then((response) => {
                        const previewData = response.data.item;
                        vm.landingPreview.uniqid = previewData.uniqid;
                        if (callback) {
                            callback(previewData);
                        }
                    }).catch((err) => this.notificationError(err.message));
                }
            },
            clickPreviewLandingButton(event) {
                if(!this.landing.name){
                    this.notificationError('Обязательное поле "Название" не заполнено');
                    return;
                }

                this.validateFormAndRun(this.tab, () => {
                    this.updatePreviewLanding((previewData) => {
                        // todo Реализовать в рамках #55634
                    });
                });
            },
            submit() {
                let model = this.landing;
                model.widgets = this.contentItems;

                if (this.isEditMode) {
                    // Update
                    Services.net()
                        .put(this.getRoute('landing.update', {id: this.landing.id,}), {}, model)
                        .then((data) => {
                            this.showMessageBox({title: 'Изменения сохранены'});
                            window.location.href = this.route('landing.listPage');
                        })
                        .catch(() => {
                            this.notificationError('Попробуйте позже');
                        });
                }
                else {
                    // Create
                    Services.net()
                        .post(this.getRoute('landing.create'), {}, model)
                        .then((data) => {
                            this.showMessageBox({title: 'Страница сохранена'});
                            window.location.href = this.route('landing.listPage');
                        })
                        .catch(() => {
                            this.notificationError('Попробуйте позже');
                        });
                }

                return false;
            },
            deleteLanding() {
                if (this.landing.id) {
                    Services.net()
                        .delete(this.getRoute('landing.delete', {id: this.landing.id}))
                        .then((data) => {
                            this.showMessageBox({title: 'Элемент удалён'});
                            window.location.href = this.route('landing.listPage');
                        })
                        .catch(() => {
                            this.notificationError('Попробуйте позже')
                        });
                }
            },
            cancel() {
                window.location.href = this.route('landing.listPage');
            },
            notificateAndLogError(error) {
                console.error(error);
                this.notificationError(error.message);
            },
            restoreSelectedTabs() {
                if (LandingFieldsStorage.get(this.landing.id, 'tab')) {
                    this.tab = LandingFieldsStorage.get(this.landing.id, 'tab');
                }
                if (LandingFieldsStorage.get(this.landing.id, 'contentTab')) {
                    this.contentTab = LandingFieldsStorage.get(this.landing.id, 'contentTab');
                }
            },
            restoreSelectedWidget() {
                if (LandingFieldsStorage.get(this.landing.id, 'selectedWidgetId')) {
                    this.selectedWidget = this.usedWidgets
                        .find((widget) => widget.id === LandingFieldsStorage.get(this.id, 'selectedWidgetId'));
                }
            },
            notificationError(message) {
                this.showMessageBox({title: 'Ошибка', text: message});
            },
        },
        computed: {
            isEditMode() {
                return !!this.landing.id;
            },
            isFullScreenMode() {
                return this.tab === 'content';
            },
            title() {
                return this.isEditMode ? `Редактирование лэндинга "${this.landing.name}"` : 'Добавление нового лэндинга';
            },
            widgetsListFiltered() {
                return this.widgetsList.filter(widget => {
                    return widget.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                }).sort((widgetA, widgetB) => widgetA.dragWidgetsListOrder - widgetB.dragWidgetsListOrder);
            }
        },
        watch: {
            'selectedWidget': {
                handler: _.debounce(function (newValue, oldValue) {
                    if (!newValue || !oldValue || (newValue.id !== oldValue.id)) return;

                    const item = transform_widget_to_content_item(newValue);

                    this.updateContentItem(item);
                }, 700),
                deep: true,
            },
            tab(newTab) {
                LandingFieldsStorage.set(this.landing.id, 'tab', newTab);
            },
            contentTab(newTab) {
                LandingFieldsStorage.set(this.landing.id, 'contentTab', newTab);
            },
        },
        created() {
            this.$validator.extend('isCode', {
                getMessage: field => 'Допустимые символы: a-z, 0-9, - и _',
                validate: value => /^[-_a-z0-9]+$/.test(value),
            });
        },
        mounted() {
            LandingFieldsStorage.init();

            if (this.landing.id) {
                this.loadContentItems();
                this.loadUsedWidgets();
                this.restoreSelectedWidget();
            }

            this.restoreSelectedTabs();
        },
    }
</script>
