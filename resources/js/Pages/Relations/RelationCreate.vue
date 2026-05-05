<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center gap-4">
      <a href="/relations"
         class="text-gray-400 hover:text-gray-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold tracking-tight">Crear Relación</h1>
      </div>
    </header>

    <main class="px-6 py-8 max-w-2xl mx-auto space-y-6">

      <!-- Error global -->
      <div v-if="globalError"
           class="bg-red-900/40 border border-red-700 text-red-300 px-4 py-3 rounded-lg text-sm">
        {{ globalError }}
      </div>
      <div v-if="success"
           class="bg-emerald-900/40 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-lg text-sm">
        ¡Relación creada exitosamente!
        <a href="/relations/create" class="underline ml-2">Crear otra</a>
        <a href="/relations" class="underline ml-2">Ver todas</a>
      </div>

      <!-- ── Paso 1: Nodo Origen ─────────────────────────────────────────── -->
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
          <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-xs flex items-center justify-center font-bold">1</span>
          Nodo Origen
        </h2>
        <div class="relative">
          <div class="flex gap-2">
            <select v-model="fromLabel"
                    class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2 py-2 focus:outline-none">
              <option v-for="l in LABELS" :key="l" :value="l">{{ l }}</option>
            </select>
            <div class="relative flex-1">
              <input v-model="fromQuery" type="text" placeholder="Buscar nodo de origen..."
                     @input="searchFrom"
                     @blur="() => setTimeout(() => { fromOpen = false }, 150)"
                     class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
              <div v-if="fromLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
              </div>
            </div>
          </div>
          <div v-if="fromOpen && fromResults.length"
               class="absolute z-20 top-full mt-1 left-0 right-0 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <button v-for="n in fromResults" :key="n.id" type="button"
                    @mousedown.prevent="pickFrom(n)"
                    class="w-full text-left px-4 py-2.5 hover:bg-gray-700 transition-colors flex items-center gap-3 border-b border-gray-700/50 last:border-0">
              <div class="flex gap-1">
                <span v-for="lbl in n.labels" :key="lbl"
                      class="px-1.5 py-0.5 rounded text-xs bg-gray-700 text-gray-300">{{ lbl }}</span>
              </div>
              <span class="text-sm text-gray-200 truncate">{{ nodeLabel(n) }}</span>
              <span class="ml-auto text-xs text-gray-500 font-mono">{{ n.id.slice(-8) }}</span>
            </button>
          </div>
        </div>
        <div v-if="fromNode" class="mt-3 flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-2">
          <div class="flex gap-1 flex-wrap">
            <span v-for="lbl in fromNode.labels" :key="lbl"
                  class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">{{ lbl }}</span>
          </div>
          <span class="text-sm text-gray-200 font-medium">{{ nodeLabel(fromNode) }}</span>
          <button @click="fromNode = null" class="ml-auto text-gray-500 hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <p v-if="errors.from" class="mt-1 text-red-400 text-xs">{{ errors.from }}</p>
      </div>

      <!-- ── Paso 2: Tipo de Relación ───────────────────────────────────── -->
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
          <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-xs flex items-center justify-center font-bold">2</span>
          Tipo de Relación
        </h2>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
          <button v-for="t in relTypes" :key="t"
                  type="button"
                  @click="relType = t"
                  :class="[
                    'py-2 px-3 rounded-lg border text-xs font-mono font-medium transition-all text-center',
                    relType === t
                      ? 'border-emerald-500 bg-emerald-600/20 text-emerald-300'
                      : 'border-gray-700 bg-gray-800 text-gray-400 hover:border-gray-600 hover:text-gray-200'
                  ]">
            {{ t }}
          </button>
        </div>
        <p v-if="errors.relType" class="mt-2 text-red-400 text-xs">{{ errors.relType }}</p>
      </div>

      <!-- ── Paso 3: Nodo Destino ───────────────────────────────────────── -->
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
          <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-xs flex items-center justify-center font-bold">3</span>
          Nodo Destino
        </h2>
        <div class="relative">
          <div class="flex gap-2">
            <select v-model="toLabel"
                    class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2 py-2 focus:outline-none">
              <option v-for="l in LABELS" :key="l" :value="l">{{ l }}</option>
            </select>
            <div class="relative flex-1">
              <input v-model="toQuery" type="text" placeholder="Buscar nodo de destino..."
                     @input="searchTo"
                     @blur="() => setTimeout(() => { toOpen = false }, 150)"
                     class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
              <div v-if="toLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
              </div>
            </div>
          </div>
          <div v-if="toOpen && toResults.length"
               class="absolute z-20 top-full mt-1 left-0 right-0 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden">
            <button v-for="n in toResults" :key="n.id" type="button"
                    @mousedown.prevent="pickTo(n)"
                    class="w-full text-left px-4 py-2.5 hover:bg-gray-700 transition-colors flex items-center gap-3 border-b border-gray-700/50 last:border-0">
              <div class="flex gap-1">
                <span v-for="lbl in n.labels" :key="lbl"
                      class="px-1.5 py-0.5 rounded text-xs bg-gray-700 text-gray-300">{{ lbl }}</span>
              </div>
              <span class="text-sm text-gray-200 truncate">{{ nodeLabel(n) }}</span>
              <span class="ml-auto text-xs text-gray-500 font-mono">{{ n.id.slice(-8) }}</span>
            </button>
          </div>
        </div>
        <div v-if="toNode" class="mt-3 flex items-center gap-2 bg-gray-800 rounded-lg px-3 py-2">
          <div class="flex gap-1 flex-wrap">
            <span v-for="lbl in toNode.labels" :key="lbl"
                  class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">{{ lbl }}</span>
          </div>
          <span class="text-sm text-gray-200 font-medium">{{ nodeLabel(toNode) }}</span>
          <button @click="toNode = null" class="ml-auto text-gray-500 hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <p v-if="errors.to" class="mt-1 text-red-400 text-xs">{{ errors.to }}</p>
      </div>

      <!-- ── Paso 4: Propiedades (mínimo 3) ─────────────────────────────── -->
      <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-300 mb-1 flex items-center gap-2">
          <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-xs flex items-center justify-center font-bold">4</span>
          Propiedades de la Relación
          <span class="text-xs font-normal text-gray-500">(mínimo 3 requeridas)</span>
        </h2>
        <p class="text-xs text-gray-500 mb-4 ml-7">
          {{ filledProps }} / 3 mínimas
          <span v-if="filledProps >= 3" class="text-emerald-400 ml-1">✓</span>
          <span v-else class="text-amber-400 ml-1">— faltan {{ 3 - filledProps }}</span>
        </p>

        <div class="space-y-2">
          <div v-for="(field, i) in propFields" :key="i"
               class="flex gap-2 items-center">
            <input v-model="field.key"
                   type="text"
                   placeholder="clave"
                   :class="[
                     'w-36 flex-shrink-0 bg-gray-800 border text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 transition-colors font-mono',
                     field.key && !validKey(field.key) ? 'border-red-600 focus:ring-red-500' : 'border-gray-700 focus:ring-emerald-500'
                   ]"/>
            <span class="text-gray-600 text-sm font-mono">:</span>
            <input v-model="field.val"
                   type="text"
                   placeholder="valor"
                   class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
            <button v-if="propFields.length > 3"
                    @click="propFields.splice(i, 1)"
                    class="flex-shrink-0 text-gray-500 hover:text-red-400 transition-colors p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <button @click="propFields.push({ key: '', val: '' })"
                class="mt-3 flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-200 transition-colors py-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Añadir campo
        </button>
        <p v-if="errors.props" class="mt-2 text-red-400 text-xs">{{ errors.props }}</p>
      </div>

      <!-- ── Preview Cypher ─────────────────────────────────────────────── -->
      <div v-if="fromNode && relType && toNode" class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-xs font-medium text-gray-500 mb-2">Vista previa Cypher</p>
        <pre class="text-xs font-mono text-emerald-400 bg-gray-950 rounded-lg p-4 overflow-x-auto whitespace-pre-wrap break-all">{{ cypherPreview }}</pre>
      </div>

      <!-- ── Acciones ───────────────────────────────────────────────────── -->
      <div class="flex items-center gap-3 pt-2">
        <a href="/relations"
           class="flex-1 text-center py-3 rounded-xl border border-gray-700 text-gray-400 hover:text-gray-200 hover:border-gray-600 text-sm font-medium transition-all">
          Cancelar
        </a>
        <button @click="submit"
                :disabled="submitting || !canSubmit"
                :class="[
                  'flex-1 py-3 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2',
                  canSubmit && !submitting
                    ? 'bg-emerald-600 hover:bg-emerald-500 text-white'
                    : 'bg-gray-800 text-gray-500 cursor-not-allowed'
                ]">
          <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
          {{ submitting ? 'Creando...' : 'Crear Relación en Neo4j' }}
        </button>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

