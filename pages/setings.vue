<template>
    <h1>
        {{ t('setings') }}
    </h1>


</template>

<script>
import { useLang } from '~/composables/useLang';

export default {
    setup() {
        const { t } = useLang();
        return { t };
    }
}
</script>
