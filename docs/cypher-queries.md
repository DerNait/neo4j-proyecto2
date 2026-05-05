# Cypher Queries — Neo4j Gaming Network

Catálogo de todas las queries Cypher usadas en el proyecto, organizadas por controlador y método.

---

## `app/Services/Neo4jClient.php`

### `warmUp()`

```cypher
RETURN 1
```

**Tipo:** Conexión / Ping  
**Explicación:** Query mínima ejecutada hasta 6 veces al iniciar cada worker de PHP-FPM. Su único propósito es forzar que el cliente Bolt establezca la conexión con AuraDB y resuelva la routing table antes de que llegue cualquier petición real. Evita el error `DatabaseNotFound: Database 519e803c not found` que AuraDB produce en la primera conexión en frío.

---

## `app/Http/Controllers/NodeController.php`

### `aggregates()` — Conteo de usuarios

```cypher
MATCH (n:User)
RETURN count(n) AS value
```

**Tipo:** Agregación  
**Explicación:** Cuenta el total de nodos con etiqueta `User` en el grafo. El resultado se muestra en la tarjeta "Total de Usuarios" del dashboard de `/nodes`.

---

### `aggregates()` — Promedio de Metacritic

```cypher
MATCH (g:Game)
WHERE g.metacriticScore IS NOT NULL
RETURN round(avg(toFloat(g.metacriticScore)), 2) AS value
```

**Tipo:** Agregación con filtro  
**Explicación:** Calcula el promedio del campo `metacriticScore` de todos los nodos `Game` que tienen ese campo definido. `toFloat()` garantiza que el promedio funcione aunque la propiedad esté almacenada como string. `round(..., 2)` limita el resultado a 2 decimales. Se muestra en la tarjeta "MetacriticScore Promedio".

---

### `aggregates()` — Conteo de posts

```cypher
MATCH (p:Post)
RETURN count(p) AS value
```

**Tipo:** Agregación  
**Explicación:** Cuenta el total de nodos con etiqueta `Post`. El resultado se muestra en la tarjeta "Total de Posts".

---

### `list()` — Listar nodos sin búsqueda

```cypher
MATCH (n:{label})
RETURN elementId(n) AS id, labels(n) AS lbls, properties(n) AS props
SKIP {skip} LIMIT {limit}
```

**Tipo:** Lectura con paginación  
**Explicación:** Recupera una página de nodos de un tipo específico (ej. `Game`, `User`). `SKIP` y `LIMIT` son enteros interpolados directamente para implementar la paginación de 25 en 25. Retorna el ID interno de Neo4j, las etiquetas y todas las propiedades de cada nodo.

---

### `list()` — Listar nodos con búsqueda de texto

```cypher
MATCH (n:{label})
WHERE toLower(toString(n.{searchField})) CONTAINS toLower('{safeSearch}')
RETURN elementId(n) AS id, labels(n) AS lbls, properties(n) AS props
SKIP {skip} LIMIT {limit}
```

**Tipo:** Lectura con filtro de texto y paginación  
**Explicación:** Igual que la anterior pero filtra por el campo de búsqueda propio de cada etiqueta (ej. `title` para `Game`, `username` para `User`). La comparación es case-insensitive gracias a `toLower()`. El término de búsqueda se sanitiza con `preg_replace` y `addslashes` antes de interpolarse directamente en el Cypher (sin parámetros Bolt) para evitar el bug de routing de AuraDB con parámetros no vacíos.

---

### `show()` — Detalle de un nodo por ID

```cypher
MATCH (n)
WHERE elementId(n) = $id
RETURN elementId(n) AS id, labels(n) AS lbls, properties(n) AS props
```

**Tipo:** Lectura por ID  
**Explicación:** Recupera un nodo específico usando su `elementId` interno de Neo4j. Se usa en la vista de detalle de un nodo individual (`/nodes/detail`). El ID se pasa como parámetro Bolt nombrado (`$id`).

---

### `store()` — Crear nodo

```cypher
CREATE (n:{labels} {prop1: $prop1, prop2: $prop2, ...})
RETURN elementId(n) AS id
```

