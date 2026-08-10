<template>
  <section class="hero">
    <p class="section-title">{{ projectsContent.eyebrow }}</p>
    <h1>{{ projectsContent.heroTitle }}</h1>
    <p>{{ projectsContent.heroText }}</p>

    <div class="content-grid">
      <div v-for="project in projectsContent.projects" :key="project.title" class="content-panel">
        <h3>{{ project.title }}</h3>
        <p>{{ project.description }}</p>
        <p><strong>{{ project.outcome }}</strong></p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const projectsContent = siteContent.projects
</script>
