# Project map

## Website purpose
World Horizon Technology is presented as a multi-page company website with sections for company story, services, project focus, industries, and contact.

## Route map
- / : Home
- /about : Company overview and values
- /services : Core service offerings
- /projects : Project focus and outcomes
- /industries : Industry-specific solutions
- /contact : Partnership and contact information

## Source map
- src/views/: page components used by the router
- src/content/siteContent.js : centralized, AI-readable content definitions
- src/router/index.js : route definitions
- public/: static deployment assets and GitHub Pages fallbacks

## Content conventions
- Keep site content in structured objects in src/content/siteContent.js.
- Keep presentation logic in Vue components.
- Keep route names stable and descriptive.
- Prefer semantic, business-oriented labels over generic placeholder text.