**Tipo:** Escritura — creación  
**Explicación:** Crea un nodo nuevo con una o más etiquetas y sus propiedades. Las etiquetas se validan contra un esquema interno (whitelist) y se interpolan en el string; los valores de propiedades viajan siempre como parámetros Bolt nombrados para evitar inyección. Retorna el `elementId` del nodo recién creado.

---

### `destroy()` — Eliminar un nodo

```cypher
MATCH (n)
WHERE elementId(n) = $id
DETACH DELETE n
```

**Tipo:** Escritura — eliminación individual de nodo  
**Explicación:** Elimina un nodo específico usando su `elementId`. `DETACH DELETE` elimina el nodo y automáticamente todas sus relaciones entrantes y salientes, sin necesidad de borrarlas antes. El ID viaja como parámetro Bolt.

---

### `bulkDestroy()` — Eliminar múltiples nodos

```cypher
UNWIND $ids AS id
MATCH (n)
WHERE elementId(n) = id
DETACH DELETE n
```

**Tipo:** Escritura masiva — eliminación de nodos  
**Explicación:** Versión masiva de `destroy()`. `UNWIND` expande la lista de IDs y aplica `DETACH DELETE` a cada nodo en una sola transacción, eliminando también todas sus relaciones. Los IDs viajan como parámetro Bolt.

---

## `app/Http/Controllers/PropertyController.php`

### `updateNode()` — Actualizar propiedades de un nodo

```cypher
MATCH (n)
WHERE elementId(n) = $id
SET n += $props
```

**Tipo:** Escritura — actualización parcial  
**Explicación:** Añade o actualiza propiedades en un nodo existente usando el operador `+=`, que hace una fusión: conserva las propiedades que no se mencionan y sobreescribe solo las que sí llegan en `$props`. El mapa de propiedades viaja como parámetro Bolt.

---

### `removeFromNode()` — Eliminar propiedades de un nodo

```cypher
MATCH (n)
WHERE elementId(n) = $id
REMOVE n.prop1, n.prop2, ...
```

**Tipo:** Escritura — eliminación de propiedades  
**Explicación:** Elimina propiedades específicas de un nodo. Los nombres de las claves se validan con regex (`/^[a-zA-Z_][a-zA-Z0-9_]*$/`) y se interpolan en la cláusula `REMOVE` (Cypher no permite parameterizar nombres de propiedades). El ID del nodo se pasa como parámetro.

---

### `bulkUpdateNodes()` — Actualizar propiedades en múltiples nodos

```cypher
UNWIND $ids AS id
MATCH (n)
WHERE elementId(n) = id
SET n += $props
```

**Tipo:** Escritura masiva — actualización  
**Explicación:** Versión masiva de `updateNode`. `UNWIND` expande la lista de IDs enviada como parámetro y aplica el mismo `SET n += $props` a cada nodo en una sola transacción, lo que es más eficiente que N peticiones individuales. Usa el mismo operador de fusión `+=`.

---

### `bulkRemoveFromNodes()` — Eliminar propiedades en múltiples nodos

```cypher
UNWIND $ids AS id
MATCH (n)
WHERE elementId(n) = id
REMOVE n.prop1, n.prop2, ...
```

**Tipo:** Escritura masiva — eliminación de propiedades  
**Explicación:** Versión masiva de `removeFromNode`. `UNWIND` itera sobre todos los IDs y elimina las mismas propiedades en cada nodo. Las claves se validan y se interpolan en la cláusula `REMOVE`; los IDs viajan como parámetro Bolt.

---

## `app/Http/Controllers/RelationController.php`

### `list()` — Listar relaciones sin búsqueda

```cypher
MATCH (a)-[r{:TYPE}]->(b)
RETURN elementId(r) AS id, type(r) AS relType, properties(r) AS props,
       elementId(a) AS fromId, labels(a) AS fromLabels, properties(a) AS fromProps,
       elementId(b) AS toId,   labels(b) AS toLabels,   properties(b) AS toProps
LIMIT {limit}
```

