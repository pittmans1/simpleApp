<template>
    <div>
        <button :class="['primary', className]">{{ text }}</button>
    </div>
</template>
<script>
export default {
    name: 'ElementButton',
    props: {
        text: {
            type: String,
            default: 'Click me'
        }
    }
}
</script>
<style>
    
</style>