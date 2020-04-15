<template>
    <div class="shadow mt-3 mr-3">
        <div v-if="mediaObject.type === publicEventMediaTypes.youtube" class="embed-responsive embed-responsive-16by9 ">
            <iframe
                    width="424"
                    height="238"
                    class="embed-responsive-item"
                    :src="mediaObject.url"
                    allowfullscreen>
            </iframe>
        </div>
        <img
                v-else-if="mediaObject.type === publicEventMediaTypes.image"
                :src="mediaObject.url"
                :class="{'big-image': !small, 'small-image': small}"
                alt="Image">
        <div v-else>
            <a :href="mediaObject.url">Скачать</a>
        </div>
        <slot />
        <fa-icon icon="trash-alt" class="float-right media-btn" @click="$emit('onDelete', mediaObject.id)" />
        <fa-icon icon="pencil-alt" class="float-right media-btn" @click="$emit('onEdit', mediaObject.id)" />
    </div>
</template>

<script>
    export default {
        props: {
            mediaObject: {},
            small: Boolean
        }
    }
</script>

<style scoped>
    .big-image {
        height: calc( 298px - 16px * 2 );
        display: block;
        max-width: 290px;
    }
    .small-image {
        height: calc( 130px - 16px * 2 );
        display: block;
    }
    .embed-responsive {
        height: calc( 298px - 16px * 2 );
        min-width: 300px;
    }
    .media-btn {
        margin-top: 6px;
        margin-right: 6px;
        color: gray;
        transition: 0.3s all;
        cursor: pointer;
    }
    .media-btn:hover {
        color: black;
    }
</style>