**Tipo:** Lectura de relaciones con nodos adyacentes  
**Explicación:** Recupera relaciones junto con los datos completos de los nodos origen (`a`) y destino (`b`). El tipo de relación es opcional: si se especifica (ej. `:WROTE`) solo devuelve ese tipo; si no, devuelve todos. Retorna propiedades e IDs de la relación y de ambos nodos para poder mostrarlos en la tabla.

---

### `list()` — Listar relaciones con búsqueda de texto

```cypher
MATCH (a)-[r{:TYPE}]->(b)
WHERE toLower(toString(coalesce(a.name, a.title, a.username, ''))) CONTAINS toLower('{safe}')
   OR toLower(toString(coalesce(b.name, b.title, b.username, ''))) CONTAINS toLower('{safe}')
RETURN elementId(r) AS id, type(r) AS relType, properties(r) AS props,
       elementId(a) AS fromId, labels(a) AS fromLabels, properties(a) AS fromProps,
       elementId(b) AS toId,   labels(b) AS toLabels,   properties(b) AS toProps
LIMIT {limit}
```

**Tipo:** Lectura de relaciones con filtro de texto  
**Explicación:** Igual que la anterior pero filtra las relaciones donde el nombre del nodo origen **o** del nodo destino contenga el término buscado. `coalesce` prueba `name`, `title` y `username` en orden para adaptarse a cualquier etiqueta de nodo. La búsqueda es case-insensitive y el término se interpola directamente (sin parámetros Bolt) por el mismo motivo que en `NodeController::list()`.

---

### `store()` — Crear relación con propiedades

```cypher
MATCH (a), (b)
WHERE elementId(a) = $fromId AND elementId(b) = $toId
CREATE (a)-[r:TIPO {prop1: $prop1, prop2: $prop2, ...}]->(b)
RETURN elementId(r) AS id
```

**Tipo:** Escritura — creación de relación  
**Explicación:** Crea una relación entre dos nodos existentes localizados por su `elementId`. El tipo de relación se valida contra el whitelist y se interpola en el string; las propiedades (mínimo 3 requeridas) viajan como parámetros Bolt nombrados. Retorna el ID de la relación creada.

---

### `updateRelation()` — Actualizar propiedades de una relación

```cypher
MATCH ()-[r]->()
WHERE elementId(r) = $id
SET r += $props
```

**Tipo:** Escritura — actualización parcial de relación  
**Explicación:** Añade o actualiza propiedades en una relación existente usando el operador de fusión `+=`. El patrón `()-[r]->()` encuentra la relación sin importar sus nodos adyacentes ni su tipo.

---

### `removeFromRelation()` — Eliminar propiedades de una relación

```cypher
MATCH ()-[r]->()
WHERE elementId(r) = $id
REMOVE r.prop1, r.prop2, ...
```

**Tipo:** Escritura — eliminación de propiedades de relación  
**Explicación:** Elimina propiedades específicas de una relación. Las claves se validan y se interpolan en `REMOVE`; el ID de la relación viaja como parámetro.

---

### `bulkUpdateRelations()` — Actualizar propiedades en múltiples relaciones

```cypher
UNWIND $ids AS id
MATCH ()-[r]->()
WHERE elementId(r) = id
SET r += $props
```

**Tipo:** Escritura masiva — actualización de relaciones  
**Explicación:** Versión masiva de `updateRelation`. `UNWIND` expande la lista de IDs de relaciones y aplica el mismo mapa de propiedades a todas en una sola transacción.

---

### `bulkRemoveFromRelations()` — Eliminar propiedades en múltiples relaciones

```cypher
UNWIND $ids AS id
MATCH ()-[r]->()
WHERE elementId(r) = id
REMOVE r.prop1, r.prop2, ...
```

**Tipo:** Escritura masiva — eliminación de propiedades de relación  
**Explicación:** Versión masiva de `removeFromRelation`. `UNWIND` itera sobre todos los IDs de relaciones y elimina las mismas propiedades en cada una.

---

