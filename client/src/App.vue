<script setup>
import { onMounted, ref } from "vue";

// One expression so the bundler folds the whole line into a single literal:
// Vite replaces import.meta.env.GREETING_TAG at build time (envPrefix below).
const frontendLine =
  "frontend: hello world oxzoo-php-vue_" + import.meta.env.GREETING_TAG;

const backendLine = ref("");
const backendError = ref("");

onMounted(async () => {
  try {
    const res = await fetch("/api/greeting");
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }
    backendLine.value = await res.text();
  } catch (err) {
    backendError.value = err.message;
  }
});
</script>

<template>
  <main>
    <h1>oxzoo-php-vue</h1>
    <p>{{ frontendLine }}</p>
    <p v-if="backendError">backend: error ({{ backendError }})</p>
    <p v-else-if="!backendLine">backend: loading…</p>
    <p v-else>backend: {{ backendLine }}</p>
  </main>
</template>

<style>
body {
  font-family: system-ui, sans-serif;
  max-width: 40rem;
  margin: 2rem auto;
  color: #1a1a1a;
}

h1 {
  font-size: 1.5rem;
}
</style>
