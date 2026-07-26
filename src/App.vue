<template>
  <div class="app-shell">
    <header class="topbar">
      <router-link class="brand" to="/">
        <span class="brand-mark">WH</span>
        <span>{{ siteContent.company.name }}</span>
      </router-link>
      <nav class="nav-links" aria-label="Primary navigation">
        <router-link v-for="item in navigationItems" :key="item.path" :to="item.path">{{ item.name }}</router-link>
      </nav>
    </header>

    <main>
      <router-view />
    </main>

    <footer class="site-footer">
      <p>{{ siteContent.company.name }} • {{ siteContent.company.tagline }}</p>
    </footer>
  </div>
</template>

<script setup>
import { navigationItems, siteContent } from './content/siteContent'
</script>