// ── Props ──────────────────────────────────────────────────────────────────────
defineProps({ relTypes: { type: Array, required: true } })

const LABELS = ['Game', 'User', 'Post', 'Community', 'Award', 'Genre', 'Platform', 'Tag']

// ── From-node search ──────────────────────────────────────────────────────────
const fromQuery   = ref('')
const fromResults = ref([])
const fromLoading = ref(false)
const fromOpen    = ref(false)
const fromLabel   = ref('Game')

const searchFrom = async () => {
  if (!fromQuery.value.trim()) { fromResults.value = []; fromOpen.value = false; return }
  fromLoading.value = true
  try {
    const res = await axios.get('/nodes/list', { params: { label: fromLabel.value, search: fromQuery.value, limit: 8 } })
    fromResults.value = Array.isArray(res.data) ? res.data : []
    fromOpen.value    = fromResults.value.length > 0
  } catch { fromResults.value = [] }
  finally { fromLoading.value = false }
}

const pickFrom = (n) => { fromNode.value = n; fromQuery.value = ''; fromOpen.value = false }

// ── To-node search ────────────────────────────────────────────────────────────
const toQuery   = ref('')
const toResults = ref([])
const toLoading = ref(false)
const toOpen    = ref(false)
const toLabel   = ref('Game')

