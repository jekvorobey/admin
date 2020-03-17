<template>
    <div v-if="canShowMapping"
         class="widget-settings__mapping-parent">
        <img :src="image.complex.src.value" alt="Изображение" @click="addMarker" ref="mappingImage" class="widget-settings__mapping-image">
        <template v-for='marker in points.array'>
            <div
                v-if="marker.x.value && marker.y.value"
                class="widget-settings__mapping-marker"
                :style="{top: marker.y.value + '%', left: marker.x.value + '%'}"
                :title="marker.name.value"
            ></div>
        </template>
    </div>
</template>

<script>
export default {
    props: ['image', 'points'],
    methods: {
        addMarker($e) {
            if (!this.$refs.hasOwnProperty('mappingImage')) {
                alert('Ошибка!');
                return;
            }

            let points = JSON.parse(JSON.stringify(this.points));
            let newItemId;
            for (let i = 0; i < points.array.length; i++) {
                if (!points.array[i].x.value && !points.array[i].y.value) {
                    newItemId = i;
                    break;
                }
            }

            if (typeof newItemId === "undefined") {
                const newItem = JSON.parse(JSON.stringify(points.array_item));
                points.array.push(newItem);
                newItemId = points.array.length - 1;
            }

            points.array[newItemId].x.value = ($e.layerX * 100) / this.$refs.mappingImage.clientWidth;
            points.array[newItemId].y.value = ($e.layerY * 100) / this.$refs.mappingImage.clientHeight;
            this.$emit('update:points', points);

        }
    },
    computed: {
        canShowMapping() {
            return !!(this.image.complex && this.image.complex.src
                && this.image.complex.src.value && this.image.complex.src.value.length);
        },
    },
};
</script>
