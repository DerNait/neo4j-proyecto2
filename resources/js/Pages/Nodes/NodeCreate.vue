<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center gap-4">
      <a href="/nodes"
         class="text-gray-400 hover:text-gray-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold tracking-tight">Crear Nuevo Nodo</h1>
      </div>
    </header>

    <main class="px-6 py-8 max-w-2xl mx-auto">

      <!-- Error global Neo4j -->
      <div v-if="errors.neo4j"
           class="mb-6 bg-red-900/40 border border-red-700 text-red-300 px-4 py-3 rounded-lg text-sm">
        {{ errors.neo4j }}
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- ── Paso 1: Etiqueta Base ──────────────────────────────────────── -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
          <h2 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-violet-600 text-white text-xs flex items-center justify-center font-bold">1</span>
            Etiqueta Base
          </h2>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button v-for="(_, lbl) in schema" :key="lbl"
                    type="button"
                    @click="selectBase(lbl)"
                    :class="[
                      'py-2.5 px-3 rounded-lg border text-sm font-medium transition-all text-center',
                      form.baseLabel === lbl
                        ? 'border-violet-500 bg-violet-600/20 text-violet-300'
                        : 'border-gray-700 bg-gray-800 text-gray-400 hover:border-gray-600 hover:text-gray-200'
                    ]">
              {{ lbl }}
            </button>
          </div>
          <p v-if="errors.baseLabel" class="mt-2 text-red-400 text-xs">{{ errors.baseLabel }}</p>
        </div>

        <!-- ── Paso 2: Etiquetas Secundarias ─────────────────────────────── -->
        <div v-if="currentSchema?.secondary?.length"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5">
          <h2 class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-violet-600 text-white text-xs flex items-center justify-center font-bold">2</span>
            Etiquetas Secundarias
            <span class="text-xs text-gray-500 font-normal">(opcional — permite crear nodo con 2+ labels)</span>
          </h2>

          <!-- Preview de labels resultantes -->
          <div class="mb-4 flex flex-wrap gap-1.5">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-800 text-violet-200">
              {{ form.baseLabel }}
            </span>
            <span v-for="s in form.secondaryLabels" :key="s"
                  class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-700 text-gray-300">
              {{ s }}
            </span>
            <span class="text-xs text-gray-500 self-center ml-1">
              → CREATE (n:<span class="font-mono text-violet-400">{{ labelsPreview }}</span> {...})
            </span>
          </div>

          <div class="flex flex-wrap gap-3">
            <label v-for="sec in currentSchema.secondary" :key="sec"
                   class="flex items-center gap-2 cursor-pointer group">
              <div class="relative">
                <input type="checkbox"
                       :value="sec"
                       v-model="form.secondaryLabels"
                       class="sr-only peer"/>
                <div class="w-4 h-4 rounded border border-gray-600 peer-checked:bg-violet-600 peer-checked:border-violet-600 transition-colors group-hover:border-gray-400"/>
                <svg class="absolute inset-0 w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <span class="text-sm text-gray-300 group-hover:text-gray-100 transition-colors">{{ sec }}</span>
            </label>
          </div>
          <p v-if="errors.secondaryLabels" class="mt-2 text-red-400 text-xs">{{ errors.secondaryLabels }}</p>
        </div>

        <!-- Sin secondary labels -->
        <div v-else-if="form.baseLabel"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5 opacity-60">
          <h2 class="text-sm font-semibold text-gray-500 flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-gray-700 text-gray-500 text-xs flex items-center justify-center font-bold">2</span>
            Etiquetas Secundarias — <span class="font-normal">{{ form.baseLabel }} no tiene etiquetas secundarias</span>
          </h2>
        </div>

        <!-- ── Paso 3: Propiedades ────────────────────────────────────────── -->
        <div v-if="form.baseLabel"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5">
          <h2 class="text-sm font-semibold text-gray-300 mb-5 flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-violet-600 text-white text-xs flex items-center justify-center font-bold">3</span>
            Propiedades
            <span class="text-xs text-gray-500 font-normal">({{ Object.keys(currentSchema.properties).length }} campos)</span>
          </h2>

          <div class="space-y-4">
            <div v-for="(meta, key) in currentSchema.properties" :key="key">

              <label class="block text-xs font-medium text-gray-400 mb-1.5">
                <span class="font-mono text-violet-400">{{ key }}</span>
                <span class="ml-1 text-gray-500">— {{ meta.label }}</span>
                <span v-if="meta.required" class="text-red-400 ml-0.5">*</span>
                <span class="ml-2 px-1.5 py-0.5 rounded text-xs bg-gray-800 text-gray-500 font-mono">{{ meta.type }}</span>
              </label>

              <!-- boolean → toggle buttons -->
              <div v-if="meta.type === 'boolean'" class="flex gap-2">
                <button v-for="opt in [{ val: 'true', label: 'Sí / true' }, { val: 'false', label: 'No / false' }]"
                        :key="opt.val"
                        type="button"
                        @click="form.properties[key] = opt.val"
                        :class="[
                          'flex-1 py-2 rounded-lg border text-sm font-medium transition-all',
                          form.properties[key] === opt.val
                            ? (opt.val === 'true' ? 'border-emerald-500 bg-emerald-600/20 text-emerald-300'
                                                  : 'border-red-500 bg-red-600/20 text-red-300')
                            : 'border-gray-700 bg-gray-800 text-gray-400 hover:border-gray-600'
                        ]">
                  {{ opt.label }}
                </button>
              </div>

              <!-- list → textarea -->
              <div v-else-if="meta.type === 'list'">
                <input v-model="form.properties[key]"
                       type="text"
                       :placeholder="`valor1, valor2, valor3`"
                       :class="inputClass(key)"/>
                <p class="text-xs text-gray-600 mt-1">Separados por coma</p>
              </div>

              <!-- date -->
              <input v-else-if="meta.type === 'date'"
                     v-model="form.properties[key]"
                     type="date"
                     :class="inputClass(key)"/>

              <!-- integer -->
              <input v-else-if="meta.type === 'integer'"
                     v-model="form.properties[key]"
                     type="number"
                     step="1"
                     :placeholder="`Número entero`"
                     :class="inputClass(key)"/>

              <!-- float -->
              <input v-else-if="meta.type === 'float'"
                     v-model="form.properties[key]"
                     type="number"
                     step="0.1"
                     min="0"
                     max="100"
                     :placeholder="`0.0 – 100.0`"
                     :class="inputClass(key)"/>

              <!-- string (default) -->
              <input v-else
                     v-model="form.properties[key]"
                     type="text"
                     :placeholder="`Ingresa ${meta.label.toLowerCase()}`"
                     :class="inputClass(key)"/>

              <p v-if="errors[`properties.${key}`]" class="mt-1 text-red-400 text-xs">
                {{ errors[`properties.${key}`] }}
              </p>
            </div>
          </div>
        </div>

        <!-- ── Cypher preview ─────────────────────────────────────────────── -->
        <div v-if="form.baseLabel" class="bg-gray-900 border border-gray-800 rounded-xl p-5">
          <p class="text-xs font-medium text-gray-500 mb-2">Vista previa de la consulta Cypher</p>
          <pre class="text-xs font-mono text-emerald-400 bg-gray-950 rounded-lg p-4 overflow-x-auto whitespace-pre-wrap break-all">{{ cypherPreview }}</pre>
        </div>

        <!-- ── Acciones ───────────────────────────────────────────────────── -->
        <div class="flex items-center gap-3 pt-2">
          <a href="/nodes"
             class="flex-1 text-center py-3 rounded-xl border border-gray-700 text-gray-400 hover:text-gray-200 hover:border-gray-600 text-sm font-medium transition-all">
            Cancelar
          </a>
          <button type="submit"
                  :disabled="!form.baseLabel || submitting"
                  :class="[
                    'flex-1 py-3 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2',
                    form.baseLabel && !submitting
                      ? 'bg-violet-600 hover:bg-violet-500 text-white'
                      : 'bg-gray-800 text-gray-500 cursor-not-allowed'
                  ]">
            <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 7a3 3 0 013-3h10a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7z"/>
            </svg>
            {{ submitting ? 'Creando...' : 'Crear Nodo en Neo4j' }}
          </button>
        </div>

      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
  schema: { type: Object, required: true },
})

