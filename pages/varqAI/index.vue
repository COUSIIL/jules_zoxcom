<template>
  <div class="hub-wrap">
    <h1>Varq AI Studio</h1>
    <p class="subtitle">Sélectionnez un outil d'intelligence artificielle</p>

    <div class="hub-grid">

      <!-- Video -->
      <NuxtLink to="/varqAI/video" class="hub-card">
        <div class="icon">🎥</div>
        <h2>Image to Video</h2>
        <p>Transformez vos images en vidéos promotionnelles animées.</p>
      </NuxtLink>

      <!-- Image -->
      <NuxtLink to="/varqAI/image" class="hub-card">
        <div class="icon">🎨</div>
        <h2>Générateur d'Image</h2>
        <p>Créez des images uniques à partir de descriptions textuelles.</p>
      </NuxtLink>

      <!-- Music -->
      <NuxtLink to="/varqAI/music" class="hub-card">
        <div class="icon">🎵</div>
        <h2>Générateur de Musique</h2>
        <p>Composez des morceaux de musique avec Suno AI.</p>
      </NuxtLink>

      <!-- Web -->
      <NuxtLink to="/varqAI/web" class="hub-card">
        <div class="icon">🌐</div>
        <h2>Image to HTML</h2>
        <p>Convertissez une maquette image en landing page HTML/CSS/JS.</p>
      </NuxtLink>

    </div>
  </div>
</template>

<style scoped>
.hub-wrap {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: system-ui, -apple-system, sans-serif;
  text-align: center;
}

h1 {
  font-size: 2.5rem;
  margin-bottom: 10px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.subtitle {
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 40px;
}

.hub-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.hub-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 32px 24px;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 16px;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0,0,0,0.02);
}

.hub-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 20px rgba(0,0,0,0.08);
  border-color: #8b5cf6;
}

.icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.hub-card h2 {
  font-size: 1.25rem;
  margin: 0 0 10px;
  font-weight: 600;
}

.hub-card p {
  font-size: 0.95rem;
  color: #666;
  line-height: 1.5;
  margin: 0;
}

/* Dark mode support if applicable */
:global(.dark) .hub-card {
  background: #1e1e1e;
  border-color: #333;
  color: #fff;
}
:global(.dark) .hub-card p {
  color: #aaa;
}
:global(.dark) .hub-card:hover {
  border-color: #8b5cf6;
}
</style>
