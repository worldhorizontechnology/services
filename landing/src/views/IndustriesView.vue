<template>
  <section class="hero">
    <p class="section-title">{{ industriesContent.eyebrow }}</p>
    <h1>{{ industriesContent.heroTitle }}</h1>
    <div class="card-grid">
      <article v-for="card in industriesContent.cards" :key="card.title" class="card">
        <h3>{{ card.title }}</h3>
        <p>{{ card.description }}</p>
      </article>
    </div>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const industriesContent = siteContent.industries
</script>