### `destroyRelation()` — Eliminar una relación

```cypher
MATCH ()-[r]->()
WHERE elementId(r) = $id
DELETE r
```

**Tipo:** Escritura — eliminación individual de relación  
**Explicación:** Elimina una relación específica por su `elementId`. A diferencia de `DETACH DELETE` (usado para nodos), para relaciones basta con `DELETE r` ya que las relaciones no tienen relaciones propias. El patrón `()-[r]->()` localiza la relación sin importar sus nodos adyacentes ni su tipo. El ID viaja como parámetro Bolt.

---

### `bulkDestroyRelations()` — Eliminar múltiples relaciones

```cypher
UNWIND $ids AS id
MATCH ()-[r]->()
WHERE elementId(r) = id
DELETE r
```

**Tipo:** Escritura masiva — eliminación de relaciones  
**Explicación:** Versión masiva de `destroyRelation`. `UNWIND` expande la lista de IDs y elimina cada relación encontrada en una sola transacción, lo que es más eficiente que N peticiones individuales. Los IDs viajan como parámetro Bolt.

---

## Agregaciones adicionales en `app/Http/Controllers/NodeController.php`

### `aggregates()` — Karma máximo de usuarios

```cypher
MATCH (u:User)
WHERE u.karmaPoints IS NOT NULL
RETURN max(toInteger(u.karmaPoints)) AS value
```

**Tipo:** Agregación — máximo  
**Explicación:** Devuelve el valor más alto de `karmaPoints` entre todos los usuarios. `max()` es una función de agregación nativa de Cypher. `toInteger()` garantiza que la comparación funcione aunque el campo esté almacenado como string. Se muestra en la tarjeta "Karma Máximo (User)".

---

### `aggregates()` — Karma mínimo de usuarios

```cypher
MATCH (u:User)
WHERE u.karmaPoints IS NOT NULL
RETURN min(toInteger(u.karmaPoints)) AS value
```

**Tipo:** Agregación — mínimo  
**Explicación:** Devuelve el valor más bajo de `karmaPoints` entre todos los usuarios. `min()` es el complemento de `max()`. Se muestra en la tarjeta "Karma Mínimo (User)".

---

### `aggregates()` — Total de upvotes en posts

```cypher
MATCH (p:Post)
WHERE p.upvotes IS NOT NULL
RETURN sum(toInteger(p.upvotes)) AS value
```

**Tipo:** Agregación — suma  
**Explicación:** Suma todos los `upvotes` de los nodos `Post`. `sum()` acumula el valor numérico de todos los registros que coinciden con el patrón. `toInteger()` asegura conversión correcta si el campo está almacenado como string. Se muestra en la tarjeta "Total Upvotes (Posts)".

---

### `aggregates()` — Desarrolladores únicos de juegos

```cypher
MATCH (g:Game)
WHERE g.developer IS NOT NULL
RETURN size(collect(DISTINCT g.developer)) AS value
```

**Tipo:** Agregación — collect + size  
**Explicación:** Combina dos funciones: `collect(DISTINCT g.developer)` agrupa todos los valores únicos del campo `developer` en una lista, y `size()` cuenta cuántos elementos tiene esa lista. El resultado es el número de estudios desarrolladores distintos registrados en el grafo. Se muestra en la tarjeta "Desarrolladores Únicos".

---

## `app/Http/Controllers/NodeController.php`

### `importCsv()` — Crear nodo desde fila CSV

```cypher
CREATE (n:{labels} {prop1: $prop1, prop2: $prop2, ...})
RETURN elementId(n) AS id
```

**Tipo:** Escritura — creación masiva desde CSV  
**Explicación:** Por cada fila válida del archivo CSV se genera un `CREATE` con las etiquetas y propiedades correspondientes al label seleccionado. Las etiquetas base y secundarias se validan contra el whitelist del esquema antes de interpolarse; los valores de propiedades viajan como parámetros Bolt nombrados. Los campos de tipo `list` se construyen a partir de los valores separados por coma dentro de la celda CSV. Si una fila tiene un campo requerido vacío o una etiqueta secundaria inválida, se omite y se reporta el error por número de fila sin interrumpir las demás filas.

