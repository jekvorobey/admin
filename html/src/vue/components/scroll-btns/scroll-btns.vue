<template>
    <div>
        <button
            class="btn-scroll left-scroll-btn"
            :class="{'show-scroll': this.showScroll && this.screenWidthToShow}"
            @click="onScroll('left')"
        >
            <v-svg
                    name="arrow-bar-left"
                    modifier="fill-white"
                    width="24"
                    height="24"
            />
        </button>
        <button
            class="btn-scroll right-scroll-btn"
            :class="{'show-scroll': this.showScroll && this.screenWidthToShow}"
            @click="onScroll('right')"
        >
            <v-svg
                    name="arrow-bar-right"
                    modifier="fill-white"
                    width="24"
                    height="24"
            />
        </button>
    </div>
</template>

<script>
    import VSvg from '../controls/VSvg/VSvg.vue';
    import '../../../images/sprite/arrow-bar-left.svg';
    import '../../../images/sprite/arrow-bar-right.svg';
    export default {
        name: "scroll-btns",
        components:{
            VSvg
        },
        props:{
            showScroll: {type: Boolean, default: false}
        },
        data(){
            return {
                screenWidthToShow: false
            }
        },
        methods: {
            onScroll(direction){
                this.$emit('onScroll', direction)
            },
            onResize() {
                this.screenWidthToShow = window.innerWidth < 1280
            }
        },
        created() {
            window.addEventListener('resize', this.onResize);
            this.onResize();
        },
        destroyed() {
            window.removeEventListener('resize', this.onResize)
        },
    }
</script>

<style scoped>
    .btn-scroll{
        transition: 0.3s all;
        width: 100px;
        height: 100px;
        border-radius: 50px;
        position: fixed;
        top: 50%;
        outline: none;
        border: none;
        background: #c19e9e52;
        opacity: 0;
    }
    .btn-scroll:hover {
        background: #c19e9e82;
    }
    .right-scroll-btn{
        right: 50px;
    }
    .show-scroll{
        opacity: 1;
    }
</style>