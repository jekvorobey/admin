<template>
    <div class="shadow mt-3 mr-3">
        <div class="card-head">
            {{ title }}
            <fa-icon v-for="(icon, e) in buttons" @click="$emit(e)" :key="e" :icon="icon" class="corner-edit-btn"></fa-icon>
        </div>
        <div :class="[`px-${padding}`, `pb-${padding}`]">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        padding: {
            default: 5
        },
        buttons: Object
    }
}
</script>

<style scoped>
    .card-head {
        height: 48px;
        padding: 8px 16px;
    }
    .corner-edit-btn {
        position: relative;
        float: right;
        top: 5px;
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
        margin-left: var(--gs);
    }
    .corner-edit-btn:hover {
        color: black;
    }
</style>