---

## `app/Http/Controllers/RelationController.php`

### `importCsv()` — Crear relación desde fila CSV

```cypher
MATCH (a:{fromLabel}) WHERE toString(a.{fromField}) = $fromValue
MATCH (b:{toLabel})   WHERE toString(b.{toField})   = $toValue
CREATE (a)-[r:{relType} {prop1: $prop1, prop2: $prop2, prop3: $prop3}]->(b)
RETURN elementId(r) AS id
```

**Tipo:** Escritura — creación masiva de relaciones desde CSV  
**Explicación:** Por cada fila del CSV se buscan primero los nodos origen (`a`) y destino (`b`) usando el campo identificador propio de cada etiqueta (ej. `title` para `Game`, `name` para `Genre`, `username` para `User`). Los valores de búsqueda viajan como parámetros Bolt (`$fromValue`, `$toValue`). Si el `MATCH` no encuentra alguno de los dos nodos, no se crea la relación y se reporta el error por número de fila. El tipo de relación se valida contra el whitelist de `REL_TYPES` y las propiedades (mínimo 3 por tipo) viajan como parámetros Bolt nombrados. Todo ocurre en queries independientes por fila, lo que permite importaciones parciales sin rollback total.

---

## Resumen por tipo de operación

| Tipo | Queries |
|------|---------|
| Ping / Conexión | `RETURN 1` |
| Lectura — listado con paginación | `NodeController::list()` (×2) |
| Lectura — detalle por ID | `NodeController::show()` |
| Lectura — listado de relaciones | `RelationController::list()` (×2) |
| Agregación — count | `aggregates()` (×2) |
| Agregación — avg | `aggregates()` (×1) |
| Agregación — max | `aggregates()` (×1) |
| Agregación — min | `aggregates()` (×1) |
| Agregación — sum | `aggregates()` (×1) |
| Agregación — collect + size | `aggregates()` (×1) |
| Creación de nodo | `NodeController::store()` |
| Creación de relación | `RelationController::store()` |
| Actualización individual | `updateNode()`, `updateRelation()` |
| Eliminación de propiedades individual | `removeFromNode()`, `removeFromRelation()` |
| Actualización masiva | `bulkUpdateNodes()`, `bulkUpdateRelations()` |
| Eliminación masiva de propiedades | `bulkRemoveFromNodes()`, `bulkRemoveFromRelations()` |
| Eliminación individual de nodo | `NodeController::destroy()` |
| Eliminación masiva de nodos | `NodeController::bulkDestroy()` |
| Eliminación individual de relación | `RelationController::destroyRelation()` |
| Eliminación masiva de relaciones | `RelationController::bulkDestroyRelations()` |
| Creación masiva de nodos (CSV) | `NodeController::importCsv()` |
| Creación masiva de relaciones (CSV) | `RelationController::importCsv()` |
| Data Science — Filtrado Colaborativo | `AnalyticsController::recommend()` |
| Data Science — Centralidad de Grado Ponderada | `AnalyticsController::influencers()` |
| Data Science — Trending Score | `AnalyticsController::trending()` |

---

## `app/Http/Controllers/AnalyticsController.php`

### `recommend()` — Filtrado Colaborativo (Recomendaciones de Juegos)

```cypher
MATCH (target:User)
WHERE toLower(toString(target.username)) CONTAINS toLower('{safeUsername}')
WITH target LIMIT 1

MATCH (target)-[:MEMBER_OF]->(c:Community)<-[:MEMBER_OF]-(similar:User)
WHERE similar <> target

MATCH (similar)-[:UPVOTED]->(p:Post)-[:ABOUT]->(g:Game)
WHERE NOT EXISTS {
    MATCH (target)-[:UPVOTED|COMMENTED_ON]->(:Post)-[:ABOUT]->(g)
}

WITH g,
     count(DISTINCT similar) AS userOverlap,
     count(DISTINCT p)       AS postCount,
     toFloat(coalesce(g.metacriticScore, 0)) AS metacritic
RETURN g.title AS title, g.developer AS developer, g.metacriticScore AS metacriticScore,
       userOverlap, postCount,
       round(userOverlap * 3.0 + postCount * 1.0 + metacritic * 0.1, 2) AS score
ORDER BY score DESC LIMIT 10
```

