export const navigationItems = [
  { name: 'Home', path: '/' },
  { name: 'Capabilities', path: '/capabilities' },
  { name: 'Industries', path: '/industries' },
  { name: 'Partnerships', path: '/partnerships' },
  { name: 'Contact', path: '/contact' },
]

export const siteContent = {
  company: {
    name: 'World Horizon Technology',
    tagline: 'Spanish-Ukrainian deep-tech engineering for resilient digital systems.',
    description:
      'We merge EU quality standards with world-class Ukrainian expertise in drone technologies, dual-use solutions, and unmanned systems digitalization.',
  },
  home: {
    eyebrow: 'Spanish-Ukrainian deep-tech engineering',
    heroTitle: 'We build intelligent hardware and software systems for resilient operations in the physical world.',
    heroText:
      'World Horizon Technology combines EU quality standards with Ukrainian engineering depth to create custom AI integrations from edge devices to cloud analytics.',
    cards: [
      {
        title: 'Hardware & Edge AI',
        description: 'Board design, sensor development, STM32 firmware, and on-device machine learning for offline decisions.',
      },
      {
        title: 'Software & Cloud AI',
        description: 'Autonomous agents, workflow orchestration, predictive dashboards, and cloud architecture on Snowflake and IBM.',
      },
      {
        title: 'Full Lifecycle Delivery',
        description: 'From PCB prototyping and assembly to software engineering, deployment, and ongoing optimization.',
      },
    ],
  },
  capabilities: {
    eyebrow: 'Capabilities',
    heroTitle: 'End-to-end product engineering for intelligent systems.',
    sections: [
      {
        title: 'Hardware & Edge AI',
        bullets: [
          'PCB prototyping and hardware assembly.',
          'Custom sensor development and STM32 firmware.',
          'Embedded ML models for real-time local inference and offline execution.',
        ],
      },
      {
        title: 'Software & Cloud AI',
        bullets: [
          'Autonomous AI agents and multi-agent workflows.',
          'Predictive business dashboards and data products.',
          'Enterprise cloud platforms on Snowflake and IBM.',
        ],
      },
    ],
  },
  industries: {
    eyebrow: 'Target industries',
    heroTitle: 'Purpose-built systems for demanding environments.',
    cards: [
      {
        title: 'Agriculture 4.0',
        description: 'Edge AI drone vision for crop monitoring, smart soil sensing, and field intelligence.',
      },
      {
        title: 'Fisheries & Aquaculture 4.0',
        description: 'Autonomous IoT water-monitoring systems designed for commercial marine deployment.',
      },
      {
        title: 'Life Sciences, MedTech & Food',
        description: 'Custom pipelines for lab automation, raw material quality control, and traceability.',
      },
    ],
  },
  partnerships: {
    eyebrow: 'Strategic partnerships',
    heroTitle: 'We enable pilots, validation, and long-term scaling.',
    sections: [
      {
        title: 'Program model',
        bullets: [
          'Supported by ADLE and the Cloud Incubator HUB at UPCT in Murcia.',
          'Coverage from prototyping to deployment and analytics.',
          'Flexible, tailored financial terms for early adopters.',
        ],
      },
      {
        title: 'Ideal collaboration',
        bullets: [
          'IoT pilots in maritime and agricultural environments.',
          'Field testing under real operating conditions.',
          'Joint development of durable, production-ready solutions.',
        ],
      },
    ],
  },
  contact: {
    eyebrow: 'Contact',
    heroTitle: 'Let’s design a resilient technology pathway for your environment.',
    body: 'World Horizon Technology is actively seeking strategic partners to test IoT devices in maritime and agricultural settings. Early adopters receive highly competitive, tailored financial terms.',
    email: 'partnerships@worldhorizontechnology.com',
    location: 'Spain • Ukraine • Global deployment partnerships',
  },
}
