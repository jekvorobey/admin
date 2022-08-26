<template>
    <div>
        <label :for="id" class="d-flex justify-content-between">
            <div>
                <slot/>
                <fa-icon v-if="$slots.help" icon="question-circle" v-b-popover.hover="$slots.help[0].text"></fa-icon>
            </div>
            <div class="btn-group advanced-styles">
                <button class="btn small-btn badge btn-outline-primary" @click="addAllOptions">all</button>
                <button class="btn small-btn badge btn-outline-danger" @click="deleteAllOptions">X</button>
            </div>
        </label>
        <div class="input-group input-group-sm">
            <div v-if="$slots.prepend" class="input-group-prepend">
                <slot name="prepend"/>
            </div>
            <div class="form-control" :class="{ 'input-error': error, 'h-100': items.length >= 2 }" @click="toggleList">
                <span v-for="item in items" :key="item.value" @click="e => {e.stopPropagation(); remove(item.value)}"
                      class="badge badge-secondary mr-2">
                    {{item.text}} <fa-icon icon="times"></fa-icon>
                </span>
                <fa-icon icon="caret-down" class="float-right"></fa-icon>
            </div>
            <div v-if="$slots.append" class="input-group-append">
                <slot name="append"/>
            </div>
        </div>
        <transition name="slide">
            <div v-if="opened" class="select-list" @mouseleave="toggleList">
                <template v-if="grouped">
                    <v-search-input @onSearch="onSearch" :value="inputSearch"/>
                    <div class="inner-list">
                        <template v-for="group in groups">
                            <div class="select-item select-item--heading" v-if="filteredGroupOptions(group).length > 0">{{ group }}</div>
                            <div
                                    v-for="option in filteredGroupOptions(group)"
                                    class="select-item"
                                    :class="{selected:valueSelected(option.value)}"
                                    @click="toggleOption(option.value)"
                            >{{ option.text }}</div>
                        </template>
                    </div>
                </template>
                <template v-else>
                    <v-search-input @onSearch="onSearch" :value="inputSearch"/>
                    <div class="inner-list">
                        <div
                                v-for="option in filteredOptions"
                                class="select-item"
                                :class="{selected:valueSelected(option.value)}"
                                @click="toggleOption(option.value)"
                        >{{ option.text }}</div>
                    </div>

                </template>
            </div>
        </transition>
        <div class="mt-1 error">
            {{ error }}
        </div>
    </div>
</template>

<script>
    import VSearchInput from "../controls/VSearchInput/VSearchInput.vue";

    export default {
        name: 'f-multi-select',
        components: {VSearchInput},
        inheritAttrs: false,
        props: {
            value: {},
            options: Array,
            grouped: {
                type: Boolean,
                default: false
            },
            error: String,
        },
        data() {
            return {
                opened: false,
                inputSearch: ''
            };
        },
        computed: {
            id() {
                return `filter-select-${this._uid}`
            },
            inputListeners() {
                return Object.assign({}, this.$listeners, {input: this.input});
            },
            items() {
                return this.options.filter(option => this.valueSelected(option.value));
            },
            groups() {
                return [...this.options.reduce((res, value) => {
                    res.add(value.group);
                    return res;
                }, new Set())];
            },
            filteredOptions(l){
                if (this.inputSearch !== null && this.inputSearch !== ''){
                    return this.options.filter(option => {
                            return option.text.toLowerCase().includes(this.inputSearch.toLowerCase())
                        }
                    )
                }
                else {
                    return this.options
                }
            },
        },
        methods: {
            input(e) {
                this.$emit('input', e.target.value);
            },
            toggleList() {
                this.opened = !this.opened;
            },
            remove(value) {
                this.value.splice(this.value.indexOf(value), 1);
                this.$emit('input', this.value);
                this.$emit('change');
            },
            toggleOption(value) {
                if (this.valueSelected(value)) {
                    this.remove(value);
                } else {
                    this.value.push(value);
                    this.$emit('input', this.value);
                    this.$emit('change');
                }
            },
            valueSelected(value) {
                return this.value.indexOf(value) !== -1;
            },
            groupOptions(group) {
                return this.options.filter(option => option.group === group);
            },
            filteredGroupOptions(group){
                if (this.inputSearch !== null && this.inputSearch !== ''){
                    return this.groupOptions(group).filter(option => {
                            return option.text.toLowerCase().includes(this.inputSearch.toLowerCase())
                        }
                    )
                }
                else {
                    return this.groupOptions(group)
                }
            },
            addAllOptions(){
                this.options.forEach(option => {
                    this.value.push(option.value);
                    this.$emit('input', this.value);
                    this.$emit('change');
                })
            },
            deleteAllOptions(){
                this.options.forEach(option => {
                    this.remove(option.value);
                })
            },
            onSearch(value){
                this.inputSearch = value
            }
        },
    }
</script>

<style scoped>
    .select-list {
        position: absolute;
        background: white;
        border: 1px solid #DFDFDF;
        width: calc( 100% - 30px );
        z-index: 9999;
    }
    .inner-list{
        max-height: 300px;
        overflow-y: auto;
    }
    .select-item {
        padding: 5px 10px;
        user-select: none;
    }
    .select-item--heading {
        font-weight: bold;
        text-transform: uppercase;
    }
    .select-item:hover:not(.select-item--heading), .selected {
        background: #F7F7F7;
    }
    .select-item:not(:last-of-type) {
        border-bottom: 1px solid #DFDFDF;
    }
    .error {
        color: red;
        font-size: small;
    }
    .input-error {
        border-color: red;
    }
    .h-100 {
        height: 100%;
        max-height: 200px;
        overflow-y: auto;
    }
    .small-btn{
        border-radius: 0.2rem;
        font-size: 12px;
        padding: 3px 5px;
    }

    .advanced-styles  .btn-outline-danger {
        border-color: #dc354385;
    }

    .advanced-styles .btn-outline-primary {
        border-color: #007bff70;
    }
</style>
