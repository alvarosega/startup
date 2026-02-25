<script setup>
import { useTheme } from '@/Composables/useTheme';
const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <label class="switch-theme">
        <input type="checkbox" :checked="isDark" @change="toggleTheme" />
        <span class="slider-theme">
            <div class="star-theme star_1"></div>
            <div class="star-theme star_2"></div>
            <div class="star-theme star_3"></div>
            <svg viewBox="0 0 16 16" class="cloud-theme"><path fill="#fff" d="m391.84 540.91c-.421-.329-.949-.524-1.523-.524-1.351 0-2.451 1.084-2.485 2.435-1.395.526-2.388 1.88-2.388 3.466 0 1.874 1.385 3.423 3.182 3.667v.034h12.73v-.006c1.775-.104 3.182-1.584 3.182-3.395 0-1.747-1.309-3.186-2.994-3.379.007-.106.011-.214.011-.322 0-2.707-2.271-4.901-5.072-4.901-2.073 0-3.856 1.202-4.643 2.925" transform="matrix(.77976 0 0 .78395-299.99-418.63)"></path></svg>
        </span>
    </label>
</template>

<style scoped>
.switch-theme { font-size: 14px; position: relative; display: inline-block; width: 4em; height: 2.2em; }
.switch-theme input { opacity: 0; width: 0; height: 0; }
.slider-theme { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #2a2a2a; transition: 0.4s; border-radius: 30px; overflow: hidden; }
.slider-theme:before { position: absolute; content: ""; height: 1.2em; width: 1.2em; border-radius: 50%; left: 0.5em; bottom: 0.5em; transition: 0.4s; transition-timing-function: cubic-bezier(0.81, -0.04, 0.38, 1.5); box-shadow: inset 8px -4px 0px 0px #fff; }
.switch-theme input:checked + .slider-theme { background-color: #00a6ff; }
.switch-theme input:checked + .slider-theme:before { transform: translateX(1.8em); box-shadow: inset 15px -4px 0px 15px #ffcf48; }
.star-theme { background-color: #fff; border-radius: 50%; position: absolute; width: 3px; height: 3px; transition: 0.4s; }
.star_1 { left: 2.5em; top: 0.5em; } .star_2 { left: 2.2em; top: 1.2em; } .star_3 { left: 3em; top: 0.9em; }
.switch-theme input:checked + .slider-theme .star-theme { opacity: 0; }
.cloud-theme { width: 3em; position: absolute; bottom: -1em; left: -0.5em; opacity: 0; transition: 0.4s; }
.switch-theme input:checked + .slider-theme .cloud-theme { opacity: 1; }
</style>