<template>
  <section class="contact-card">
    <p class="section-title">{{ contactContent.eyebrow }}</p>
    <h1>{{ contactContent.heroTitle }}</h1>
    <p>{{ contactContent.body }}</p>
    <p><strong>Email:</strong> {{ contactContent.email }}</p>
    <p><strong>Location:</strong> {{ contactContent.location }}</p>
  </section>
</template>

<script setup>
import { siteContent } from '../content/siteContent'

const contactContent = siteContent.contact
</script>
