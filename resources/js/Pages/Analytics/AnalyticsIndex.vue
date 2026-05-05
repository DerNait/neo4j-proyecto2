<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold tracking-tight">Analytics</h1>
        <span class="text-xs text-gray-500 font-mono hidden sm:inline">Data Science · Neo4j Gaming Network</span>
      </div>
      <div class="flex items-center gap-2">
        <a href="/nodes"
           class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-200 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 7a3 3 0 013-3h10a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7z"/>
          </svg>
          Nodos
        </a>
        <a href="/relations"
           class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-200 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          Relaciones
        </a>
      </div>
    </header>

    <main class="px-6 py-6 space-y-8 max-w-7xl mx-auto">

      <!-- ── Intro ────────────────────────────────────────────────────────── -->
      <section class="bg-indigo-950/40 border border-indigo-800/50 rounded-xl p-5">
        <h2 class="text-base font-semibold text-indigo-300 mb-1">Algoritmos de Data Science</h2>
        <p class="text-sm text-gray-400">
          Análisis sobre el grafo de Gaming Network usando tres algoritmos:
          <span class="text-indigo-300 font-medium">Filtrado Colaborativo</span> para recomendaciones,
          <span class="text-yellow-300 font-medium">Centralidad de Grado Ponderada</span> para rankings de influencia, y
          <span class="text-emerald-300 font-medium">Scoring de Tendencias</span> para juegos populares.
        </p>
      </section>

      <!-- ── Sección 1: Filtrado Colaborativo ─────────────────────────────── -->
      <section class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-7 h-7 rounded-lg bg-indigo-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-white">Filtrado Colaborativo — Recomendaciones de Juegos</h2>
            <p class="text-xs text-gray-500">Usuarios similares (misma comunidad) que valoraron juegos que el target no conoce · Score = usuarios × 3 + posts × 1 + metacritic × 0.1</p>
          </div>
        </div>

        <!-- Input -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex flex-col sm:flex-row gap-3">
          <div class="flex items-center gap-2 flex-1">
            <label class="text-xs text-gray-400 whitespace-nowrap font-medium">Username</label>
            <input v-model="recUsername" type="text" placeholder="Ej: user_abc123"
                   @keyup.enter="loadRecommendations"
                   class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
          </div>
          <button @click="loadRecommendations" :disabled="recLoading"
                  class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors whitespace-nowrap">
            {{ recLoading ? 'Buscando...' : 'Recomendar' }}
          </button>
        </div>

        <!-- Error -->
        <div v-if="recError"
             class="bg-red-900/40 border border-red-700 text-red-300 px-4 py-3 rounded-lg text-sm">
          {{ recError }}
        </div>

        <!-- Tabla resultados -->
        <div v-if="recommendations.length" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-800">
            <span class="text-sm font-medium text-gray-300">
              {{ recommendations.length }} juego{{ recommendations.length !== 1 ? 's' : '' }} recomendado{{ recommendations.length !== 1 ? 's' : '' }}
              <span class="text-xs text-gray-500 ml-2">para "{{ recUsernameDisplay }}"</span>
            </span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-800 text-left">
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Juego</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Desarrollador</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Metacritic</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Usuarios similares</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Posts</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Score</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-800">
                <tr v-for="(row, i) in recommendations" :key="i"
                    class="hover:bg-gray-800/50 transition-colors">
                  <td class="px-4 py-3 text-gray-500 text-xs">{{ i + 1 }}</td>
                  <td class="px-4 py-3 text-gray-100 font-medium">{{ row.title }}</td>
                  <td class="px-4 py-3 text-gray-400">{{ row.developer }}</td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-xs font-bold"
                          :class="metacriticColor(row.metacriticScore)">
                      {{ row.metacriticScore ?? '—' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center text-indigo-300 font-mono text-xs">{{ row.userOverlap }}</td>
                  <td class="px-4 py-3 text-center text-gray-400 font-mono text-xs">{{ row.postCount }}</td>
                  <td class="px-4 py-3 text-right">
                    <span class="text-indigo-400 font-bold font-mono text-sm">{{ row.score }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else-if="!recLoading && recUsernameDisplay"
             class="text-center text-gray-500 text-sm py-6">
          Sin resultados. Prueba con otro username o verifica que el usuario esté en la base de datos.
        </div>
      </section>

      <!-- ── Sección 2: Influencers (Centralidad) ──────────────────────── -->
      <section class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-7 h-7 rounded-lg bg-yellow-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-white">Centralidad de Grado Ponderada — Usuarios Más Influyentes</h2>
            <p class="text-xs text-gray-500">Degree centrality ponderada · Score = seguidores × 2 + posts × 1.5 + karma × 0.01</p>
          </div>
        </div>

        <div v-if="infLoading" class="bg-gray-900 border border-gray-800 rounded-xl py-16 flex justify-center">
          <svg class="w-6 h-6 text-yellow-500 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>
        <div v-else-if="infError"
             class="bg-red-900/40 border border-red-700 text-red-300 px-4 py-3 rounded-lg text-sm">
          {{ infError }}
        </div>
        <div v-else-if="influencers.length" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-800 text-left">
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider w-12">#</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Username</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Seguidores</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Posts</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Karma</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Influence Score</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-800">
                <tr v-for="(row, i) in influencers" :key="i"
                    class="hover:bg-gray-800/50 transition-colors">
                  <td class="px-4 py-3 text-center">
                    <span class="text-sm font-bold"
                          :class="rankBadge(i)">
                      {{ i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : i + 1 }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-100 font-medium font-mono text-xs">{{ row.username }}</td>
                  <td class="px-4 py-3 text-center text-gray-300 font-mono text-xs">{{ row.followers.toLocaleString() }}</td>
                  <td class="px-4 py-3 text-center text-gray-300 font-mono text-xs">{{ row.posts }}</td>
                  <td class="px-4 py-3 text-center text-gray-300 font-mono text-xs">{{ row.karma.toLocaleString() }}</td>
                  <td class="px-4 py-3 text-right">
                    <span class="text-yellow-400 font-bold font-mono text-sm">{{ row.influenceScore }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ── Sección 3: Trending Games ─────────────────────────────────── -->
      <section class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-7 h-7 rounded-lg bg-emerald-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-semibold text-white">Trending Games — Juegos en Tendencia</h2>
            <p class="text-xs text-gray-500">Score de actividad · Score = posts × 5 + upvotes × 0.05 + votantes × 1</p>
          </div>
        </div>

        <div v-if="trendLoading" class="bg-gray-900 border border-gray-800 rounded-xl py-16 flex justify-center">
          <svg class="w-6 h-6 text-emerald-500 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>
        <div v-else-if="trendError"
             class="bg-red-900/40 border border-red-700 text-red-300 px-4 py-3 rounded-lg text-sm">
          {{ trendError }}
        </div>
        <div v-else-if="trending.length" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-800 text-left">
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider w-12">#</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Juego</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Desarrollador</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Metacritic</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Posts</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Upvotes</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-center">Comentaristas</th>
                  <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Trend Score</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-800">
                <tr v-for="(row, i) in trending" :key="i"
                    class="hover:bg-gray-800/50 transition-colors">
                  <td class="px-4 py-3 text-gray-500 text-xs text-center font-bold">{{ i + 1 }}</td>
                  <td class="px-4 py-3 text-gray-100 font-medium">{{ row.title }}</td>
                  <td class="px-4 py-3 text-gray-400">{{ row.developer }}</td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-xs font-bold"
                          :class="metacriticColor(row.metacriticScore)">
                      {{ row.metacriticScore ?? '—' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center text-gray-300 font-mono text-xs">{{ row.postCount }}</td>
                  <td class="px-4 py-3 text-center text-gray-300 font-mono text-xs">{{ (row.totalUpvotes ?? 0).toLocaleString() }}</td>
                  <td class="px-4 py-3 text-center text-gray-300 font-mono text-xs">{{ row.totalCommenters }}</td>
                  <td class="px-4 py-3 text-right">
                    <span class="text-emerald-400 font-bold font-mono text-sm">{{ row.trendScore }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// ── Sección 1: Filtrado Colaborativo ─────────────────────────────────────────
const recUsername        = ref('')
const recUsernameDisplay = ref('')
const recommendations    = ref([])
const recLoading         = ref(false)
const recError           = ref(null)

const loadRecommendations = async () => {
  if (!recUsername.value.trim()) return
  recLoading.value         = true
  recError.value           = null
  recommendations.value    = []
  recUsernameDisplay.value = recUsername.value.trim()
  try {
    const res = await axios.get('/analytics/recommend', {
      params: { username: recUsername.value.trim() },
    })
    if (res.data.error) {
      recError.value = res.data.error
    } else {
      recommendations.value = Array.isArray(res.data) ? res.data : []
    }
  } catch (e) {
    recError.value = e.response?.data?.error ?? 'Error al cargar recomendaciones'
  } finally {
    recLoading.value = false
  }
}

// ── Sección 2: Influencers ────────────────────────────────────────────────────
const influencers = ref([])
const infLoading  = ref(true)
const infError    = ref(null)

const loadInfluencers = async () => {
  infLoading.value = true
  infError.value   = null
  try {
    const res = await axios.get('/analytics/influencers')
    influencers.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    infError.value = e.response?.data?.error ?? 'Error al cargar influencers'
  } finally {
    infLoading.value = false
  }
}

// ── Sección 3: Trending ───────────────────────────────────────────────────────
const trending     = ref([])
const trendLoading = ref(true)
const trendError   = ref(null)

const loadTrending = async () => {
  trendLoading.value = true
  trendError.value   = null
  try {
    const res = await axios.get('/analytics/trending')
    trending.value = Array.isArray(res.data) ? res.data : []
  } catch (e) {
    trendError.value = e.response?.data?.error ?? 'Error al cargar tendencias'
  } finally {
    trendLoading.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const metacriticColor = (score) => {
  const s = parseFloat(score)
  if (isNaN(s)) return 'bg-gray-700 text-gray-400'
  if (s >= 85)  return 'bg-emerald-900 text-emerald-300'
  if (s >= 70)  return 'bg-yellow-900 text-yellow-300'
  return 'bg-red-900 text-red-300'
}

const rankBadge = (i) => ({
  'text-yellow-400': i === 0,
  'text-gray-300':   i === 1,
  'text-orange-400': i === 2,
  'text-gray-500':   i > 2,
})

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  loadInfluencers()
  loadTrending()
})
</script>
