import { BDropdownItem, BDropdown } from 'bootstrap-vue-next'
import ElementTable from './components/ElementTable.vue'
import ElementForm from './components/ElementForm.vue'
import ElementButton from './components/ElementButton.vue'
import ElementModal from './components/ElementModal.vue'
import ElementTrashPanda from './components/ElementTrashPanda.vue'
import ElementThemeSelctor from './components/ElementThemeSelctor.vue'

export default {
    BDropdownItem,
    BDropdown,
    registerComponents(app) {
        app.component('BDropdownItem', BDropdownItem)
        app.component('BDropdown', BDropdown)
        const components = {
            'element-table': ElementTable,
            'element-form': ElementForm,
            'element-button': ElementButton,
            'element-modal': ElementModal,
            'element-trash-panda': ElementTrashPanda,
            'element-theme-selctor': ElementThemeSelctor,
        }

        for (const [name, component] of Object.entries(components)) {
            app.component(name, component)
        }

        return components
    },
}