<script setup>
import { ref, onMounted } from 'vue';
const isFullscreen = ref(false);
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        isFullscreen.value = true;
    } else {
        document.exitFullscreen();
        isFullscreen.value = false;
    }
};
</script>

<template>
    <label class="switch-fs">
        <input class="chk-fs" type="checkbox" @change="toggleFullscreen" :checked="isFullscreen" />
        <span class="slider-fs"></span>
    </label>
</template>

<style scoped>
.switch-fs { font-size: 14px; position: relative; display: inline-block; width: 1.2em; height: 3.3em; }
.chk-fs { opacity: 0; width: 0; height: 0; }
.slider-fs { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 5px; }
.slider-fs:before { position: absolute; content: ""; height: .5em; width: 2.4em; border-radius: 5px; left: -0.6em; top: 0.2em; background-color: white; box-shadow: 0 6px 7px rgba(0,0,0,0.3); transition: .4s; }
.slider-fs:after { content: ""; background: linear-gradient(transparent 50%, rgba(255, 255, 255, 0.15) 0) 0 50% / 50% 100%; border: 0.25em solid transparent; border-left: 0.4em solid #ffffff; transition: 0.3s; transform: translateX(-22.5%) rotate(90deg); position: relative; top: 0.5em; left: 0.55em; width: 2em; height: 1em; display: block; }
.chk-fs:checked + .slider-fs { background-color: #00ffa3; }
.chk-fs:checked + .slider-fs:before { transform: translateY(2.3em); }
.chk-fs:checked + .slider-fs:after { transform: rotateZ(90deg) rotateY(180deg) translateY(0.45em) translateX(-1.4em); }
</style>