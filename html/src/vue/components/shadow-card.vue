<template>
    <div class="shadow mt-3 mr-3">
        <div class="card-head">
            {{ title }}
            <fa-icon v-if="editBtn" @click="$emit('on-edit')" icon="pencil-alt" class="corner-edit-btn"></fa-icon>
        </div>
        <div class="px-5 pb-5">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            title: String,
            editBtn: Boolean,
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
    }
    .corner-edit-btn:hover {
        color: black;
    }
</style>