**Tipo:** Data Science — Filtrado Colaborativo  
**Explicación:** Implementa filtrado colaborativo basado en grafos. Primero encuentra usuarios similares al target usando membresía compartida en comunidades (`MEMBER_OF`) como proxy de similitud. Luego identifica juegos que esos usuarios similares valoraron positivamente (`UPVOTED → Post → ABOUT → Game`) pero que el target no conoce (`NOT EXISTS`). El score pondera: `userOverlap × 3` (señal social más fuerte), `postCount × 1` (volumen de contenido) y `metacritic × 0.1` (calidad objetiva). El username se sanitiza con `preg_replace` + `addslashes` e interpola directamente en el Cypher (mismo patrón defensivo del resto del proyecto para evitar el bug de routing de AuraDB con parámetros no vacíos).

---

### `influencers()` — Centralidad de Grado Ponderada

```cypher
MATCH (u:User)
OPTIONAL MATCH (u)<-[:FOLLOWS]-(follower:User)
OPTIONAL MATCH (u)-[:WROTE]->(p:Post)
WITH u,
     count(DISTINCT follower) AS followers,
     count(DISTINCT p)        AS posts,
     toInteger(coalesce(u.karmaPoints, 0)) AS karma
RETURN u.username AS username, followers, posts, karma,
       round(toFloat(followers) * 2.0 + toFloat(posts) * 1.5 + toFloat(karma) * 0.01, 2) AS influenceScore
ORDER BY influenceScore DESC LIMIT 15
```

**Tipo:** Data Science — Centralidad de Grado Ponderada (Degree Centrality)  
**Explicación:** Calcula una variante de degree centrality ponderada semánticamente para el dominio gaming. Los `followers` valen más (señal social directa, peso 2.0), los `posts` miden actividad de contenido (peso 1.5) y el `karma` la reputación acumulada (peso 0.01, escala diferente). `OPTIONAL MATCH` garantiza que usuarios sin seguidores o sin posts no se excluyan del ranking. Retorna los 15 usuarios más influyentes.

---

### `trending()` — Trending Score (Actividad de Juegos)

```cypher
MATCH (p:Post)-[:ABOUT]->(g:Game)
OPTIONAL MATCH (u:User)-[:UPVOTED]->(p)
OPTIONAL MATCH (u2:User)-[:COMMENTED_ON]->(p)
WITH g, p,
     count(DISTINCT u)  AS voters,
     count(DISTINCT u2) AS commenters,
     toInteger(coalesce(p.upvotes, 0)) AS upvotes
WITH g,
     count(DISTINCT p) AS postCount,
     sum(upvotes)       AS totalUpvotes,
     sum(voters)        AS totalVoters,
     sum(commenters)    AS totalCommenters
RETURN g.title AS title, g.developer AS developer, g.metacriticScore AS metacriticScore,
       postCount, totalUpvotes, totalCommenters,
       round(toFloat(postCount) * 5.0 + toFloat(totalUpvotes) * 0.05 + toFloat(totalVoters) * 1.0, 2) AS trendScore
ORDER BY trendScore DESC LIMIT 10
```

**Tipo:** Data Science — Scoring de Tendencias  
**Explicación:** Mide la actividad total generada por cada juego en la red social. Agrega en dos etapas: primero a nivel de post (voters y commenters únicos por post), luego a nivel de juego (suma los posts, upvotes y votantes de todos sus posts). El trendScore pondera: `postCount × 5` (cantidad de posts como señal fuerte de relevancia), `totalUpvotes × 0.05` (volumen de votos con escala reducida) y `totalVoters × 1` (usuarios únicos que votaron, señal de alcance). Retorna los 10 juegos en mayor tendencia.
