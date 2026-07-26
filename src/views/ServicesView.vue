<template>
  <section class="hero">
    <p class="section-title">{{ servicesContent.eyebrow }}</p>
    <h1>{{ servicesContent.heroTitle }}</h1>
    <p>{{ servicesContent.heroText }}</p>

    <div class="card-grid">
      <article v-for="service in servicesContent.services" :key="service.title" class="card">
        <h3>{{ service.title }}</h3>
        <p>{{ service.description }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const servicesContent = siteContent.services
</script>
