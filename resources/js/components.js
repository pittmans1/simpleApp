import { BDropdownItem, BDropdown } from 'bootstrap-vue-next'

export default {
    BDropdownItem,
    BDropdown,
    registerComponents(app) {
        app.component('BDropdownItem', BDropdownItem)
        app.component('BDropdown', BDropdown)
        const components = {
            'element-table': require('./components/ElementTable.vue').default,
            'element-form': require('./components/ElementForm.vue').default,
            'element-button': require('./components/ElementButton.vue').default,
            'element-modal': require('./components/ElementModal.vue').default,
            'element-trash-panda': require('./components/ElementTrashPanda.vue').default,
        }

        for (const [name, component] of Object.entries(components)) {
            app.component(name, component)
        }

        return components
    },
}