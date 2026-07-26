# World Horizon Technology

## Project purpose
This repository contains a production-ready static website for World Horizon Technology, built with Vue 3 + Vite and Vue Router. The site is designed as a multi-page company website for a Spanish-Ukrainian deep-tech company focused on hardware, edge AI, software, cloud AI, and industrial deployment.

## Repository structure
- src/: application source code
  - views/: route-level page components
  - content/: AI-readable content modules for all website sections
  - router/: Vue Router configuration
- public/: static assets and GitHub Pages fallback files
- docs/: project documentation and AI prompt templates
- .github/workflows/: deployment automation for GitHub Pages

## Core business domains
- Hardware & Edge AI
- Software & Cloud AI
- Agriculture 4.0
- Fisheries & Aquaculture 4.0
- Life Sciences, MedTech & Food
- Strategic partnerships and pilot deployment programs

## AI-readiness standards
- All major website sections are represented as structured content modules.
- Route-level UI is separated from business content.
- Documentation is centralized and explicit for agentic maintenance.
- Prompt templates are provided to support future AI-assisted updates.

## Local development
1. npm install
2. npm run dev
3. npm run build

## Deployment
The site is configured for GitHub Pages via the static branch and the workflow in .github/workflows/deploy.yml.
