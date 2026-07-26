<template>
  <section class="hero">
    <p class="section-title">{{ partnershipsContent.eyebrow }}</p>
    <h1>{{ partnershipsContent.heroTitle }}</h1>
    <div class="content-grid">
      <div v-for="section in partnershipsContent.sections" :key="section.title" class="content-panel">
        <h3>{{ section.title }}</h3>
        <ul>
          <li v-for="bullet in section.bullets" :key="bullet">{{ bullet }}</li>
        </ul>
      </div>
    </div>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const partnershipsContent = siteContent.partnerships
</script>
