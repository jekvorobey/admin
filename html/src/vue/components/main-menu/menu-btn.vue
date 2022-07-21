<template>
    <b-navbar-toggle
            target="nav-collapse"
            class="main-btn"
            @click="isToggle = !isToggle"
            :class="{'toggled': isToggle}"
    ></b-navbar-toggle>
</template>

<script>
    export default {
        name: 'main-btn',
        data(){
            return{
                isToggle: false
            }
        }
    };
</script>

<style scoped>
    .main-btn{
        transition: 1.5s all;
        margin-right: 1rem;
        border: none;
        outline: none;
        padding: 4px 0 0 0;
    }
    .toggled{
        padding: 0 0 4px 0;
        transform: rotateZ(180deg);
    }
</style>