// ── Form state ───────────────────────────────────────────────────────────────
const form = useForm({
  baseLabel:       '',
  secondaryLabels: [],
  properties:      {},
})

const submitting = ref(false)

// Errors expuestos (Inertia los pone en form.errors)
const errors = computed(() => form.errors)

// ── Schema actual según base label ───────────────────────────────────────────
const currentSchema = computed(() => props.schema[form.baseLabel] ?? null)

// ── Al cambiar base label, resetear secondaries y props ──────────────────────
const selectBase = (lbl) => {
  form.baseLabel       = lbl
  form.secondaryLabels = []
  const propKeys = Object.keys(props.schema[lbl]?.properties ?? {})
  const empty    = {}
  propKeys.forEach(k => { empty[k] = '' })
  form.properties = empty
}

// ── Preview labels string ─────────────────────────────────────────────────
const labelsPreview = computed(() => {
  const all = [form.baseLabel, ...form.secondaryLabels].filter(Boolean)
  return all.join(':')
})

// ── Cypher preview ────────────────────────────────────────────────────────
const cypherPreview = computed(() => {
  if (!form.baseLabel) return ''
  const schema = currentSchema.value?.properties ?? {}
  const pairs  = Object.entries(schema)
    .filter(([k]) => form.properties[k] !== '')
    .map(([k, meta]) => {
      const val = form.properties[k]
      if (meta.type === 'integer') return `${k}: ${parseInt(val) || 0}`
      if (meta.type === 'float')   return `${k}: ${parseFloat(val) || 0}`
      if (meta.type === 'boolean') return `${k}: ${val === 'true'}`
      if (meta.type === 'list') {
        const items = (val || '').split(',').map(s => `"${s.trim()}"`).join(', ')
        return `${k}: [${items}]`
      }
      return `${k}: "${val}"`
    })
    .join(',\n  ')

  return `CREATE (n:${labelsPreview.value} {\n  ${pairs}\n})\nRETURN elementId(n) AS id`
})

// ── Input class helper ────────────────────────────────────────────────────────
const inputClass = (key) => [
  'w-full bg-gray-800 border rounded-lg px-3 py-2.5 text-sm text-gray-100 placeholder-gray-600',
  'focus:outline-none focus:ring-2 focus:ring-violet-500 transition-colors',
  errors.value[`properties.${key}`]
    ? 'border-red-500'
    : 'border-gray-700 hover:border-gray-600',
]

// ── Submit via Inertia ────────────────────────────────────────────────────────
const submit = () => {
  submitting.value = true
  form.post('/nodes', {
    onFinish: () => { submitting.value = false },
  })
}
</script>
