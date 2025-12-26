import { ref, onMounted } from 'vue'

export const useMeta = () => {

  // --- 🔐 TOKEN & STATE ---
  const userToken = ref('')
  const pages = ref([])
  const adAccounts = ref([])
  const campaigns = ref([])
  const adsets = ref([])
  const ads = ref([])
  const insights = ref(null)

  // Charger le token côté client uniquement
  onMounted(() => {
    if (typeof localStorage !== 'undefined') {
      userToken.value = localStorage.getItem('meta_user_token') || ''
    }
  })

  // -------------------------------------------------------------------
  // 🔵 1) LOGIN FACEBOOK (redirection OAuth)
  // -------------------------------------------------------------------
  const login = () => {
    const appId = "1206555098186313"
    const redirect = encodeURIComponent(window.location.origin + "/meta/callback")

    const permissions = [
      "pages_show_list",
      "pages_read_engagement",
      "ads_read",
      "ads_management",
      "business_management",
      "pages_manage_metadata",
      "pages_manage_ads"
    ]

    const url =
      `https://www.facebook.com/v24.0/dialog/oauth?client_id=${appId}` +
      `&redirect_uri=${redirect}` +
      `&scope=${permissions.join(',')}`

    window.location.href = url
  }

  // -------------------------------------------------------------------
  // 🔵 2) Sauvegarder le token après login
  // -------------------------------------------------------------------
  const saveToken = (token) => {
    userToken.value = token

    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('meta_user_token', token)
    }
  }

  // -------------------------------------------------------------------
  // 🔵 3) Récupérer les pages admin
  // -------------------------------------------------------------------
  const fetchPages = async () => {
    if (!userToken.value) return []

    const res = await $fetch(`https://graph.facebook.com/v24.0/me/accounts`, {
      query: { access_token: userToken.value }
    })

    pages.value = res.data || []
    return pages.value
  }

  // -------------------------------------------------------------------
  // 🔵 4) Récupérer les comptes publicitaires
  // -------------------------------------------------------------------
  const fetchAdAccounts = async () => {
    if (!userToken.value) return []

    const res = await $fetch(`https://graph.facebook.com/v24.0/me/adaccounts`, {
      query: { access_token: userToken.value }
    })

    adAccounts.value = res.data || []
    return adAccounts.value
  }

  // -------------------------------------------------------------------
  // 🔵 5) Récupérer les campagnes
  // -------------------------------------------------------------------
  const fetchCampaigns = async (adAccountId) => {
    const res = await $fetch(`https://graph.facebook.com/v24.0/${adAccountId}/campaigns`, {
      query: {
        fields: "id,name,status,effective_status",
        access_token: userToken.value
      }
    })

    campaigns.value = res.data || []
    return campaigns.value
  }

  // -------------------------------------------------------------------
  // 🔵 6) Récupérer les adsets
  // -------------------------------------------------------------------
  const fetchAdsets = async (campaignId) => {
    const res = await $fetch(`https://graph.facebook.com/v24.0/${campaignId}/adsets`, {
      query: {
        fields: "id,name,bid_strategy,optimization_goal,daily_budget",
        access_token: userToken.value
      }
    })

    adsets.value = res.data || []
    return adsets.value
  }

  // -------------------------------------------------------------------
  // 🔵 7) Récupérer les Ads
  // -------------------------------------------------------------------
  const fetchAds = async (adsetId) => {
    const res = await $fetch(`https://graph.facebook.com/v24.0/${adsetId}/ads`, {
      query: {
        fields: "id,name,status,effective_status",
        access_token: userToken.value
      }
    })

    ads.value = res.data || []
    return ads.value
  }

  // -------------------------------------------------------------------
  // 🔵 8) Récupérer les insights d'une publicité
  // -------------------------------------------------------------------
  const fetchInsights = async (adId) => {
    const res = await $fetch(`https://graph.facebook.com/v24.0/${adId}/insights`, {
      query: {
        fields:
          "impressions,reach,clicks,ctr,cpc,spend,unique_clicks,cost_per_inline_link_click",
        access_token: userToken.value
      }
    })

    insights.value = res.data?.[0] || null
    return insights.value
  }

  // -------------------------------------------------------------------
  // 🔴 9) Logout
  // -------------------------------------------------------------------
  const logout = () => {
    userToken.value = ''
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem('meta_user_token')
    }
  }

  return {
    userToken,
    pages,
    adAccounts,
    campaigns,
    adsets,
    ads,
    insights,

    login,
    saveToken,
    logout,

    fetchPages,
    fetchAdAccounts,
    fetchCampaigns,
    fetchAdsets,
    fetchAds,
    fetchInsights,
  }
}
