export default defineNuxtPlugin(() => {
  const $api = $fetch.create({
    // Adaptez l'URL de base à l'emplacement de votre api.php
    baseURL: 'https://management.hoggari.com/',
    // Vous pouvez ajouter ici la logique pour inclure le token d'authentification
    // onRequest({ options }) {
    //   const token = useCookie('auth_token').value;
    //   if (token) {
    //     options.headers = { ...options.headers, Authorization: `Bearer ${token}` };
    //   }
    // }
  });

  return {
    provide: {
      api: $api
    }
  }
});