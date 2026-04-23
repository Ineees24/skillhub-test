const baseAteliers = [
  {
    id: 1,
    titre: "Introduction à React",
    description:
      "Découvrez les bases de React et créez votre première application interactive.",
    categorie: "developpement",
    duree: 4,
    prix: 89,
    formateur: "Emma Laurent",
    niveau: "Débutant",
  },
  {
    id: 2,
    titre: "UI Design avec Figma",
    description:
      "Maîtrisez Figma pour créer des interfaces modernes et professionnelles.",
    categorie: "design",
    duree: 3,
    prix: 75,
    formateur: "Clara Roussel",
    niveau: "Tous niveaux",
  },
  {
    id: 3,
    titre: "SEO pour débutants",
    description:
      "Apprenez les techniques de référencement naturel pour améliorer votre visibilité.",
    categorie: "marketing",
    duree: 2,
    prix: 49,
    formateur: "Sara Fontaine",
    niveau: "Débutant",
  },
  {
    id: 4,
    titre: "Communication assertive",
    description:
      "Développez votre capacité à communiquer avec confiance et clarté.",
    categorie: "soft-skills",
    duree: 3,
    prix: 65,
    formateur: "Pierre Lin",
    niveau: "Tous niveaux",
  },
  {
    id: 5,
    titre: "Montage vidéo avec Premiere Pro",
    description:
      "Maîtrisez Premiere Pro pour créer des vidéos professionnelles : montage, effets, colorimétrie.",
    categorie: "creation-contenu",
    duree: 6,
    prix: 95,
    formateur: "Hassan Malik",
    niveau: "Tous niveaux",
  },
  {
    id: 6,
    titre: "Créer son business plan",
    description:
      "Structurez votre projet entrepreneurial avec un business plan solide.",
    categorie: "entrepreneuriat",
    duree: 4,
    prix: 95,
    formateur: "Marc DeLaFontaine",
    niveau: "Débutant",
  },
  {
    id: 7,
    titre: "JavaScript avancé",
    description:
      "Approfondissez JavaScript avec les concepts avancés et les bonnes pratiques.",
    categorie: "developpement",
    duree: 6,
    prix: 110,
    formateur: "Alex Dupond",
    niveau: "Avancé",
  },
  {
    id: 8,
    titre: "UX Research",
    description:
      "Apprenez à mener des recherches utilisateurs pour améliorer vos produits.",
    categorie: "design",
    duree: 4,
    prix: 85,
    formateur: "Emma Rousseau",
    niveau: "Moyen",
  },
  {
    id: 9,
    titre: "Social Media Marketing",
    description:
      "Stratégies et outils pour développer votre présence sur les réseaux sociaux.",
    categorie: "marketing",
    duree: 3,
    prix: 70,
    formateur: "Lucas Girard",
    niveau: "Débutant",
  },
  {
    id: 10,
    titre: "Gestion de projet agile",
    description:
      "Maîtrisez les méthodes agiles (Scrum, Kanban) pour gérer vos projets efficacement.",
    categorie: "soft-skills",
    duree: 4,
    prix: 90,
    formateur: "Camille Blanc",
    niveau: "Moyen",
  },
  {
    id: 11,
    titre: "SQL pour débutants",
    description: "Apprenez à interroger des bases de données avec SQL.",
    categorie: "data",
    duree: 3,
    prix: 60,
    formateur: "Nicolas Roux",
    niveau: "Débutant",
  },
  {
    id: 12,
    titre: "Levée de fonds",
    description:
      "Préparez-vous à lever des fonds pour votre startup : pitch, business plan, investisseurs.",
    categorie: "entrepreneuriat",
    duree: 6,
    prix: 150,
    formateur: "Isabelle Martin",
    niveau: "Avancé",
  },
  {
    id: 13,
    titre: "Vue.js pour débutants",
    description:
      "Initiation à Vue.js : composants, directives et gestion d'état pour créer des applications web modernes.",
    categorie: "developpement",
    duree: 4,
    prix: 85,
    formateur: "David Chen",
    niveau: "Débutant",
  },
  {
    id: 14,
    titre: "Figma avancé",
    description:
      "Maîtrisez les fonctionnalités avancées de Figma : composants, variants, prototypage interactif.",
    categorie: "design",
    duree: 3,
    prix: 70,
    formateur: "Sarah Johnson",
    niveau: "Moyen",
  },
  {
    id: 15,
    titre: "Content Marketing",
    description:
      "Créez du contenu qui convertit : stratégie éditoriale, storytelling et distribution multi-canal.",
    categorie: "marketing",
    duree: 4,
    prix: 80,
    formateur: "Lucas Girard",
    niveau: "Moyen",
  },
  {
    id: 16,
    titre: "Prise de parole en public",
    description:
      "Surmontez votre trac et captez l'attention de votre audience avec des techniques de communication impactantes.",
    categorie: "soft-skills",
    duree: 3,
    prix: 65,
    formateur: "Pierre Leroy",
    niveau: "Tous niveaux",
  },
  {
    id: 17,
    titre: "Rédaction web et SEO",
    description:
      "Écrivez des articles optimisés pour le web : structure, mots-clés, engagement et référencement naturel.",
    categorie: "creation-contenu",
    duree: 4,
    prix: 75,
    formateur: "Sophie Rédactrice",
    niveau: "Moyen",
  },
  {
    id: 18,
    titre: "Stratégie de marque",
    description:
      "Développez une identité de marque forte : positionnement, valeurs et communication cohérente.",
    categorie: "entrepreneuriat",
    duree: 3,
    prix: 90,
    formateur: "Marc Dubois",
    niveau: "Moyen",
  },
  {
    id: 19,
    titre: "Node.js et Express",
    description:
      "Créez des APIs REST avec Node.js et Express : routes, middleware, base de données.",
    categorie: "developpement",
    duree: 6,
    prix: 120,
    formateur: "Alexandre Petit",
    niveau: "Moyen",
  },
  {
    id: 20,
    titre: "Design System",
    description:
      "Concevez et maintenez un design system cohérent pour vos projets : composants, tokens, documentation.",
    categorie: "design",
    duree: 4,
    prix: 95,
    formateur: "Emma Rousseau",
    niveau: "Avancé",
  },
  {
    id: 21,
    titre: "Email Marketing",
    description:
      "Optimisez vos campagnes email : segmentation, automatisation et amélioration du taux d'ouverture.",
    categorie: "marketing",
    duree: 3,
    prix: 60,
    formateur: "Sophie Bernard",
    niveau: "Débutant",
  },
  {
    id: 22,
    titre: "Négociation commerciale",
    description:
      "Maîtrisez l'art de la négociation : préparation, techniques et gestion des objections.",
    categorie: "soft-skills",
    duree: 4,
    prix: 85,
    formateur: "Camille Blanc",
    niveau: "Avancé",
  },
];

export const ateliers = baseAteliers.map((atelier) => ({
  ...atelier,
  apprenants: 20 + atelier.id * 4,
  vues: 120 + atelier.id * 18,
}));

export const categories = {
  developpement: "Developpement",
  design: "Design",
  marketing: "Marketing",
  "soft-skills": "Soft Skills",
  data: "Data",
  "creation-contenu": "Creation de Contenu",
  entrepreneuriat: "Entrepreneuriat",
};
