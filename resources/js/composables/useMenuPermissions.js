import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useMenuPermissions(menuKey) {
    const page = usePage()
    const permissions = computed(() => page.props.auth?.permissions ?? [])

    function can(action) {
        return permissions.value.includes(`${menuKey}.${action}`)
    }

    return {
        permissions,
        can,
    }
}
