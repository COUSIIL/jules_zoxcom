import fr from '~/locales/fr.json'
import en from '~/locales/en.json'
import ar from '~/locales/ar.json'

const messages = { fr, en, ar }

export const useLang = () => {
  const locale = useState('locale', () => 'en')

  const t = (key) => {
    return messages[locale.value]?.[key] || key
  }

  const setLocale = (newLocale) => {
    if (messages[newLocale]) {
      locale.value = newLocale
    }
  }

  return { t, locale, setLocale }
}
