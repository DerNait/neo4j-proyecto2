<?php

namespace App\Http\Controllers;

use App\Services\Neo4jClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;

class RelationController extends Controller
{
    private const REL_TYPES = [
        'IS_OF', 'RELEASED_ON', 'RELATED_TO', 'POSTED_IN', 'WROTE',
        'FOLLOWS', 'MEMBER_OF', 'UPVOTED', 'COMMENTED_ON', 'LIKES',
        'CROSSPOSTED_TO', 'RECEIVED_AWARD', 'TAGGED_WITH', 'ABOUT',
    ];

    // Search field used to identify each node in CSV import
    private const NODE_SEARCH = [
        'User'      => 'username',
        'Post'      => 'title',
        'Community' => 'name',
        'Game'      => 'title',
        'Award'     => 'name',
        'Genre'     => 'name',
        'Platform'  => 'name',
        'Tag'       => 'name',
    ];

    private const REL_SCHEMA = [
        'IS_OF' => [
            'from' => 'Game', 'to' => 'Genre',
            'properties' => [
                'assignedAt'      => ['type' => 'date',    'required' => true],
                'isPrimaryGenre'  => ['type' => 'boolean', 'required' => true],
                'matchPercentage' => ['type' => 'float',   'required' => true],
            ],
        ],
        'RELEASED_ON' => [
            'from' => 'Game', 'to' => 'Platform',
            'properties' => [
                'releasedAt' => ['type' => 'date',    'required' => true],
                'isPorted'   => ['type' => 'boolean', 'required' => true],
                'targetFps'  => ['type' => 'integer', 'required' => true],
            ],
        ],
        'RELATED_TO' => [
            'from' => 'Community', 'to' => 'Game',
            'properties' => [
                'establishedAt' => ['type' => 'date',    'required' => true],
                'officialLink'  => ['type' => 'string',  'required' => true],
                'priorityLevel' => ['type' => 'integer', 'required' => true],
            ],
        ],
        'POSTED_IN' => [
            'from' => 'Post', 'to' => 'Community',
            'properties' => [
                'isPinned'       => ['type' => 'boolean', 'required' => true],
                'allowsComments' => ['type' => 'boolean', 'required' => true],
                'offTopic'       => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'WROTE' => [
            'from' => 'User', 'to' => 'Post',
            'properties' => [
                'clientApp' => ['type' => 'string',  'required' => true],
                'isEdited'  => ['type' => 'boolean', 'required' => true],
                'location'  => ['type' => 'string',  'required' => true],
            ],
        ],
        'FOLLOWS' => [
            'from' => 'User', 'to' => 'User',
            'properties' => [
                'sinceAt'         => ['type' => 'date',    'required' => true],
                'notificationsOn' => ['type' => 'boolean', 'required' => true],
                'closeFriend'     => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'MEMBER_OF' => [
            'from' => 'User', 'to' => 'Community',
            'properties' => [
                'joinedAt' => ['type' => 'date',    'required' => true],
                'role'     => ['type' => 'string',  'required' => true],
                'isActive' => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'UPVOTED' => [
            'from' => 'User', 'to' => 'Post',
            'properties' => [
                'upvotedAt'   => ['type' => 'date',    'required' => true],
                'voteWeight'  => ['type' => 'integer', 'required' => true],
                'isSuperVote' => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'COMMENTED_ON' => [
            'from' => 'User', 'to' => 'Post',
            'properties' => [
                'comment'     => ['type' => 'string',  'required' => true],
                'commentedAt' => ['type' => 'date',    'required' => true],
                'isReply'     => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'LIKES' => [
            'from' => 'User', 'to' => 'Genre',
            'properties' => [
                'likedAt'        => ['type' => 'date',    'required' => true],
                'intensityLevel' => ['type' => 'integer', 'required' => true],
                'isPublic'       => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'CROSSPOSTED_TO' => [
            'from' => 'Post', 'to' => 'Community',
            'properties' => [
                'crosspostedAt' => ['type' => 'date',    'required' => true],
                'karmaGained'   => ['type' => 'integer', 'required' => true],
                'reason'        => ['type' => 'string',  'required' => true],
            ],
        ],
        'RECEIVED_AWARD' => [
            'from' => 'Post', 'to' => 'Award',
            'properties' => [
                'grantedAt'   => ['type' => 'date',    'required' => true],
                'quantity'    => ['type' => 'integer', 'required' => true],
                'givenByUser' => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'TAGGED_WITH' => [
            'from' => 'Post', 'to' => 'Tag',
            'properties' => [
                'addedAt'            => ['type' => 'date',    'required' => true],
                'confidenceScore'    => ['type' => 'float',   'required' => true],
                'isModeratorApplied' => ['type' => 'boolean', 'required' => true],
            ],
        ],
        'ABOUT' => [
            'from' => 'Post', 'to' => 'Game',
            'properties' => [
                'containsSpoilers' => ['type' => 'boolean', 'required' => true],
                'isReview'         => ['type' => 'boolean', 'required' => true],
                'firstTime'        => ['type' => 'boolean', 'required' => true],
            ],
        ],
    ];

    public function __construct(private Neo4jClient $neo4j) {}

    // ── Páginas Inertia ────────────────────────────────────────────────────────

    public function index()
    {
        return Inertia::render('Relations/RelationIndex', [
            'relTypes'  => self::REL_TYPES,
            'relSchema' => self::REL_SCHEMA,
            'nodeSearch' => self::NODE_SEARCH,
        ]);
    }

    public function create()
    {
        return Inertia::render('Relations/RelationCreate', [
            'relTypes' => self::REL_TYPES,
        ]);
    }

    // ── API: listado de relaciones ─────────────────────────────────────────────

    public function list(Request $request)
    {
        $type   = $request->input('type', '');
        $limit  = min((int) $request->input('limit', 25), 100);
        $search = trim($request->input('search', ''));

        if ($type && !in_array($type, self::REL_TYPES, true)) {
            return response()->json(['error' => 'Tipo de relación inválido'], 422);
        }

        $typeClause = $type ? ":{$type}" : '';

        if ($search !== '') {
            $safe = addslashes(preg_replace('/[^\p{L}\p{N}\s\-\.]/u', '', $search));
            $cypher = "MATCH (a)-[r{$typeClause}]->(b)
                       WHERE toLower(toString(coalesce(a.name, a.title, a.username, ''))) CONTAINS toLower('{$safe}')
                          OR toLower(toString(coalesce(b.name, b.title, b.username, ''))) CONTAINS toLower('{$safe}')
                       RETURN elementId(r) AS id, type(r) AS relType, properties(r) AS props,
                              elementId(a) AS fromId, labels(a) AS fromLabels, properties(a) AS fromProps,
                              elementId(b) AS toId, labels(b) AS toLabels, properties(b) AS toProps
                       LIMIT {$limit}";
            $params = [];
        } else {
            $cypher = "MATCH (a)-[r{$typeClause}]->(b)
                       RETURN elementId(r) AS id, type(r) AS relType, properties(r) AS props,
                              elementId(a) AS fromId, labels(a) AS fromLabels, properties(a) AS fromProps,
                              elementId(b) AS toId, labels(b) AS toLabels, properties(b) AS toProps
                       LIMIT {$limit}";
            $params = [];
        }

        try {
            $result = $this->neo4j->run($cypher, $params);
            $rels   = [];
            foreach ($result as $row) {
                $rels[] = $this->formatRelRow($row);
            }
            return response()->json($rels);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── API: crear relación con 3+ propiedades ─────────────────────────────────

    public function store(Request $request)
    {
        $fromId  = $request->input('fromId', '');
        $toId    = $request->input('toId', '');
        $relType = $request->input('relType', '');
        $props   = $request->input('properties', []);

        if (!in_array($relType, self::REL_TYPES, true)) {
            return response()->json(['error' => 'Tipo de relación inválido'], 422);
        }
        if (!$fromId || !$toId) {
            return response()->json(['error' => 'Nodos origen y destino requeridos'], 422);
        }
        if (count($props) < 3) {
            return response()->json(['error' => 'Se requieren mínimo 3 propiedades en la relación'], 422);
        }

        foreach (array_keys($props) as $key) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                return response()->json(['error' => "Clave inválida: {$key}"], 422);
            }
        }

        // relType is whitelist-validated — safe to embed; props go as named params
        $propPairs = implode(', ', array_map(fn($k) => "{$k}: \${$k}", array_keys($props)));
        $cypher    = "MATCH (a), (b)
                      WHERE elementId(a) = \$fromId AND elementId(b) = \$toId
                      CREATE (a)-[r:{$relType} {{$propPairs}}]->(b)
                      RETURN elementId(r) AS id";

        $params = array_merge(['fromId' => $fromId, 'toId' => $toId], $props);

        try {
            $result = $this->neo4j->run($cypher, $params);
            $id     = $result->count() > 0 ? (string) $result->first()->get('id') : null;
            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── CSV Import de relaciones ───────────────────────────────────────────────

    public function importCsv(Request $request)
    {
        $relType = $request->input('relType', '');

        if (!array_key_exists($relType, self::REL_SCHEMA)) {
            return response()->json(['error' => 'Tipo de relación inválido'], 422);
        }

        if (!$request->hasFile('csv')) {
            return response()->json(['error' => 'No se proporcionó archivo CSV'], 422);
        }

        $content = file_get_contents($request->file('csv')->getRealPath());
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines   = array_values(array_filter(explode("\n", $content), fn($l) => trim($l) !== ''));

        if (count($lines) < 2) {
            return response()->json(['error' => 'El CSV necesita al menos encabezado y una fila de datos'], 422);
        }

        $headers    = array_map('trim', str_getcsv(array_shift($lines)));
        $schema     = self::REL_SCHEMA[$relType];
        $fromLabel  = $schema['from'];
        $toLabel    = $schema['to'];
        $fromField  = self::NODE_SEARCH[$fromLabel];
        $toField    = self::NODE_SEARCH[$toLabel];

        $created = 0;
        $errors  = [];

        foreach ($lines as $i => $line) {
            $rowNum = $i + 2;
            $cols   = str_getcsv($line);

            if (count($cols) !== count($headers)) {
                $errors[] = "Fila {$rowNum}: número de columnas incorrecto";
                continue;
            }

            $data = array_combine($headers, array_map('trim', $cols));

            if (empty($data['fromValue']) || empty($data['toValue'])) {
                $errors[] = "Fila {$rowNum}: fromValue y toValue son requeridos";
                continue;
            }

            $params = [
                'fromValue' => $data['fromValue'],
                'toValue'   => $data['toValue'],
            ];
            $rowOk = true;

            foreach ($schema['properties'] as $key => $meta) {
                $val = $data[$key] ?? null;
                if ($val === null || $val === '') {
                    if ($meta['required']) {
                        $errors[] = "Fila {$rowNum}: campo requerido '{$key}' faltante";
                        $rowOk = false;
                        break;
                    }
                    continue;
                }
                $params[$key] = $this->castRelValue($val, $meta['type']);
            }

            if (!$rowOk) continue;

            $propPairs = implode(', ', array_map(
                fn($k) => "{$k}: \${$k}",
                array_keys($schema['properties'])
            ));

            $cypher = "MATCH (a:{$fromLabel}) WHERE toString(a.{$fromField}) = \$fromValue
                       MATCH (b:{$toLabel}) WHERE toString(b.{$toField}) = \$toValue
                       CREATE (a)-[r:{$relType} {{$propPairs}}]->(b)
                       RETURN elementId(r) AS id";

            try {
                $result = $this->neo4j->run($cypher, $params);
                if ($result->count() > 0) {
                    $created++;
                } else {
                    $errors[] = "Fila {$rowNum}: nodos origen/destino no encontrados (\"{$data['fromValue']}\" → \"{$data['toValue']}\")";
                }
            } catch (\Throwable $e) {
                $errors[] = "Fila {$rowNum}: " . $e->getMessage();
            }
        }

        return response()->json(['created' => $created, 'errors' => $errors]);
    }

    private function castRelValue(mixed $val, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $val,
            'float'   => (float) $val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            default   => (string) $val,
        };
    }

    // ── Helpers privados de validación ─────────────────────────────────────────

    private function validateKeys(array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', (string) $key)) {
                return "Clave inválida: {$key}";
            }
        }
        return null;
    }

    // ── API: propiedades individuales de relación ──────────────────────────────

    public function updateRelation(Request $request)
    {
        $id    = $request->input('id');
        $props = $request->input('properties', []);

        if (!$id || empty($props)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }
        if ($err = $this->validateKeys(array_keys($props))) {
            return response()->json(['error' => $err], 422);
        }

        try {
            $this->neo4j->run(
                'MATCH ()-[r]->() WHERE elementId(r) = $id SET r += $props',
                ['id' => $id, 'props' => $props]
            );
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeFromRelation(Request $request)
    {
        $id   = $request->input('id');
        $keys = $request->input('keys', []);

        if (!$id || empty($keys)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }
        if ($err = $this->validateKeys($keys)) {
            return response()->json(['error' => $err], 422);
        }

        $removes = implode(', ', array_map(fn($k) => "r.{$k}", $keys));

        try {
            $this->neo4j->run(
                "MATCH ()-[r]->() WHERE elementId(r) = \$id REMOVE {$removes}",
                ['id' => $id]
            );
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── API: propiedades masivas de relaciones ─────────────────────────────────

    public function bulkUpdateRelations(Request $request)
    {
        $ids   = $request->input('ids', []);
        $props = $request->input('properties', []);

        if (empty($ids) || empty($props)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }
        if ($err = $this->validateKeys(array_keys($props))) {
            return response()->json(['error' => $err], 422);
        }

        try {
            $this->neo4j->run(
                'UNWIND $ids AS id MATCH ()-[r]->() WHERE elementId(r) = id SET r += $props',
                ['ids' => array_values($ids), 'props' => $props]
            );
            return response()->json(['success' => true, 'count' => count($ids)]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkRemoveFromRelations(Request $request)
    {
        $ids  = $request->input('ids', []);
        $keys = $request->input('keys', []);

        if (empty($ids) || empty($keys)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }
        if ($err = $this->validateKeys($keys)) {
            return response()->json(['error' => $err], 422);
        }

        $removes = implode(', ', array_map(fn($k) => "r.{$k}", $keys));

        try {
            $this->neo4j->run(
                "UNWIND \$ids AS id MATCH ()-[r]->() WHERE elementId(r) = id REMOVE {$removes}",
                ['ids' => array_values($ids)]
            );
            return response()->json(['success' => true, 'count' => count($ids)]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── API: eliminación individual de relación ───────────────────────────────

    public function destroyRelation(Request $request)
    {
        $id = $request->input('id', '');
        if (!$id) {
            return response()->json(['error' => 'ID requerido'], 422);
        }
        try {
            $this->neo4j->run(
                'MATCH ()-[r]->() WHERE elementId(r) = $id DELETE r',
                ['id' => $id]
            );
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── API: eliminación masiva de relaciones ─────────────────────────────────

    public function bulkDestroyRelations(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'IDs requeridos'], 422);
        }
        try {
            $this->neo4j->run(
                'UNWIND $ids AS id MATCH ()-[r]->() WHERE elementId(r) = id DELETE r',
                ['ids' => array_values($ids)]
            );
            return response()->json(['success' => true, 'count' => count($ids)]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Helpers de formato ─────────────────────────────────────────────────────

    private function formatRelRow(mixed $row): array
    {
        return [
            'id'    => (string) $row->get('id'),
            'type'  => $row->get('relType'),
            'props' => $this->convertCypherMap($row->get('props')),
            'from'  => [
                'id'     => (string) $row->get('fromId'),
                'labels' => $this->toArray($row->get('fromLabels')),
                'props'  => $this->convertCypherMap($row->get('fromProps')),
            ],
            'to'    => [
                'id'     => (string) $row->get('toId'),
                'labels' => $this->toArray($row->get('toLabels')),
                'props'  => $this->convertCypherMap($row->get('toProps')),
            ],
        ];
    }

    private function toArray(mixed $v): array
    {
        return $v instanceof CypherList ? $v->toArray() : (array) $v;
    }

    private function convertCypherMap(mixed $map): array
    {
        if (!($map instanceof CypherMap)) {
            return [];
        }
        $out = [];
        foreach ($map as $k => $v) {
            $out[$k] = $v instanceof CypherList
                ? $v->toArray()
                : ($v instanceof CypherMap ? $this->convertCypherMap($v) : $v);
        }
        return $out;
    }
}
