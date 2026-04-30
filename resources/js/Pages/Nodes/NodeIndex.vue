<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 7a3 3 0 013-3h10a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7z"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold tracking-tight">Gestión de Nodos</h1>
        <span class="text-xs text-gray-500 font-mono">Neo4j Gaming Network</span>
      </div>
      <a :href="route('nodes.create')"
         class="flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Crear Nodo
      </a>
    </header>

    <main class="px-6 py-6 space-y-6 max-w-7xl mx-auto">

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success"
           class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-lg text-sm">
        {{ $page.props.flash.success }}
      </div>

      <!-- ── Aggregate Cards ────────────────────────────────────────────── -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div v-if="aggregatesLoading" v-for="i in 3" :key="i"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5 animate-pulse h-24"/>

        <div v-for="stat in aggregates" :key="stat.label"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5 flex items-center gap-4">
          <!-- Icon -->
          <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
               :class="iconBg(stat.icon)">
            <!-- users -->
            <svg v-if="stat.icon === 'users'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m7-4a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <!-- star -->
            <svg v-else-if="stat.icon === 'star'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.518 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.05 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.518-4.674z"/>
            </svg>
            <!-- document -->
            <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">
              {{ stat.format === 'float' ? Number(stat.value).toFixed(1) : stat.value.toLocaleString() }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ stat.label }}</p>
          </div>
        </div>
      </section>

      <!-- ── Filtros ─────────────────────────────────────────────────────── -->
      <section class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex items-center gap-2 flex-1">
          <label class="text-xs text-gray-400 whitespace-nowrap font-medium">Tipo de nodo</label>
          <select v-model="selectedLabel"
                  class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500">
            <option v-for="(_, lbl) in schema" :key="lbl" :value="lbl">{{ lbl }}</option>
          </select>
        </div>

        <div class="flex items-center gap-2 flex-1">
          <label class="text-xs text-gray-400 whitespace-nowrap font-medium">Buscar</label>
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
            </svg>
            <input v-model="search" type="text" placeholder="Filtrar por nombre / título..."
                   class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500"/>
          </div>
        </div>

        <button @click="loadNodes"
                class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
          Buscar
        </button>
      </section>

      <!-- ── Tabla de Nodos ──────────────────────────────────────────────── -->
      <section class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
          <span class="text-sm font-medium text-gray-300">
            {{ nodes.length }} nodo{{ nodes.length !== 1 ? 's' : '' }} encontrado{{ nodes.length !== 1 ? 's' : '' }}
          </span>
          <span class="text-xs text-gray-500 font-mono">:{{ selectedLabel }}</span>
        </div>

        <!-- Loading -->
        <div v-if="listLoading" class="py-16 flex justify-center">
          <svg class="w-6 h-6 text-violet-500 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        <!-- Empty -->
        <div v-else-if="!nodes.length" class="py-16 text-center text-gray-500 text-sm">
          No se encontraron nodos para esta búsqueda.
        </div>

        <!-- Grid -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-800 text-left">
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Labels</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Propiedad principal</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider"># Props</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Element ID</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Detalle</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
              <tr v-for="node in nodes" :key="node.id"
                  class="hover:bg-gray-800/50 transition-colors cursor-pointer"
                  @click="openDetail(node)">
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="lbl in node.labels" :key="lbl"
                          class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="labelColor(lbl)">
                      {{ lbl }}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-3 text-gray-200 font-medium max-w-xs truncate">
                  {{ mainProp(node) }}
                </td>
                <td class="px-4 py-3 text-gray-400">
                  {{ Object.keys(node.props).length }}
                </td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs max-w-[180px] truncate">
                  {{ node.id }}
                </td>
                <td class="px-4 py-3 text-right">
                  <button class="text-violet-400 hover:text-violet-300 text-xs font-medium transition-colors">
                    Ver →
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>

    <!-- ── Modal Detalle ──────────────────────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="showModal"
           class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
           @click.self="showModal = false">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-lg shadow-2xl">

          <!-- Modal Header -->
          <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-gray-800">
            <div>
              <div class="flex flex-wrap gap-1.5 mb-2">
                <span v-for="lbl in selectedNode?.labels" :key="lbl"
                      class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="labelColor(lbl)">
                  {{ lbl }}
                </span>
              </div>
              <p class="text-xs text-gray-500 font-mono">{{ selectedNode?.id }}</p>
            </div>
            <button @click="showModal = false"
                    class="text-gray-500 hover:text-gray-300 transition-colors ml-4 mt-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Properties -->
          <div class="px-6 py-4 space-y-2 max-h-[60vh] overflow-y-auto">
            <div v-for="(val, key) in selectedNode?.props" :key="key"
                 class="flex gap-3 py-2 border-b border-gray-800/60 last:border-0">
              <span class="text-xs font-mono text-violet-400 w-36 flex-shrink-0 pt-0.5">{{ key }}</span>
              <span class="text-sm text-gray-200 break-all">
                <template v-if="Array.isArray(val)">
                  <span v-if="val.length === 0" class="text-gray-500 italic">[ vacío ]</span>
                  <div v-else class="flex flex-wrap gap-1">
                    <span v-for="(item, i) in val" :key="i"
                          class="bg-gray-800 text-gray-300 px-2 py-0.5 rounded text-xs">{{ item }}</span>
                  </div>
                </template>
                <template v-else-if="typeof val === 'boolean'">
                  <span :class="val ? 'text-emerald-400' : 'text-red-400'">
                    {{ val ? 'true' : 'false' }}
                  </span>
                </template>
                <template v-else>{{ val }}</template>
              </span>
            </div>
            <p v-if="!Object.keys(selectedNode?.props ?? {}).length"
               class="text-center text-gray-500 text-sm py-4">
              Este nodo no tiene propiedades.
            </p>
          </div>

          <div class="px-6 pb-5 pt-3">
            <button @click="showModal = false"
                    class="w-full bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm py-2.5 rounded-lg transition-colors">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

