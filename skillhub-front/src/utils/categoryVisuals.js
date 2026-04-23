const CATEGORY_MAP = {
  developpement: { label: "Developpement", icon: "fa-solid fa-code" },
  design: { label: "Design", icon: "fa-solid fa-palette" },
  marketing: { label: "Marketing", icon: "fa-solid fa-bullhorn" },
  "soft-skills": { label: "Soft Skills", icon: "fa-solid fa-people-group" },
  data: { label: "Data", icon: "fa-solid fa-chart-line" },
  "creation-contenu": {
    label: "Creation de Contenu",
    icon: "fa-solid fa-photo-film",
  },
  entrepreneuriat: { label: "Entrepreneuriat", icon: "fa-solid fa-rocket" },
};

const NAME_HINTS = [
  {
    keys: ["developpement", "development", "web", "code"],
    icon: "fa-solid fa-code",
  },
  { keys: ["design", "ux", "ui", "figma"], icon: "fa-solid fa-palette" },
  {
    keys: ["marketing", "seo", "social", "media"],
    icon: "fa-solid fa-bullhorn",
  },
  {
    keys: ["soft", "communication", "leadership"],
    icon: "fa-solid fa-people-group",
  },
  { keys: ["data", "analytics", "analyse"], icon: "fa-solid fa-chart-line" },
  {
    keys: ["contenu", "video", "podcast", "media"],
    icon: "fa-solid fa-photo-film",
  },
  {
    keys: ["entrepreneuriat", "startup", "business"],
    icon: "fa-solid fa-rocket",
  },
  {
    keys: ["securite", "security", "cyber"],
    icon: "fa-solid fa-shield-halved",
  },
  { keys: ["devops", "infra"], icon: "fa-solid fa-gears" },
  { keys: ["mobile"], icon: "fa-solid fa-mobile-screen" },
];

function normalize(text) {
  return String(text || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
}

export function getCategoryVisualByKey(key) {
  return (
    CATEGORY_MAP[key] || {
      label: key || "Categorie",
      icon: "fa-solid fa-book-open",
    }
  );
}

export function getCategoryIconByName(name) {
  const normalized = normalize(name);
  for (const entry of NAME_HINTS) {
    if (entry.keys.some((key) => normalized.includes(key))) {
      return entry.icon;
    }
  }
  return "fa-solid fa-book-open";
}

export function getCategoriesLabelMap() {
  return Object.fromEntries(
    Object.entries(CATEGORY_MAP).map(([key, value]) => [key, value.label]),
  );
}
