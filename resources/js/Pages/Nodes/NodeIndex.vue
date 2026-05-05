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
        <span class="text-xs text-gray-500 font-mono hidden sm:inline">Neo4j Gaming Network</span>
      </div>
      <div class="flex items-center gap-2">
        <a href="/relations"
           class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-200 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          Relaciones
        </a>
        <button @click="openCsvModal"
                class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          Cargar Nodos
        </button>
        <a href="/nodes/create"
           class="flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Crear Nodo
        </a>
      </div>
    </header>

    <main class="px-6 py-6 space-y-6 max-w-7xl mx-auto" :class="{ 'pb-32': selectionCount > 0 }">

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="bg-emerald-900/50 border border-emerald-700 text-emerald-300 px-4 py-3 rounded-lg text-sm">
        {{ $page.props.flash.success }}
      </div>

      <!-- ── Aggregate Cards ────────────────────────────────────────────── -->
      <section class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-if="aggregatesLoading" v-for="i in 7" :key="i"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5 animate-pulse h-24"/>
        <div v-for="stat in aggregates" :key="stat.label"
             class="bg-gray-900 border border-gray-800 rounded-xl p-5 flex items-center gap-4">
          <div class="w-11 h-11 rounded-lg flex items-center justify-center flex-shrink-0"
               :class="iconBg(stat.icon)">
            <svg v-if="stat.icon === 'users'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m7-4a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg v-else-if="stat.icon === 'star'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.518 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.05 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.518-4.674z"/>
            </svg>
            <svg v-else-if="stat.icon === 'trophy'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <svg v-else-if="stat.icon === 'arrow-down'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg v-else-if="stat.icon === 'chart'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <svg v-else-if="stat.icon === 'tag'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
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
                   @keyup.enter="searchNodes"
                   class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500"/>
          </div>
        </div>
        <button @click="searchNodes"
                class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors whitespace-nowrap">
          Buscar
        </button>
      </section>

      <!-- ── Tabla de Nodos ──────────────────────────────────────────────── -->
      <section class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-300">
              {{ nodes.length }} nodo{{ nodes.length !== 1 ? 's' : '' }} encontrado{{ nodes.length !== 1 ? 's' : '' }}
            </span>
            <span v-if="selectionCount > 0"
                  class="text-xs bg-violet-900/60 text-violet-300 border border-violet-700 px-2 py-0.5 rounded-full">
              {{ selectionCount }} seleccionado{{ selectionCount !== 1 ? 's' : '' }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <button v-if="selectionCount > 0" @click="deselectAll"
                    class="text-xs text-gray-400 hover:text-gray-200 transition-colors">
              Deseleccionar todo
            </button>
            <span class="text-xs text-gray-500 font-mono">:{{ selectedLabel }}</span>
          </div>
        </div>

        <div v-if="listLoading" class="py-16 flex justify-center">
          <svg class="w-6 h-6 text-violet-500 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        <div v-else-if="!nodes.length" class="py-16 text-center text-gray-500 text-sm">
          No se encontraron nodos para esta búsqueda.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-800 text-left">
                <th class="px-4 py-3 w-10">
                  <input type="checkbox"
                         :checked="selectionCount === nodes.length && nodes.length > 0"
                         :indeterminate="selectionCount > 0 && selectionCount < nodes.length"
                         @change="selectionCount === nodes.length ? deselectAll() : selectAll()"
                         class="accent-violet-500 w-4 h-4 cursor-pointer"/>
                </th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Labels</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Propiedad principal</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider"># Props</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Element ID</th>
                <th class="px-4 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider text-right">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
              <tr v-for="node in nodes" :key="node.id"
                  class="hover:bg-gray-800/50 transition-colors"
                  :class="{ 'bg-violet-900/10': selectedNodeIds.has(node.id) }">
                <td class="px-4 py-3" @click.stop>
                  <input type="checkbox"
                         :checked="selectedNodeIds.has(node.id)"
                         @change="toggleNodeSelect(node.id)"
                         class="accent-violet-500 w-4 h-4 cursor-pointer"/>
                </td>
                <td class="px-4 py-3 cursor-pointer" @click="openDetail(node)">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="lbl in node.labels" :key="lbl"
                          class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="labelColor(lbl)">{{ lbl }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-gray-200 font-medium max-w-xs truncate cursor-pointer"
                    @click="openDetail(node)">
                  {{ mainProp(node) }}
                </td>
                <td class="px-4 py-3 text-gray-400 cursor-pointer" @click="openDetail(node)">
                  {{ Object.keys(node.props).length }}
                </td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs max-w-[180px] truncate cursor-pointer"
                    @click="openDetail(node)">
                  {{ node.id }}
                </td>
                <td class="px-4 py-3 text-right" @click.stop>
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openPropPanel(node)"
                            title="Editar propiedades"
                            class="text-gray-400 hover:text-violet-300 transition-colors p-1 rounded">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button @click="openDetail(node)"
                            class="text-violet-400 hover:text-violet-300 text-xs font-medium transition-colors">
                      Ver →
                    </button>
                    <button @click="deleteNode(node)"
                            title="Eliminar nodo"
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

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-800 flex items-center justify-between">
          <span class="text-xs text-gray-500">Página {{ page }}</span>
          <div class="flex gap-2">
            <button @click="prevPage" :disabled="page <= 1 || listLoading"
                    class="px-3 py-1.5 text-xs rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
              ← Anterior
            </button>
            <button @click="nextPage" :disabled="!hasMore || listLoading"
                    class="px-3 py-1.5 text-xs rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
              Siguiente →
            </button>
          </div>
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
            <div>
              <div class="flex flex-wrap gap-1.5 mb-2">
                <span v-for="lbl in selectedNode?.labels" :key="lbl"
                      class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="labelColor(lbl)">{{ lbl }}</span>
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

          <div class="px-6 py-4 space-y-2 max-h-[55vh] overflow-y-auto">
            <div v-for="(val, key) in selectedNode?.props" :key="key"
                 class="flex gap-3 py-2 border-b border-gray-800/60 last:border-0">
              <span class="text-xs font-mono text-violet-400 w-36 flex-shrink-0 pt-0.5">{{ key }}</span>
              <span class="text-sm text-gray-200 break-all">
                <template v-if="Array.isArray(val)">
                  <span v-if="!val.length" class="text-gray-500 italic">[ vacío ]</span>
                  <div v-else class="flex flex-wrap gap-1">
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
            <p v-if="!Object.keys(selectedNode?.props ?? {}).length"
               class="text-center text-gray-500 text-sm py-4">Sin propiedades.</p>
          </div>

          <div class="px-6 pb-5 pt-3 flex gap-2">
            <button @click="openPropPanel(selectedNode); showModal = false"
                    class="flex-1 bg-violet-700 hover:bg-violet-600 text-white text-sm py-2.5 rounded-lg transition-colors font-medium">
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

    <!-- ── Slide-over: Editar Propiedades ─────────────────────────────────── -->
    <Transition name="slide-right">
      <div v-if="propPanel" class="fixed inset-0 z-50 flex">
        <div class="flex-1 bg-black/50" @click="closePropPanel"/>
        <div class="w-full max-w-md bg-gray-900 border-l border-gray-800 flex flex-col shadow-2xl">

          <!-- Panel header -->
          <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between flex-shrink-0">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-white mb-1">Editar Propiedades</p>
              <div class="flex flex-wrap gap-1 mb-1">
                <span v-for="lbl in propPanel.labels" :key="lbl"
                      class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="labelColor(lbl)">{{ lbl }}</span>
              </div>
              <p class="text-xs text-gray-500 font-mono truncate">{{ propPanel.id }}</p>
            </div>
            <button @click="closePropPanel"
                    class="text-gray-500 hover:text-gray-300 transition-colors ml-3 flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Panel body -->
          <div class="flex-1 overflow-y-auto p-5 space-y-3">

            <!-- Feedback -->
            <div v-if="propError"
                 class="bg-red-900/40 border border-red-700 text-red-300 px-3 py-2 rounded-lg text-xs">
              {{ propError }}
            </div>
            <div v-if="propSuccess"
                 class="bg-emerald-900/40 border border-emerald-700 text-emerald-300 px-3 py-2 rounded-lg text-xs">
              {{ propSuccess }}
            </div>

            <!-- Existing + editable props -->
            <div v-for="(val, key) in editPropsMap" :key="key"
                 class="flex gap-2 items-center">
              <span class="text-xs font-mono text-violet-400 w-28 flex-shrink-0 truncate">{{ key }}</span>
              <input v-model="editPropsMap[key]"
                     type="text"
                     class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500"/>
              <button @click="removeEditProp(key)"
                      class="text-gray-500 hover:text-red-400 transition-colors flex-shrink-0 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Add new prop form -->
            <div class="pt-2 border-t border-gray-800">
              <p class="text-xs text-gray-500 mb-2 font-medium">Añadir propiedad</p>
              <div class="flex gap-2">
                <input v-model="newPropKey" type="text" placeholder="clave"
                       class="w-28 flex-shrink-0 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500 font-mono"/>
                <input v-model="newPropVal" type="text" placeholder="valor"
                       @keyup.enter="addEditProp"
                       class="flex-1 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500"/>
                <button @click="addEditProp"
                        class="flex-shrink-0 bg-violet-700 hover:bg-violet-600 text-white text-xs px-3 py-2 rounded-lg transition-colors">
                  +
                </button>
              </div>
            </div>
          </div>

          <!-- Panel footer -->
          <div class="px-5 py-4 border-t border-gray-800 flex gap-2 flex-shrink-0">
            <button @click="savePropEdit"
                    :disabled="propSaving"
                    class="flex-1 bg-violet-600 hover:bg-violet-500 disabled:opacity-50 text-white text-sm py-2.5 rounded-lg transition-colors font-medium">
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

    <!-- ── Modal CSV Import ─────────────────────────────────────────────────── -->
    <Transition name="fade">
      <div v-if="csvModal"
           class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
           @click.self="closeCsvModal">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-lg shadow-2xl">

          <!-- Header -->
          <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-800">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
              </svg>
              <h2 class="text-sm font-semibold text-white">Cargar Nodos desde CSV</h2>
            </div>
            <button @click="closeCsvModal" class="text-gray-500 hover:text-gray-300 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="px-6 py-5 space-y-4">

            <!-- Label selector -->
            <div>
              <label class="text-xs text-gray-400 font-medium block mb-1.5">Tipo de nodo</label>
              <select v-model="csvLabel"
                      class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500">
                <option v-for="(_, lbl) in schema" :key="lbl" :value="lbl">{{ lbl }}</option>
              </select>
            </div>

            <!-- Expected columns info -->
            <div class="bg-gray-800/60 rounded-lg p-3 space-y-1.5">
              <p class="text-xs font-medium text-gray-400">Columnas esperadas para <span class="text-violet-400">{{ csvLabel }}</span>:</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="col in csvExpectedCols" :key="col"
                      class="text-xs font-mono bg-gray-700 text-gray-300 px-2 py-0.5 rounded">{{ col }}</span>
                <span v-if="schema[csvLabel]?.secondary?.length"
                      class="text-xs font-mono bg-gray-700/60 text-gray-500 px-2 py-0.5 rounded border border-dashed border-gray-600">
                  secondaryLabels (opcional)
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                Listas: separadas por coma dentro de comillas <span class="font-mono text-gray-400">"val1, val2"</span>.
                Labels secundarios: separados por <span class="font-mono text-gray-400">|</span>
                <span v-if="schema[csvLabel]?.secondary?.length">
                  — válidos: <span class="text-violet-400">{{ schema[csvLabel].secondary.join(', ') }}</span>
                </span>
              </p>
            </div>

            <!-- File input -->
            <div>
              <label class="text-xs text-gray-400 font-medium block mb-1.5">Archivo CSV</label>
              <div class="relative">
                <input ref="csvFileInput" type="file" accept=".csv,text/csv"
                       @change="onCsvFileChange"
                       class="block w-full text-sm text-gray-400
                              file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-xs file:font-medium file:bg-violet-700 file:text-white
                              file:hover:bg-violet-600 file:cursor-pointer file:transition-colors
                              cursor-pointer"/>
              </div>
              <p v-if="csvFileName" class="text-xs text-gray-500 mt-1">{{ csvFileName }}</p>
            </div>

            <!-- Result -->
            <div v-if="csvResult" class="rounded-lg p-3 space-y-1"
                 :class="csvResult.errors.length ? 'bg-yellow-900/30 border border-yellow-700/50' : 'bg-emerald-900/30 border border-emerald-700/50'">
              <p class="text-sm font-medium" :class="csvResult.errors.length ? 'text-yellow-300' : 'text-emerald-300'">
                ✓ {{ csvResult.created }} nodo{{ csvResult.created !== 1 ? 's' : '' }} creado{{ csvResult.created !== 1 ? 's' : '' }}
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
                    class="flex-1 bg-violet-600 hover:bg-violet-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-medium py-2.5 rounded-lg transition-colors">
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

          <!-- Bulk mode: Add/Update -->
          <div v-if="bulkMode === 'add'" class="space-y-3">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-semibold text-white">
                Agregar / Actualizar Propiedades en {{ selectionCount }} nodo{{ selectionCount !== 1 ? 's' : '' }}
              </p>
              <button @click="bulkMode = null; bulkFields = []; bulkError = null"
                      class="text-gray-400 hover:text-gray-200 text-xs">Cancelar</button>
            </div>
            <div v-if="bulkError" class="text-red-400 text-xs bg-red-900/30 px-3 py-2 rounded-lg">{{ bulkError }}</div>
            <div class="flex flex-wrap gap-2">
              <div v-for="(f, i) in bulkFields" :key="i" class="flex gap-1.5 items-center">
                <input v-model="f.key" placeholder="clave" type="text"
                       class="w-28 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-violet-500 font-mono"/>
                <span class="text-gray-600 text-xs">:</span>
                <input v-model="f.val" placeholder="valor" type="text"
                       class="w-36 bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-violet-500"/>
                <button @click="bulkFields.splice(i, 1)"
                        class="text-gray-500 hover:text-red-400 transition-colors">
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
            <div class="flex gap-2 pt-1">
              <button @click="applyBulkUpdate"
                      :disabled="bulkSaving"
                      class="bg-emerald-700 hover:bg-emerald-600 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                {{ bulkSaving ? 'Aplicando...' : `Aplicar a ${selectionCount} nodos` }}
              </button>
            </div>
          </div>

          <!-- Bulk mode: Remove -->
          <div v-else-if="bulkMode === 'remove'" class="space-y-3">
            <div class="flex items-center justify-between mb-2">
              <p class="text-sm font-semibold text-white">
                Eliminar Propiedades de {{ selectionCount }} nodo{{ selectionCount !== 1 ? 's' : '' }}
              </p>
              <button @click="bulkMode = null; bulkKeys = []; bulkError = null"
                      class="text-gray-400 hover:text-gray-200 text-xs">Cancelar</button>
            </div>
            <div v-if="bulkError" class="text-red-400 text-xs bg-red-900/30 px-3 py-2 rounded-lg">{{ bulkError }}</div>
            <div class="flex flex-wrap gap-2 items-center">
              <div v-for="(_, i) in bulkKeys" :key="i" class="flex gap-1.5 items-center">
                <input v-model="bulkKeys[i]" placeholder="clave a eliminar" type="text"
                       class="w-40 bg-gray-800 border border-red-800/60 text-gray-100 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-red-500 font-mono"/>
                <button @click="bulkKeys.splice(i, 1)"
                        class="text-gray-500 hover:text-red-400 transition-colors">
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
            <div class="flex gap-2 pt-1">
              <button @click="applyBulkRemove"
                      :disabled="bulkSaving"
                      class="bg-red-700 hover:bg-red-600 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                {{ bulkSaving ? 'Eliminando...' : `Eliminar de ${selectionCount} nodos` }}
              </button>
            </div>
          </div>

          <!-- Bulk default bar -->
          <div v-else class="flex items-center gap-4 flex-wrap">
            <span class="text-sm text-gray-300 font-medium">
              {{ selectionCount }} nodo{{ selectionCount !== 1 ? 's' : '' }} seleccionado{{ selectionCount !== 1 ? 's' : '' }}
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
              <button @click="bulkDeleteNodes"
                      class="text-xs bg-red-800 hover:bg-red-700 text-red-100 border border-red-700 px-3 py-1.5 rounded-lg transition-colors font-medium">
                🗑 Eliminar Nodos
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

const props = defineProps({
  schema: { type: Object, required: true },
  flash:  { type: Object, default: () => ({}) },
})

const PER_PAGE = 25

// ── State ─────────────────────────────────────────────────────────────────────
const selectedLabel     = ref('Game')
const search            = ref('')
const nodes             = ref([])
const aggregates        = ref([])
const listLoading       = ref(false)
const aggregatesLoading = ref(true)
const showModal         = ref(false)
const selectedNode      = ref(null)
const page              = ref(1)
const hasMore           = ref(false)

// Selection
const selectedNodeIds = ref(new Set())
const selectionCount  = computed(() => selectedNodeIds.value.size)

// Prop edit panel
const propPanel   = ref(null)
const editPropsMap = ref({})
const newPropKey  = ref('')
const newPropVal  = ref('')
const propSaving  = ref(false)
const propError   = ref(null)
const propSuccess = ref(null)
const _originalProps = ref({})

// CSV Import
const csvModal     = ref(false)
const csvLabel     = ref('Game')
const csvFile      = ref(null)
const csvFileName  = ref('')
const csvUploading = ref(false)
const csvResult    = ref(null)
const csvError     = ref(null)
const csvFileInput = ref(null)

const csvExpectedCols = computed(() => {
  const s = props.schema[csvLabel.value]
  return s ? Object.keys(s.properties) : []
})

const openCsvModal = () => {
  csvLabel.value     = selectedLabel.value
  csvFile.value      = null
  csvFileName.value  = ''
  csvResult.value    = null
  csvError.value     = null
  csvModal.value     = true
}

const closeCsvModal = () => {
  csvModal.value = false
}

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
  form.append('label', csvLabel.value)

  try {
    const res = await axios.post('/nodes/import-csv', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    csvResult.value = res.data
    if (res.data.created > 0) {
      await loadNodes()
      await loadAggregates()
    }
  } catch (e) {
    csvError.value = e.response?.data?.error ?? 'Error al importar CSV'
  } finally {
    csvUploading.value = false
  }
}

// Bulk
const bulkMode   = ref(null)   // 'add' | 'remove' | null
const bulkFields = ref([])     // [{key, val}]
const bulkKeys   = ref([])     // [string]
const bulkSaving = ref(false)
const bulkError  = ref(null)

// ── Fetch ─────────────────────────────────────────────────────────────────────
const loadAggregates = async () => {
  aggregatesLoading.value = true
  try {
    const res = await axios.get('/nodes/aggregates')
    aggregates.value = res.data
  } catch { aggregates.value = [] }
  finally { aggregatesLoading.value = false }
}

const loadNodes = async () => {
  listLoading.value = true
  deselectAll()
  try {
    const res = await axios.get('/nodes/list', {
      params: { label: selectedLabel.value, search: search.value, page: page.value, limit: PER_PAGE + 1 },
    })
    const data = Array.isArray(res.data) ? res.data : []
    hasMore.value  = data.length > PER_PAGE
    nodes.value    = hasMore.value ? data.slice(0, PER_PAGE) : data
  } catch { nodes.value = []; hasMore.value = false }
  finally { listLoading.value = false }
}

const prevPage = () => { if (page.value > 1) { page.value--; loadNodes() } }
const nextPage = () => { if (hasMore.value)  { page.value++; loadNodes() } }

const searchNodes = () => { page.value = 1; loadNodes() }

// ── Selection ─────────────────────────────────────────────────────────────────
const toggleNodeSelect = (id) => {
  const s = new Set(selectedNodeIds.value)
  s.has(id) ? s.delete(id) : s.add(id)
  selectedNodeIds.value = s
}
const selectAll   = () => { selectedNodeIds.value = new Set(nodes.value.map(n => n.id)) }
const deselectAll = () => { selectedNodeIds.value = new Set() }

// ── Detail modal ──────────────────────────────────────────────────────────────
const openDetail = (node) => {
  selectedNode.value = node
  showModal.value    = true
}

// ── Property edit panel ───────────────────────────────────────────────────────
const displayVal = (v) => {
  if (v === null || v === undefined) return ''
  if (typeof v === 'boolean') return v ? 'true' : 'false'
  if (Array.isArray(v)) return v.join(', ')
  if (typeof v === 'object' && 'days' in v) return String(v.days)
  return String(v)
}

const openPropPanel = (node) => {
  propPanel.value    = node
  propError.value    = null
  propSuccess.value  = null
  _originalProps.value = { ...node.props }
  const mapped = {}
  for (const [k, v] of Object.entries(node.props)) {
    mapped[k] = displayVal(v)
  }
  editPropsMap.value = mapped
  newPropKey.value   = ''
  newPropVal.value   = ''
}

const closePropPanel = () => {
  propPanel.value = null
  propError.value = null
}

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
  propSaving.value = true
  propError.value  = null
  propSuccess.value = null
  try {
    const updates  = { ...editPropsMap.value }
    const origKeys = Object.keys(_originalProps.value)
    const removals = origKeys.filter(k => !(k in editPropsMap.value))

    if (Object.keys(updates).length > 0) {
      await axios.post('/nodes/properties/update', {
        id:         propPanel.value.id,
        properties: updates,
      })
    }
    if (removals.length > 0) {
      await axios.post('/nodes/properties/remove', {
        id:   propPanel.value.id,
        keys: removals,
      })
    }

    // Update local node state
    const newProps = { ...editPropsMap.value }
    const idx = nodes.value.findIndex(n => n.id === propPanel.value.id)
    if (idx !== -1) {
      nodes.value[idx] = { ...nodes.value[idx], props: newProps }
    }
    propPanel.value      = { ...propPanel.value, props: newProps }
    _originalProps.value = { ...newProps }
    propSuccess.value    = '¡Propiedades guardadas exitosamente!'
  } catch (e) {
    propError.value = e.response?.data?.error ?? 'Error al guardar propiedades'
  } finally {
    propSaving.value = false
  }
}

// ── Bulk operations ───────────────────────────────────────────────────────────
const applyBulkUpdate = async () => {
  const properties = {}
  bulkFields.value.forEach(f => { if (f.key.trim()) properties[f.key.trim()] = f.val })
  if (!Object.keys(properties).length) {
    bulkError.value = 'Agrega al menos una propiedad con clave'
    return
  }
  bulkSaving.value = true
  bulkError.value  = null
  try {
    await axios.post('/nodes/properties/bulk-update', {
      ids: [...selectedNodeIds.value],
      properties,
    })
    await loadNodes()
    bulkMode.value   = null
    bulkFields.value = []
  } catch (e) {
    bulkError.value = e.response?.data?.error ?? 'Error al aplicar'
  } finally {
    bulkSaving.value = false
  }
}

const applyBulkRemove = async () => {
  const keys = bulkKeys.value.map(k => k.trim()).filter(Boolean)
  if (!keys.length) {
    bulkError.value = 'Agrega al menos una clave a eliminar'
    return
  }
  bulkSaving.value = true
  bulkError.value  = null
  try {
    await axios.post('/nodes/properties/bulk-remove', {
      ids: [...selectedNodeIds.value],
      keys,
    })
    await loadNodes()
    bulkMode.value = null
    bulkKeys.value = []
  } catch (e) {
    bulkError.value = e.response?.data?.error ?? 'Error al eliminar'
  } finally {
    bulkSaving.value = false
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deleteNode = async (node) => {
  const name = mainProp(node)
  if (!window.confirm(`¿Eliminar nodo "${name}"? Se eliminarán también todas sus relaciones.`)) return
  try {
    await axios.post('/nodes/delete', { id: node.id })
    await loadNodes()
    await loadAggregates()
  } catch (e) {
    alert(e.response?.data?.error ?? 'Error al eliminar nodo')
  }
}

const bulkDeleteNodes = async () => {
  const count = selectionCount.value
  if (!window.confirm(`¿Eliminar ${count} nodo${count !== 1 ? 's' : ''}? Se eliminarán también todas sus relaciones.`)) return
  try {
    await axios.post('/nodes/bulk-delete', { ids: [...selectedNodeIds.value] })
    await loadNodes()
    await loadAggregates()
  } catch (e) {
    alert(e.response?.data?.error ?? 'Error al eliminar nodos')
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const mainProp = (node) => {
  const schema = props.schema[selectedLabel.value]
  if (!schema) return '—'
  return node.props[schema.searchField] ?? node.props[Object.keys(node.props)[0]] ?? '—'
}

const iconBg = (icon) => ({
  'bg-violet-600': icon === 'users',
  'bg-amber-600':  icon === 'star',
  'bg-sky-600':    icon === 'document',
  'bg-yellow-600': icon === 'trophy',
  'bg-rose-600':   icon === 'arrow-down',
  'bg-blue-600':   icon === 'chart',
  'bg-teal-600':   icon === 'tag',
})

const LABEL_COLORS = {
  User:      'bg-violet-900 text-violet-300',
  Post:      'bg-sky-900 text-sky-300',
  Community: 'bg-emerald-900 text-emerald-300',
  Game:      'bg-orange-900 text-orange-300',
  Award:     'bg-yellow-900 text-yellow-300',
  Genre:     'bg-pink-900 text-pink-300',
  Platform:  'bg-cyan-900 text-cyan-300',
  Tag:       'bg-gray-700 text-gray-300',
}
const labelColor = (lbl) => LABEL_COLORS[lbl] ?? 'bg-gray-700 text-gray-300'

// ── Lifecycle ─────────────────────────────────────────────────────────────────
watch(selectedLabel, () => { search.value = ''; page.value = 1; loadNodes() })
onMounted(() => { loadAggregates(); loadNodes() })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.25s ease; }
.slide-right-enter-from, .slide-right-leave-to       { transform: translateX(100%); }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.2s ease; }
.slide-up-enter-from, .slide-up-leave-to       { transform: translateY(100%); }
</style>
