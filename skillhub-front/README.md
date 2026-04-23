# React + Vite

Ce template fournit une configuration minimale pour faire fonctionner React avec Vite, le rechargement a chaud (HMR) et quelques regles ESLint.

Actuellement, deux plugins officiels sont disponibles :

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react) utilise [Babel](https://babeljs.io/) (ou [oxc](https://oxc.rs) avec [rolldown-vite](https://vite.dev/guide/rolldown)) pour Fast Refresh
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react-swc) utilise [SWC](https://swc.rs/) pour Fast Refresh

## React Compiler

Le React Compiler n'est pas active dans ce template en raison de son impact sur les performances en developpement et en build. Pour l'ajouter, consultez [cette documentation](https://react.dev/learn/react-compiler/installation).

## Etendre la configuration ESLint

Si vous developpez une application de production, il est recommande d'utiliser TypeScript avec des regles de lint sensibles aux types. Consultez le [template TS](https://github.com/vitejs/vite/tree/main/packages/create-vite/template-react-ts) pour savoir comment integrer TypeScript et [`typescript-eslint`](https://typescript-eslint.io) dans votre projet.