const searchTo = async () => {
  if (!toQuery.value.trim()) { toResults.value = []; toOpen.value = false; return }
  toLoading.value = true
  try {
    const res = await axios.get('/nodes/list', { params: { label: toLabel.value, search: toQuery.value, limit: 8 } })
    toResults.value = Array.isArray(res.data) ? res.data : []
    toOpen.value    = toResults.value.length > 0
  } catch { toResults.value = [] }
  finally { toLoading.value = false }
}

const pickTo = (n) => { toNode.value = n; toQuery.value = ''; toOpen.value = false }

// ── Main state ─────────────────────────────────────────────────────────────────
const fromNode   = ref(null)
const toNode     = ref(null)
const relType    = ref('')
const propFields = ref([
  { key: '', val: '' },
  { key: '', val: '' },
  { key: '', val: '' },
])
const submitting  = ref(false)
const globalError = ref(null)
const success     = ref(false)
const errors      = ref({})

// ── Computed ──────────────────────────────────────────────────────────────────
const validKey = (k) => /^[a-zA-Z_][a-zA-Z0-9_]*$/.test(k)

const filledProps = computed(() =>
  propFields.value.filter(f => f.key.trim() && f.val.trim()).length
)

const canSubmit = computed(() =>
  fromNode.value && toNode.value && relType.value && filledProps.value >= 3
)

const nodeLabel = (n) =>
  n?.props?.name ?? n?.props?.title ?? n?.props?.username ?? n?.id?.slice(-8) ?? '?'

const cypherPreview = computed(() => {
  if (!fromNode.value || !relType.value || !toNode.value) return ''
  const pairs = propFields.value
    .filter(f => f.key.trim())
    .map(f => `${f.key}: "${f.val}"`)
    .join(',\n  ')
  const from = `(a:${fromNode.value.labels[0]} { /* ${nodeLabel(fromNode.value)} */ })`
  const to   = `(b:${toNode.value.labels[0]} { /* ${nodeLabel(toNode.value)} */ })`
  return `MATCH ${from}, ${to}\nWHERE elementId(a) = "..." AND elementId(b) = "..."\nCREATE (a)-[r:${relType.value} {\n  ${pairs}\n}]->(b)\nRETURN elementId(r) AS id`
})

// ── Submit ────────────────────────────────────────────────────────────────────
const submit = async () => {
  errors.value      = {}
  globalError.value = null
  success.value     = false

  if (!fromNode.value) { errors.value.from = 'Selecciona un nodo de origen'; return }
  if (!relType.value)  { errors.value.relType = 'Selecciona un tipo de relación'; return }
  if (!toNode.value)   { errors.value.to = 'Selecciona un nodo de destino'; return }

  const properties = {}
  for (const f of propFields.value) {
    if (f.key.trim()) {
      if (!validKey(f.key.trim())) {
        errors.value.props = `Clave inválida: "${f.key}" — solo letras, números y guión bajo`
        return
      }
      properties[f.key.trim()] = f.val
    }
  }
  if (Object.keys(properties).length < 3) {
    errors.value.props = 'Se requieren mínimo 3 propiedades (con clave y valor)'
    return
  }

  submitting.value = true
  try {
    await axios.post('/relations', {
      fromId:     fromNode.value.id,
      toId:       toNode.value.id,
      relType:    relType.value,
      properties,
    })
    success.value    = true
    fromNode.value   = null
    toNode.value     = null
    relType.value    = ''
    propFields.value = [{ key: '', val: '' }, { key: '', val: '' }, { key: '', val: '' }]
  } catch (e) {
    globalError.value = e.response?.data?.error ?? 'Error al crear la relación'
  } finally {
    submitting.value = false
  }
}
</script>
