<template>
  <section class="hero">
    <p class="section-title">{{ homeContent.eyebrow }}</p>
    <h1>{{ homeContent.heroTitle }}</h1>
    <p>{{ homeContent.heroText }}</p>
    <div class="card-grid">
      <article v-for="card in homeContent.cards" :key="card.title" class="card">
        <h3>{{ card.title }}</h3>
        <p>{{ card.description }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const homeContent = siteContent.home
</script>
