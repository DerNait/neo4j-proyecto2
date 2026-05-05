<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- Header -->
    <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
        </div>
        <h1 class="text-lg font-semibold tracking-tight">Gestión de Relaciones</h1>
        <span class="text-xs text-gray-500 font-mono hidden sm:inline">Neo4j Gaming Network</span>
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
        <button @click="openCsvModal"
                class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          Cargar Relaciones
        </button>
        <a href="/relations/create"
           class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Crear Relación
        </a>
      </div>
    </header>

    <main class="px-6 py-6 space-y-6 max-w-7xl mx-auto" :class="{ 'pb-36': selectionCount > 0 }">

      <!-- ── Filtros ─────────────────────────────────────────────────────── -->
      <section class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex items-center gap-2 flex-1">
          <label class="text-xs text-gray-400 whitespace-nowrap font-medium">Tipo</label>
          <select v-model="selectedType"
                  class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">Todos</option>
            <option v-for="t in relTypes" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>
        <div class="flex items-center gap-2 flex-1">
          <label class="text-xs text-gray-400 whitespace-nowrap font-medium">Buscar nodo</label>
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
            </svg>
            <input v-model="search" type="text" placeholder="Filtrar por nombre del nodo..."
                   @keyup.enter="loadRelations"
                   class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500"/>
          </div>
        </div>
        <button @click="loadRelations"
                class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
          Buscar
        </button>
      </section>

      <!-- ── Tabla de Relaciones ────────────────────────────────────────── -->
      <section class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-300">
              {{ relations.length }} relación{{ relations.length !== 1 ? 'es' : '' }} encontrada{{ relations.length !== 1 ? 's' : '' }}
            </span>
            <span v-if="selectionCount > 0"
                  class="text-xs bg-emerald-900/60 text-emerald-300 border border-emerald-700 px-2 py-0.5 rounded-full">
              {{ selectionCount }} seleccionada{{ selectionCount !== 1 ? 's' : '' }}
            </span>
          </div>
          <button v-if="selectionCount > 0" @click="deselectAll"
                  class="text-xs text-gray-400 hover:text-gray-200 transition-colors">
            Deseleccionar todo
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="py-16 flex justify-center">
          <svg class="w-6 h-6 text-emerald-500 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        <!-- Empty -->
        <div v-else-if="!relations.length" class="py-16 text-center text-gray-500 text-sm">
          No se encontraron relaciones. <a href="/relations/create" class="text-emerald-400 hover:underline">Crear una.</a>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-800 text-left">
                <th class="px-4 py-3 w-10">
                  <input type="checkbox"
                         :checked="selectionCount === relations.length && relations.length > 0"
                         :indeterminate="selectionCount > 0 && selectionCount < relations.length"
                         @change="selectionCount === relations.length ? deselectAll() : selectAll()"
                         class="accent-emerald-500 w-4 h-4 cursor-pointer"/>
                </th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Desde</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Hacia</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider"># Props</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
              <tr v-for="rel in relations" :key="rel.id"
                  class="hover:bg-gray-800/50 transition-colors"
                  :class="{ 'bg-emerald-900/10': selectedRelIds.has(rel.id) }">
                <td class="px-4 py-3" @click.stop>
                  <input type="checkbox"
                         :checked="selectedRelIds.has(rel.id)"
                         @change="toggleSelect(rel.id)"
                         class="accent-emerald-500 w-4 h-4 cursor-pointer"/>
                </td>
                <td class="px-4 py-3 cursor-pointer" @click="openDetail(rel)">
                  <span class="inline-flex items-center gap-1.5">
                    <span :class="['px-1.5 py-0.5 rounded text-xs font-medium', chipColor(chipLabel(rel.from))]">{{ chipLabel(rel.from) }}</span>
                    <span class="text-gray-300 text-xs truncate max-w-[100px]">{{ chipName(rel.from) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3 cursor-pointer" @click="openDetail(rel)">
                  <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-emerald-900/50 text-emerald-300 border border-emerald-800">
                    {{ rel.type }}
                  </span>
                </td>
                <td class="px-4 py-3 cursor-pointer" @click="openDetail(rel)">
                  <span class="inline-flex items-center gap-1.5">
                    <span :class="['px-1.5 py-0.5 rounded text-xs font-medium', chipColor(chipLabel(rel.to))]">{{ chipLabel(rel.to) }}</span>
                    <span class="text-gray-300 text-xs truncate max-w-[100px]">{{ chipName(rel.to) }}</span>
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-400 cursor-pointer" @click="openDetail(rel)">
                  {{ Object.keys(rel.props).length }}
                </td>
                <td class="px-4 py-3 text-right" @click.stop>
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openPropPanel(rel)"
                            title="Editar propiedades"
                            class="text-gray-400 hover:text-emerald-300 transition-colors p-1 rounded">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button @click="openDetail(rel)"
                            class="text-emerald-400 hover:text-emerald-300 text-xs font-medium transition-colors">
                      Ver →
                    </button>
                    <button @click="deleteRelation(rel)"
                            title="Eliminar relación"
                            class="text-gray-500 hover:text-red-400 transition-colors p-1 rounded">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
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
           class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 flex items-center justify-center p-4"
           @click.self="showModal = false">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-lg shadow-2xl">
          <div class="flex items-start justify-between px-6 pt-5 pb-4 border-b border-gray-800">
            <div class="flex-1 min-w-0">
              <!-- Cypher-style header -->
              <div class="flex items-center gap-2 flex-wrap mb-2">
                <span class="inline-flex items-center gap-1.5">
                  <span :class="['px-1.5 py-0.5 rounded text-xs font-medium', chipColor(chipLabel(selectedRel?.from))]">{{ chipLabel(selectedRel?.from) }}</span>
                  <span class="text-gray-300 text-xs truncate max-w-[120px]">{{ chipName(selectedRel?.from) }}</span>
                </span>
                <span class="text-gray-500 font-mono text-xs">─[</span>
                <span class="text-emerald-400 font-mono text-xs font-bold">{{ selectedRel?.type }}</span>
                <span class="text-gray-500 font-mono text-xs">]→</span>
                <span class="inline-flex items-center gap-1.5">
                  <span :class="['px-1.5 py-0.5 rounded text-xs font-medium', chipColor(chipLabel(selectedRel?.to))]">{{ chipLabel(selectedRel?.to) }}</span>
                  <span class="text-gray-300 text-xs truncate max-w-[120px]">{{ chipName(selectedRel?.to) }}</span>
                </span>
              </div>
              <p class="text-xs text-gray-500 font-mono truncate">{{ selectedRel?.id }}</p>
            </div>
            <button @click="showModal = false"
                    class="text-gray-500 hover:text-gray-300 transition-colors ml-4 mt-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-6 py-4 max-h-[50vh] overflow-y-auto space-y-2">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-3">Propiedades de la relación</p>
            <div v-for="(val, key) in selectedRel?.props" :key="key"
                 class="flex gap-3 py-2 border-b border-gray-800/60 last:border-0">
              <span class="text-xs font-mono text-emerald-400 w-36 flex-shrink-0 pt-0.5">{{ key }}</span>
              <span class="text-sm text-gray-200 break-all">
                <template v-if="Array.isArray(val)">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="(item, i) in val" :key="i"
                          class="bg-gray-800 text-gray-300 px-2 py-0.5 rounded text-xs">{{ item }}</span>
                  </div>
                </template>
                <template v-else-if="typeof val === 'boolean'">
                  <span :class="val ? 'text-emerald-400' : 'text-red-400'">{{ val ? 'true' : 'false' }}</span>
                </template>
                <template v-else>{{ val }}</template>
              </span>
            </div>
            <p v-if="!Object.keys(selectedRel?.props ?? {}).length"
               class="text-center text-gray-500 text-sm py-4">Sin propiedades.</p>
          </div>
          <div class="px-6 pb-5 pt-3 flex gap-2">
            <button @click="openPropPanel(selectedRel); showModal = false"
                    class="flex-1 bg-emerald-700 hover:bg-emerald-600 text-white text-sm py-2.5 rounded-lg transition-colors font-medium">
              Editar Propiedades
            </button>
            <button @click="showModal = false"
                    class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm py-2.5 rounded-lg transition-colors">
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── Slide-over: Editar Propiedades de Relación ─────────────────────── -->
    <Transition name="slide-right">
      <div v-if="propPanel" class="fixed inset-0 z-50 flex">
        <div class="flex-1 bg-black/50" @click="closePropPanel"/>
        <div class="w-full max-w-md bg-gray-900 border-l border-gray-800 flex flex-col shadow-2xl">

          <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between flex-shrink-0">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-white mb-1">Editar Propiedades</p>
              <div class="flex items-center gap-1.5 flex-wrap text-xs font-mono">
                <span class="text-gray-400">{{ nodeMainProp(propPanel.from) }}</span>
                <span class="text-gray-600">─[</span>
                <span class="text-emerald-400 font-bold">{{ propPanel.type }}</span>
                <span class="text-gray-600">]→</span>
                <span class="text-gray-400">{{ nodeMainProp(propPanel.to) }}</span>
              </div>
            </div>
            <button @click="closePropPanel"
                    class="text-gray-500 hover:text-gray-300 transition-colors ml-3 flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-5 space-y-3">
            <div v-if="propError"
                 class="bg-red-900/40 border border-red-700 text-red-300 px-3 py-2 rounded-lg text-xs">
              {{ propError }}
            </div>
            <div v-if="propSuccess"
                 class="bg-emerald-900/40 border border-emerald-700 text-emerald-300 px-3 py-2 rounded-lg text-xs">
              {{ propSuccess }}
            </div>

            <!-- Editable props -->
            <div v-for="(val, key) in editPropsMap" :key="key"
                 class="flex gap-2 items-center">
              <span class="text-xs font-mono text-emerald-400 w-28 flex-shrink-0 truncate">{{ key }}</span>
              <input v-model="editPropsMap[key]"
                     type="text"
                     class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-500"/>
              <button @click="removeEditProp(key)"
                      class="text-gray-500 hover:text-red-400 transition-colors flex-shrink-0 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Add new prop -->
            <div class="pt-2 border-t border-gray-800">
              <p class="text-xs text-gray-500 mb-2 font-medium">Añadir propiedad</p>
              <div class="flex gap-2">
                <input v-model="newPropKey" type="text" placeholder="clave"
                       class="w-28 flex-shrink-0 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono"/>
                <input v-model="newPropVal" type="text" placeholder="valor"
                       @keyup.enter="addEditProp"
                       class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-1 focus:ring-emerald-500"/>
                <button @click="addEditProp"
                        class="flex-shrink-0 bg-emerald-700 hover:bg-emerald-600 text-white text-xs px-3 py-2 rounded-lg transition-colors">
                  +
                </button>
              </div>
            </div>
          </div>

          <div class="px-5 py-4 border-t border-gray-800 flex gap-2 flex-shrink-0">
            <button @click="savePropEdit"
                    :disabled="propSaving"
                    class="flex-1 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-sm py-2.5 rounded-lg transition-colors font-medium">
              {{ propSaving ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
            <button @click="closePropPanel"
                    class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm py-2.5 rounded-lg transition-colors">
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── Modal CSV Import Relaciones ──────────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="csvModal"
           class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
           @click.self="closeCsvModal">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-lg shadow-2xl">

          <!-- Header -->
          <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-800">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
              </svg>
              <h2 class="text-sm font-semibold text-white">Cargar Relaciones desde CSV</h2>
            </div>
            <button @click="closeCsvModal" class="text-gray-500 hover:text-gray-300 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="px-6 py-5 space-y-4">

            <!-- RelType selector -->
            <div>
              <label class="text-xs text-gray-400 font-medium block mb-1.5">Tipo de relación</label>
              <select v-model="csvRelType"
                      class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option v-for="t in relTypes" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>

            <!-- Info de nodos from/to y columnas esperadas -->
            <div v-if="csvSchemaInfo" class="bg-gray-800/60 rounded-lg p-3 space-y-2">
              <div class="flex items-center gap-2 text-xs">
                <span class="px-2 py-0.5 rounded bg-emerald-900/60 text-emerald-300 font-medium">{{ csvSchemaInfo.from }}</span>
                <span class="text-gray-500 font-mono">─[{{ csvRelType }}]→</span>
                <span class="px-2 py-0.5 rounded bg-emerald-900/60 text-emerald-300 font-medium">{{ csvSchemaInfo.to }}</span>
              </div>

              <p class="text-xs font-medium text-gray-400 mt-1">Columnas del CSV:</p>
              <div class="flex flex-wrap gap-1.5">
                <span class="text-xs font-mono bg-violet-900/50 text-violet-300 px-2 py-0.5 rounded border border-violet-800">
                  fromValue <span class="text-violet-500">({{ csvSchemaInfo.from }}.{{ NODE_SEARCH_LOCAL[csvSchemaInfo.from] }})</span>
                </span>
                <span class="text-xs font-mono bg-violet-900/50 text-violet-300 px-2 py-0.5 rounded border border-violet-800">
                  toValue <span class="text-violet-500">({{ csvSchemaInfo.to }}.{{ NODE_SEARCH_LOCAL[csvSchemaInfo.to] }})</span>
                </span>
                <span v-for="(meta, col) in csvSchemaInfo.properties" :key="col"
                      class="text-xs font-mono bg-gray-700 text-gray-300 px-2 py-0.5 rounded">
                  {{ col }}
                </span>
              </div>

              <p class="text-xs text-gray-500">
                <span class="font-mono text-gray-400">fromValue</span> y <span class="font-mono text-gray-400">toValue</span>
                deben coincidir exactamente con el campo identificador del nodo.
                Booleanos: <span class="font-mono text-gray-400">true</span> / <span class="font-mono text-gray-400">false</span>.
                Fechas: <span class="font-mono text-gray-400">YYYY-MM-DD</span>.
              </p>
            </div>

            <!-- File input -->
            <div>
              <label class="text-xs text-gray-400 font-medium block mb-1.5">Archivo CSV</label>
              <input ref="csvFileInput" type="file" accept=".csv,text/csv"
                     @change="onCsvFileChange"
                     class="block w-full text-sm text-gray-400
                            file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                            file:text-xs file:font-medium file:bg-emerald-700 file:text-white
                            file:hover:bg-emerald-600 file:cursor-pointer file:transition-colors
                            cursor-pointer"/>
              <p v-if="csvFileName" class="text-xs text-gray-500 mt-1">{{ csvFileName }}</p>
            </div>

            <!-- Result -->
            <div v-if="csvResult" class="rounded-lg p-3 space-y-1"
                 :class="csvResult.errors.length ? 'bg-yellow-900/30 border border-yellow-700/50' : 'bg-emerald-900/30 border border-emerald-700/50'">
              <p class="text-sm font-medium" :class="csvResult.errors.length ? 'text-yellow-300' : 'text-emerald-300'">
                ✓ {{ csvResult.created }} relación{{ csvResult.created !== 1 ? 'es' : '' }} creada{{ csvResult.created !== 1 ? 's' : '' }}
              </p>
              <ul v-if="csvResult.errors.length" class="space-y-0.5 max-h-32 overflow-y-auto">
                <li v-for="(err, i) in csvResult.errors" :key="i"
                    class="text-xs text-yellow-400 font-mono">{{ err }}</li>
              </ul>
            </div>

            <div v-if="csvError" class="bg-red-900/40 border border-red-700 rounded-lg px-3 py-2">
              <p class="text-xs text-red-300">{{ csvError }}</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="px-6 pb-5 flex gap-2">
            <button @click="submitCsv"
                    :disabled="!csvFile || csvUploading"
                    class="flex-1 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
              {{ csvUploading ? 'Importando...' : 'Importar CSV' }}
            </button>
            <button @click="closeCsvModal"
                    class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm py-2.5 rounded-lg transition-colors">
              Cerrar
            </button>
          </div>

        </div>
      </div>
    </Transition>

    <!-- ── Bulk Action Bar ────────────────────────────────────────────────── -->
    <Transition name="slide-up">
      <div v-if="selectionCount > 0"
           class="fixed bottom-0 inset-x-0 z-30 bg-gray-900 border-t border-gray-700 px-6 py-4">
        <div class="max-w-7xl mx-auto">

          <!-- Bulk: Add/Update -->
          <div v-if="bulkMode === 'add'" class="space-y-3">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-semibold text-white">
                Agregar / Actualizar Propiedades en {{ selectionCount }} relación{{ selectionCount !== 1 ? 'es' : '' }}
              </p>
              <button @click="bulkMode = null; bulkFields = []; bulkError = null"
                      class="text-gray-400 hover:text-gray-200 text-xs">Cancelar</button>
            </div>
            <div v-if="bulkError" class="text-red-400 text-xs bg-red-900/30 px-3 py-2 rounded-lg">{{ bulkError }}</div>
            <div class="flex flex-wrap gap-2">
              <div v-for="(f, i) in bulkFields" :key="i" class="flex gap-1.5 items-center">
                <input v-model="f.key" placeholder="clave" type="text"
                       class="w-28 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono"/>
                <span class="text-gray-600 text-xs">:</span>
                <input v-model="f.val" placeholder="valor" type="text"
                       class="w-36 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500"/>
                <button @click="bulkFields.splice(i, 1)" class="text-gray-500 hover:text-red-400 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <button @click="bulkFields.push({ key: '', val: '' })"
                      class="text-xs bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 px-3 py-1.5 rounded-lg transition-colors">
                + Campo
              </button>
            </div>
            <button @click="applyBulkUpdate" :disabled="bulkSaving"
                    class="bg-emerald-700 hover:bg-emerald-600 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
              {{ bulkSaving ? 'Aplicando...' : `Aplicar a ${selectionCount} relaciones` }}
            </button>
          </div>

          <!-- Bulk: Remove -->
          <div v-else-if="bulkMode === 'remove'" class="space-y-3">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-semibold text-white">
                Eliminar Propiedades de {{ selectionCount }} relación{{ selectionCount !== 1 ? 'es' : '' }}
              </p>
              <button @click="bulkMode = null; bulkKeys = []; bulkError = null"
                      class="text-gray-400 hover:text-gray-200 text-xs">Cancelar</button>
            </div>
            <div v-if="bulkError" class="text-red-400 text-xs bg-red-900/30 px-3 py-2 rounded-lg">{{ bulkError }}</div>
            <div class="flex flex-wrap gap-2 items-center">
              <div v-for="(_, i) in bulkKeys" :key="i" class="flex gap-1.5 items-center">
                <input v-model="bulkKeys[i]" placeholder="clave a eliminar" type="text"
                       class="w-40 bg-gray-800 border border-red-800/60 text-gray-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-red-500 font-mono"/>
                <button @click="bulkKeys.splice(i, 1)" class="text-gray-500 hover:text-red-400 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
              <button @click="bulkKeys.push('')"
                      class="text-xs bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 px-3 py-1.5 rounded-lg transition-colors">
                + Clave
              </button>
            </div>
            <button @click="applyBulkRemove" :disabled="bulkSaving"
                    class="bg-red-700 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
              {{ bulkSaving ? 'Eliminando...' : `Eliminar de ${selectionCount} relaciones` }}
            </button>
          </div>

          <!-- Default bar -->
          <div v-else class="flex items-center gap-4 flex-wrap">
            <span class="text-sm text-gray-300 font-medium">
              {{ selectionCount }} relación{{ selectionCount !== 1 ? 'es' : '' }} seleccionada{{ selectionCount !== 1 ? 's' : '' }}
            </span>
            <div class="flex gap-2 flex-wrap">
              <button @click="bulkMode = 'add'; bulkFields = [{ key: '', val: '' }]; bulkError = null"
                      class="text-xs bg-emerald-800 hover:bg-emerald-700 text-emerald-200 border border-emerald-700 px-3 py-1.5 rounded-lg transition-colors font-medium">
                + Agregar / Actualizar Propiedades
              </button>
              <button @click="bulkMode = 'remove'; bulkKeys = ['']; bulkError = null"
                      class="text-xs bg-red-900/60 hover:bg-red-800/60 text-red-300 border border-red-800 px-3 py-1.5 rounded-lg transition-colors font-medium">
                ✕ Eliminar Propiedades
              </button>
              <button @click="bulkDeleteRelations"
                      class="text-xs bg-red-800 hover:bg-red-700 text-red-100 border border-red-700 px-3 py-1.5 rounded-lg transition-colors font-medium">
                🗑 Eliminar Relaciones
              </button>
              <button @click="deselectAll"
                      class="text-xs text-gray-400 hover:text-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-800 transition-colors">
                Deseleccionar
              </button>
            </div>
          </div>

        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

// ── Node chip helpers ─────────────────────────────────────────────────────────
const LABEL_COLORS = {
  User: 'bg-violet-900 text-violet-300', Post: 'bg-sky-900 text-sky-300',
  Community: 'bg-emerald-900 text-emerald-300', Game: 'bg-orange-900 text-orange-300',
  Award: 'bg-yellow-900 text-yellow-300', Genre: 'bg-pink-900 text-pink-300',
  Platform: 'bg-cyan-900 text-cyan-300', Tag: 'bg-gray-700 text-gray-300',
}
const chipColor = (lbl) => LABEL_COLORS[lbl] ?? 'bg-gray-700 text-gray-300'
const chipName  = (n)   => n?.props?.name ?? n?.props?.title ?? n?.props?.username ?? '?'
const chipLabel = (n)   => n?.labels?.[0] ?? '?'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
  relTypes:   { type: Array,  required: true },
  relSchema:  { type: Object, default: () => ({}) },
  nodeSearch: { type: Object, default: () => ({}) },
})

const NODE_SEARCH_LOCAL = {
  User: 'username', Post: 'title', Community: 'name',
  Game: 'title', Award: 'name', Genre: 'name', Platform: 'name', Tag: 'name',
}

const REL_SCHEMA_LOCAL = {
  IS_OF:         { from: 'Game',      to: 'Genre',     properties: { assignedAt: 'date', isPrimaryGenre: 'boolean', matchPercentage: 'float' } },
  RELEASED_ON:   { from: 'Game',      to: 'Platform',  properties: { releasedAt: 'date', isPorted: 'boolean', targetFps: 'integer' } },
  RELATED_TO:    { from: 'Community', to: 'Game',      properties: { establishedAt: 'date', officialLink: 'string', priorityLevel: 'integer' } },
  POSTED_IN:     { from: 'Post',      to: 'Community', properties: { isPinned: 'boolean', allowsComments: 'boolean', offTopic: 'boolean' } },
  WROTE:         { from: 'User',      to: 'Post',      properties: { clientApp: 'string', isEdited: 'boolean', location: 'string' } },
  FOLLOWS:       { from: 'User',      to: 'User',      properties: { sinceAt: 'date', notificationsOn: 'boolean', closeFriend: 'boolean' } },
  MEMBER_OF:     { from: 'User',      to: 'Community', properties: { joinedAt: 'date', role: 'string', isActive: 'boolean' } },
  UPVOTED:       { from: 'User',      to: 'Post',      properties: { upvotedAt: 'date', voteWeight: 'integer', isSuperVote: 'boolean' } },
  COMMENTED_ON:  { from: 'User',      to: 'Post',      properties: { comment: 'string', commentedAt: 'date', isReply: 'boolean' } },
  LIKES:         { from: 'User',      to: 'Genre',     properties: { likedAt: 'date', intensityLevel: 'integer', isPublic: 'boolean' } },
  CROSSPOSTED_TO:{ from: 'Post',      to: 'Community', properties: { crosspostedAt: 'date', karmaGained: 'integer', reason: 'string' } },
  RECEIVED_AWARD:{ from: 'Post',      to: 'Award',     properties: { grantedAt: 'date', quantity: 'integer', givenByUser: 'boolean' } },
  TAGGED_WITH:   { from: 'Post',      to: 'Tag',       properties: { addedAt: 'date', confidenceScore: 'float', isModeratorApplied: 'boolean' } },
  ABOUT:         { from: 'Post',      to: 'Game',      properties: { containsSpoilers: 'boolean', isReview: 'boolean', firstTime: 'boolean' } },
}

// ── State ─────────────────────────────────────────────────────────────────────
const selectedType = ref('')
const search       = ref('')
const relations    = ref([])
const loading      = ref(false)
const showModal    = ref(false)
const selectedRel  = ref(null)

const selectedRelIds = ref(new Set())
const selectionCount = computed(() => selectedRelIds.value.size)

const propPanel    = ref(null)
const editPropsMap = ref({})
const newPropKey   = ref('')
const newPropVal   = ref('')
const propSaving   = ref(false)
const propError    = ref(null)
const propSuccess  = ref(null)
const _origProps   = ref({})

const bulkMode   = ref(null)
const bulkFields = ref([])
const bulkKeys   = ref([])
const bulkSaving = ref(false)
const bulkError  = ref(null)

// CSV Import
const csvModal     = ref(false)
const csvRelType   = ref(props.relTypes[0] ?? '')
const csvFile      = ref(null)
const csvFileName  = ref('')
const csvUploading = ref(false)
const csvResult    = ref(null)
const csvError     = ref(null)
const csvFileInput = ref(null)

const csvSchemaInfo = computed(() => REL_SCHEMA_LOCAL[csvRelType.value] ?? null)

const openCsvModal = () => {
  csvRelType.value  = props.relTypes[0] ?? ''
  csvFile.value     = null
  csvFileName.value = ''
  csvResult.value   = null
  csvError.value    = null
  csvModal.value    = true
}
const closeCsvModal = () => { csvModal.value = false }

const onCsvFileChange = (e) => {
  const f = e.target.files?.[0]
  if (!f) return
  csvFile.value     = f
  csvFileName.value = f.name
  csvResult.value   = null
  csvError.value    = null
}

const submitCsv = async () => {
  if (!csvFile.value) return
  csvUploading.value = true
  csvResult.value    = null
  csvError.value     = null
  const form = new FormData()
  form.append('csv', csvFile.value)
  form.append('relType', csvRelType.value)
  try {
    const res = await axios.post('/relations/import-csv', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    csvResult.value = res.data
    if (res.data.created > 0) await loadRelations()
  } catch (e) {
    csvError.value = e.response?.data?.error ?? 'Error al importar CSV'
  } finally {
    csvUploading.value = false
  }
}

// ── Fetch ─────────────────────────────────────────────────────────────────────
const loadRelations = async () => {
  loading.value = true
  deselectAll()
  try {
    const res = await axios.get('/relations/list', {
      params: { type: selectedType.value, search: search.value },
    })
    relations.value = Array.isArray(res.data) ? res.data : []
  } catch { relations.value = [] }
  finally { loading.value = false }
}

// ── Selection ─────────────────────────────────────────────────────────────────
const toggleSelect = (id) => {
  const s = new Set(selectedRelIds.value)
  s.has(id) ? s.delete(id) : s.add(id)
  selectedRelIds.value = s
}
const selectAll   = () => { selectedRelIds.value = new Set(relations.value.map(r => r.id)) }
const deselectAll = () => { selectedRelIds.value = new Set() }

// ── Detail modal ──────────────────────────────────────────────────────────────
const openDetail = (rel) => { selectedRel.value = rel; showModal.value = true }

// ── Prop panel ────────────────────────────────────────────────────────────────
const nodeMainProp = (n) =>
  n?.props?.name ?? n?.props?.title ?? n?.props?.username ?? n?.id?.slice(-8) ?? '?'

const displayVal = (v) => {
  if (v === null || v === undefined) return ''
  if (typeof v === 'boolean') return v ? 'true' : 'false'
  if (Array.isArray(v)) return v.join(', ')
  if (typeof v === 'object' && 'days' in v) return String(v.days)
  return String(v)
}

const openPropPanel = (rel) => {
  propPanel.value   = rel
  propError.value   = null
  propSuccess.value = null
  _origProps.value  = { ...rel.props }
  const mapped = {}
  for (const [k, v] of Object.entries(rel.props)) mapped[k] = displayVal(v)
  editPropsMap.value = mapped
  newPropKey.value   = ''
  newPropVal.value   = ''
}

const closePropPanel = () => { propPanel.value = null; propError.value = null }

const addEditProp = () => {
  const k = newPropKey.value.trim()
  if (!k) return
  editPropsMap.value = { ...editPropsMap.value, [k]: newPropVal.value }
  newPropKey.value   = ''
  newPropVal.value   = ''
}

const removeEditProp = (key) => {
  const copy = { ...editPropsMap.value }
  delete copy[key]
  editPropsMap.value = copy
}

const savePropEdit = async () => {
  propSaving.value  = true
  propError.value   = null
  propSuccess.value = null
  try {
    const updates  = { ...editPropsMap.value }
    const removals = Object.keys(_origProps.value).filter(k => !(k in editPropsMap.value))

    if (Object.keys(updates).length > 0) {
      await axios.post('/relations/properties/update', {
        id: propPanel.value.id, properties: updates,
      })
    }
    if (removals.length > 0) {
      await axios.post('/relations/properties/remove', {
        id: propPanel.value.id, keys: removals,
      })
    }

    const newProps = { ...editPropsMap.value }
    const idx = relations.value.findIndex(r => r.id === propPanel.value.id)
    if (idx !== -1) relations.value[idx] = { ...relations.value[idx], props: newProps }
    propPanel.value   = { ...propPanel.value, props: newProps }
    _origProps.value  = { ...newProps }
    propSuccess.value = '¡Propiedades guardadas exitosamente!'
  } catch (e) {
    propError.value = e.response?.data?.error ?? 'Error al guardar'
  } finally {
    propSaving.value = false
  }
}

// ── Bulk ──────────────────────────────────────────────────────────────────────
const applyBulkUpdate = async () => {
  const properties = {}
  bulkFields.value.forEach(f => { if (f.key.trim()) properties[f.key.trim()] = f.val })
  if (!Object.keys(properties).length) { bulkError.value = 'Agrega al menos una propiedad'; return }
  bulkSaving.value = true; bulkError.value = null
  try {
    await axios.post('/relations/properties/bulk-update', {
      ids: [...selectedRelIds.value], properties,
    })
    await loadRelations()
    bulkMode.value = null; bulkFields.value = []
  } catch (e) {
    bulkError.value = e.response?.data?.error ?? 'Error al aplicar'
  } finally { bulkSaving.value = false }
}

const applyBulkRemove = async () => {
  const keys = bulkKeys.value.map(k => k.trim()).filter(Boolean)
  if (!keys.length) { bulkError.value = 'Agrega al menos una clave'; return }
  bulkSaving.value = true; bulkError.value = null
  try {
    await axios.post('/relations/properties/bulk-remove', {
      ids: [...selectedRelIds.value], keys,
    })
    await loadRelations()
    bulkMode.value = null; bulkKeys.value = []
  } catch (e) {
    bulkError.value = e.response?.data?.error ?? 'Error al eliminar'
  } finally { bulkSaving.value = false }
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteRelation = async (rel) => {
  const label = `${chipName(rel.from)} -[${rel.type}]-> ${chipName(rel.to)}`
  if (!window.confirm(`¿Eliminar relación "${label}"?`)) return
  try {
    await axios.post('/relations/delete', { id: rel.id })
    await loadRelations()
  } catch (e) {
    alert(e.response?.data?.error ?? 'Error al eliminar relación')
  }
}

const bulkDeleteRelations = async () => {
  const count = selectionCount.value
  if (!window.confirm(`¿Eliminar ${count} relación${count !== 1 ? 'es' : ''}?`)) return
  try {
    await axios.post('/relations/bulk-delete', { ids: [...selectedRelIds.value] })
    await loadRelations()
  } catch (e) {
    alert(e.response?.data?.error ?? 'Error al eliminar relaciones')
  }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
watch(selectedType, () => { search.value = ''; loadRelations() })
onMounted(loadRelations)
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.25s ease; }
.slide-right-enter-from, .slide-right-leave-to       { transform: translateX(100%); }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.2s ease; }
.slide-up-enter-from, .slide-up-leave-to       { transform: translateY(100%); }
</style>
