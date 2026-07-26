<template>
  <section class="hero">
    <p class="section-title">{{ aboutContent.eyebrow }}</p>
    <h1>{{ aboutContent.heroTitle }}</h1>
    <p>{{ aboutContent.heroText }}</p>

    <div class="content-grid">
      <div class="content-panel">
        <h3>{{ aboutContent.sections[0].title }}</h3>
        <p>{{ aboutContent.sections[0].body }}</p>
      </div>
      <div class="content-panel">
        <h3>{{ aboutContent.sections[1].title }}</h3>
        <p>{{ aboutContent.sections[1].body }}</p>
      </div>
    </div>

    <div class="card-grid">
      <article v-for="value in aboutContent.values" :key="value.title" class="card">
        <h3>{{ value.title }}</h3>
        <p>{{ value.description }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const aboutContent = siteContent.about
</script>
