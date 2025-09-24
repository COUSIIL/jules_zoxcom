import { readBody } from 'h3'

export default defineEventHandler(async (event) => {
  try {
    const body = await readBody(event)
    const message = body.message || 'Bonjour.'

    // Clé API Gemini depuis .env
    const apiKey = process.env.API_GEMINI
    if (!apiKey) {
      return { success: false, error: 'Clé API manquante.' }
    }

    const response = await $fetch('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Goog-Api-Key': apiKey
      },
      body: {
        contents: [
          { parts: [{ text: message }] }
        ]
      }
    })

    // Extraire le texte généré
    const text = response.candidates?.[0]?.content?.parts?.[0]?.text || 'Réponse indisponible.'

    return { success: true, reply: text }

  } catch (err) {
    return { success: false, error: err.message }
  }
})
