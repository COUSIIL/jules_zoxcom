<template>
    <div class="login-container">
      <div class="login-box">
        <h2>Connexion</h2>
        <form @submit.prevent="handleLogin">
          <input 
            type="text" 
            v-model="username" 
            placeholder="Nom d'utilisateur" 
            required 
          />
          <input 
            type="password" 
            v-model="password" 
            placeholder="Mot de passe" 
            required 
          />
          <button type="submit">Se connecter</button>
          <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
    import { ref } from 'vue'

    const username = ref('')
    const password = ref('')
    const errorMessage = ref('')

    const handleLogin = async () => {
    errorMessage.value = ''

    try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=connexion', {
        method: 'POST',
        body: JSON.stringify({
            username: username.value,
            password: password.value
        })
        })

        if (!response.ok) {
            errorMessage.value = 'Error in response';
            return;
        }

        const data = await response.json();

        if (data.success) {
        // ✅ Enregistrer l'utilisateur dans le state global
        localStorage.setItem('auth', JSON.stringify(data.data));
        errorMessage.value = data.message;


        window.location.href = '/' // Rediriger vers la page de connexion après déconnexion

        } else {
            console.log('data.message: ', data.message);
            errorMessage.value = data.message;
        }
    } catch (error) {
        errorMessage.value = 'Erreur lors de la connexion';
    }
    }


  </script>
  
  <style scoped>
  .login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  
  .login-box {
    background: var(--color-whitly);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 360px;
  }
  .dark .login-box {
    background: var(--color-darkly);
  }
  
  h2 {
    margin-bottom: 1.5rem;
    font-size: 24px;
  }
  
  input {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    font-size: 16px;
    border-radius: 8px;
    border: 1px solid var(--color-whity);
  }
  .dark input {
    border: 1px solid var(--color-darky);
  }
  
  input:focus {
    border-color: #6a5acd;
    outline: none;
  }
  
  button {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    font-size: 16px;
    border-radius: 8px;
    background-color: #6a5acd;
    color: white;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  button:hover {
    background-color: #5a4abc;
  }
  
  .error {
    color: red;
    margin-top: 10px;
  }
  </style>
  