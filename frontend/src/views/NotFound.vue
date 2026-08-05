<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="flex min-h-full flex-col items-center justify-center gap-3 px-4 text-center">
    <p class="text-6xl font-bold text-brand-600">404</p>
    <p class="text-slate-600">This page could not be found.</p>
    <RouterLink :to="{ name: 'dashboard' }" class="font-semibold text-brand-600 hover:text-brand-700">
      Go back home
    </RouterLink>
  </div>
</template>