// Inertia props
const props = defineProps({
  schema: { type: Object, required: true },
  flash:  { type: Object, default: () => ({}) },
})

// ── State ────────────────────────────────────────────────────────────────────
const selectedLabel    = ref('Game')
const search           = ref('')
const nodes            = ref([])
const aggregates       = ref([])
const listLoading      = ref(false)
const aggregatesLoading = ref(true)
const showModal        = ref(false)
const selectedNode     = ref(null)

// ── Helpers de ruta (usa Ziggy si está disponible, sino hardcoded) ────────────
function route(name, params = {}) {
  const map = {
    'nodes.create': '/nodes/create',
    'nodes.index':  '/nodes',
    'nodes.list':   '/nodes/list',
    'nodes.aggregates': '/nodes/aggregates',
  }
  return map[name] ?? '/' + name
}

// ── Fetch aggregates ─────────────────────────────────────────────────────────
const loadAggregates = async () => {
  aggregatesLoading.value = true
  try {
    const res = await axios.get('/nodes/aggregates')
    aggregates.value = res.data
  } catch {
    aggregates.value = []
  } finally {
    aggregatesLoading.value = false
  }
}

// ── Fetch node list ──────────────────────────────────────────────────────────
const loadNodes = async () => {
  listLoading.value = true
  try {
    const res = await axios.get('/nodes/list', {
      params: { label: selectedLabel.value, search: search.value },
    })
    nodes.value = Array.isArray(res.data) ? res.data : []
  } catch {
    nodes.value = []
  } finally {
    listLoading.value = false
  }
}

// ── Open detail modal ────────────────────────────────────────────────────────
const openDetail = (node) => {
  selectedNode.value = node
  showModal.value    = true
}

// ── Prop principal a mostrar en tabla ────────────────────────────────────────
const mainProp = (node) => {
  const schema = props.schema[selectedLabel.value]
  if (!schema) return '—'
  const field = schema.searchField
  return node.props[field] ?? node.props[Object.keys(node.props)[0]] ?? '—'
}

// ── Estilos dinámicos ────────────────────────────────────────────────────────
const iconBg = (icon) => ({
  'bg-violet-600': icon === 'users',
  'bg-amber-600':  icon === 'star',
  'bg-sky-600':    icon === 'document',
})

const LABEL_COLORS = {
  User: 'bg-violet-900 text-violet-300',
  Post: 'bg-sky-900 text-sky-300',
  Community: 'bg-emerald-900 text-emerald-300',
  Game: 'bg-orange-900 text-orange-300',
  Award: 'bg-yellow-900 text-yellow-300',
  Genre: 'bg-pink-900 text-pink-300',
  Platform: 'bg-cyan-900 text-cyan-300',
  Tag: 'bg-gray-700 text-gray-300',
}
const labelColor = (lbl) =>
  LABEL_COLORS[lbl] ?? 'bg-gray-700 text-gray-300'

// ── Watchers y lifecycle ─────────────────────────────────────────────────────
watch(selectedLabel, () => {
  search.value = ''
  loadNodes()
})

onMounted(() => {
  loadAggregates()
  loadNodes()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to    { opacity: 0; }
</style>
