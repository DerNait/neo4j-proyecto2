<?php

namespace App\Http\Controllers;

use App\Services\Neo4jClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;

class NodeController extends Controller
{
    /**
     * Whitelist completo del modelo de datos.
     * Labels e properties están hardcodeados aquí para prevenir inyección Cypher.
     */
    private const SCHEMA = [
        'User' => [
            'secondary'   => ['Admin', 'Moderator', 'Bot'],
            'searchField' => 'username',
            'properties'  => [
                'username'    => ['type' => 'string',  'required' => true,  'label' => 'Username'],
                'joinDate'    => ['type' => 'date',    'required' => true,  'label' => 'Fecha de registro'],
                'karmaPoints' => ['type' => 'integer', 'required' => true,  'label' => 'Karma points'],
                'isPremium'   => ['type' => 'boolean', 'required' => true,  'label' => 'Es premium'],
                'socialLinks' => ['type' => 'list',    'required' => false, 'label' => 'Links sociales (coma)'],
            ],
        ],
        'Post' => [
            'secondary'   => ['Featured', 'Announcement', 'Archived'],
            'searchField' => 'title',
            'properties'  => [
                'id'        => ['type' => 'string',  'required' => true,  'label' => 'ID único'],
                'title'     => ['type' => 'string',  'required' => true,  'label' => 'Título'],
                'body'      => ['type' => 'string',  'required' => true,  'label' => 'Contenido'],
                'createdAt' => ['type' => 'date',    'required' => true,  'label' => 'Fecha de creación'],
                'upvotes'   => ['type' => 'integer', 'required' => true,  'label' => 'Upvotes'],
                'keywords'  => ['type' => 'list',    'required' => false, 'label' => 'Keywords (coma)'],
            ],
        ],
        'Community' => [
            'secondary'   => ['Official', 'Restricted', 'Trending'],
            'searchField' => 'name',
            'properties'  => [
                'name'        => ['type' => 'string',  'required' => true,  'label' => 'Nombre'],
                'createdDate' => ['type' => 'date',    'required' => true,  'label' => 'Fecha de creación'],
                'memberCount' => ['type' => 'integer', 'required' => true,  'label' => 'Miembros'],
                'isNSFW'      => ['type' => 'boolean', 'required' => true,  'label' => 'Es NSFW'],
                'rules'       => ['type' => 'list',    'required' => false, 'label' => 'Reglas (coma)'],
            ],
        ],
        'Game' => [
            'secondary'   => ['Indie', 'AAA', 'EarlyAccess'],
            'searchField' => 'title',
            'properties'  => [
                'title'           => ['type' => 'string',  'required' => true,  'label' => 'Título'],
                'developer'       => ['type' => 'string',  'required' => true,  'label' => 'Desarrollador'],
                'releaseDate'     => ['type' => 'date',    'required' => true,  'label' => 'Fecha de lanzamiento'],
                'metacriticScore' => ['type' => 'float',   'required' => true,  'label' => 'Metacritic Score'],
                'features'        => ['type' => 'list',    'required' => false, 'label' => 'Features (coma)'],
            ],
        ],
        'Award' => [
            'secondary'   => ['Seasonal', 'Legacy'],
            'searchField' => 'name',
            'properties'  => [
                'name'      => ['type' => 'string',  'required' => true, 'label' => 'Nombre'],
                'coinCost'  => ['type' => 'integer', 'required' => true, 'label' => 'Costo en monedas'],
                'isRare'    => ['type' => 'boolean', 'required' => true, 'label' => 'Es raro'],
                'iconUrl'   => ['type' => 'string',  'required' => true, 'label' => 'URL del ícono'],
                'createdAt' => ['type' => 'date',    'required' => true, 'label' => 'Fecha de creación'],
            ],
        ],
        'Genre' => [
            'secondary'   => [],
            'searchField' => 'name',
            'properties'  => [
                'name'           => ['type' => 'string',  'required' => true, 'label' => 'Nombre'],
                'slug'           => ['type' => 'string',  'required' => true, 'label' => 'Slug'],
                'description'    => ['type' => 'string',  'required' => true, 'label' => 'Descripción'],
                'colorHex'       => ['type' => 'string',  'required' => true, 'label' => 'Color Hex'],
                'popularityRank' => ['type' => 'integer', 'required' => true, 'label' => 'Rank de popularidad'],
            ],
        ],
        'Platform' => [
            'secondary'   => [],
            'searchField' => 'name',
            'properties'  => [
                'name'         => ['type' => 'string',  'required' => true,  'label' => 'Nombre'],
                'manufacturer' => ['type' => 'string',  'required' => true,  'label' => 'Fabricante'],
                'generation'   => ['type' => 'integer', 'required' => true,  'label' => 'Generación'],
                'releasedAt'   => ['type' => 'date',    'required' => true,  'label' => 'Fecha de lanzamiento'],
                'websiteUrl'   => ['type' => 'string',  'required' => false, 'label' => 'Sitio web'],
                'iconUrl'      => ['type' => 'string',  'required' => false, 'label' => 'URL del ícono'],
            ],
        ],
        'Tag' => [
            'secondary'   => [],
            'searchField' => 'name',
            'properties'  => [
                'name'        => ['type' => 'string',  'required' => true, 'label' => 'Nombre'],
                'description' => ['type' => 'string',  'required' => true, 'label' => 'Descripción'],
                'createdAt'   => ['type' => 'date',    'required' => true, 'label' => 'Fecha de creación'],
                'usageCount'  => ['type' => 'integer', 'required' => true, 'label' => 'Usos totales'],
                'isOfficial'  => ['type' => 'boolean', 'required' => true, 'label' => 'Es oficial'],
            ],
        ],
    ];

    public function __construct(private Neo4jClient $neo4j) {}

    public function index()
    {
        return Inertia::render('Nodes/NodeIndex', [
            'schema' => self::SCHEMA,
        ]);
    }

    public function create()
    {
        return Inertia::render('Nodes/NodeCreate', [
            'schema' => self::SCHEMA,
        ]);
    }

    public function aggregates()
    {
        $queries = [
            [
                'label'  => 'Total de Usuarios',
                'cypher' => 'MATCH (n:User) RETURN count(n) AS value',
                'icon'   => 'users',
                'format' => 'integer',
            ],
            [
                'label'  => 'MetacriticScore Promedio',
                'cypher' => 'MATCH (g:Game) WHERE g.metacriticScore IS NOT NULL RETURN round(avg(g.metacriticScore), 2) AS value',
                'icon'   => 'star',
                'format' => 'float',
            ],
            [
                'label'  => 'Total de Posts',
                'cypher' => 'MATCH (p:Post) RETURN count(p) AS value',
                'icon'   => 'document',
                'format' => 'integer',
            ],
        ];

        $stats = [];
        foreach ($queries as $q) {
            try {
                $result = $this->neo4j->run($q['cypher'], []);
                $value  = $result->count() > 0 ? $result->first()->get('value') : 0;
            } catch (\Throwable) {
                $value = 0;
            }
            $stats[] = [
                'label'  => $q['label'],
                'value'  => $value,
                'icon'   => $q['icon'],
                'format' => $q['format'],
            ];
        }

        return response()->json($stats);
    }

    public function list(Request $request)
    {
        $label  = $request->input('label', 'Game');
        $search = trim($request->input('search', ''));
        $limit  = min((int) $request->input('limit', 25), 100);

        if (!array_key_exists($label, self::SCHEMA)) {
            return response()->json(['error' => 'Label inválido'], 422);
        }

        $searchField = self::SCHEMA[$label]['searchField'];

        if ($search !== '') {
            $params = ['search' => $search];
            $cypher = "MATCH (n:{$label})
                       WHERE toLower(toString(n.{$searchField})) CONTAINS toLower(\$search)
                       RETURN elementId(n) AS id, labels(n) AS lbls, properties(n) AS props
                       LIMIT {$limit}";
        } else {
            $params = [];
            $cypher = "MATCH (n:{$label})
                       RETURN elementId(n) AS id, labels(n) AS lbls, properties(n) AS props
                       LIMIT {$limit}";
        }

        try {
            $result = $this->neo4j->run($cypher, $params);
            $nodes  = [];
            foreach ($result as $row) {
                $nodes[] = $this->formatRow($row);
            }
            return response()->json($nodes);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        $id    = $request->input('id');
        $label = $request->input('label');

        if (!$id || !array_key_exists($label, self::SCHEMA)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }

        try {
            $result = $this->neo4j->run(
                'MATCH (n) WHERE elementId(n) = $id
                 RETURN elementId(n) AS id, labels(n) AS lbls, properties(n) AS props',
                ['id' => $id]
            );

            if ($result->count() === 0) {
                return response()->json(['error' => 'Nodo no encontrado'], 404);
            }

            return response()->json($this->formatRow($result->first()));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $base        = $request->input('baseLabel', '');
        $secondaries = $request->input('secondaryLabels', []);
        $rawProps    = $request->input('properties', []);

        // Validar base label contra whitelist
        if (!array_key_exists($base, self::SCHEMA)) {
            return back()->withErrors(['baseLabel' => 'Etiqueta base inválida.']);
        }

        // Validar secondary labels contra whitelist
        $allowedSecondary = self::SCHEMA[$base]['secondary'];
        foreach ($secondaries as $s) {
            if (!in_array($s, $allowedSecondary, true)) {
                return back()->withErrors(['secondaryLabels' => "Etiqueta secundaria inválida: {$s}"]);
            }
        }

        // Construir cadena de labels (segura: sólo caracteres del whitelist)
        $allLabels = array_merge([$base], array_values($secondaries));
        $labelsStr = implode(':', $allLabels);

        // Castear propiedades según esquema
        $schema = self::SCHEMA[$base]['properties'];
        $params = [];

        foreach ($schema as $key => $meta) {
            $val = $rawProps[$key] ?? null;
            if ($val === null || $val === '') {
                if ($meta['required']) {
                    return back()->withErrors(["properties.{$key}" => "El campo {$meta['label']} es requerido."]);
                }
                continue;
            }
            $params[$key] = $this->castValue($val, $meta['type']);
        }

        // Construir cláusula de propiedades con parámetros nombrados
        $propPairs  = implode(', ', array_map(fn($k) => "{$k}: \${$k}", array_keys($params)));
        $cypher     = "CREATE (n:{$labelsStr} {{$propPairs}}) RETURN elementId(n) AS id";

        try {
            $this->neo4j->run($cypher, $params);
        } catch (\Throwable $e) {
            return back()->withErrors(['neo4j' => 'Error al crear el nodo: ' . $e->getMessage()]);
        }

        return redirect()->route('nodes.index')->with('success', 'Nodo creado exitosamente.');
    }

    private function formatRow(mixed $row): array
    {
        $rawLabels = $row->get('lbls');
        $rawProps  = $row->get('props');

        return [
            'id'     => (string) $row->get('id'),
            'labels' => $rawLabels instanceof CypherList ? $rawLabels->toArray() : (array) $rawLabels,
            'props'  => $this->convertCypherMap($rawProps),
        ];
    }

    private function convertCypherMap(mixed $map): array
    {
        if (!($map instanceof CypherMap)) {
            return [];
        }
        $out = [];
        foreach ($map as $k => $v) {
            $out[$k] = $this->convertCypherValue($v);
        }
        return $out;
    }

    private function convertCypherValue(mixed $v): mixed
    {
        if ($v instanceof CypherList) {
            return array_map([$this, 'convertCypherValue'], $v->toArray());
        }
        if ($v instanceof CypherMap) {
            return $this->convertCypherMap($v);
        }
        return $v;
    }

    private function castValue(mixed $val, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $val,
            'float'   => (float) $val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            'list'    => is_array($val)
                            ? $val
                            : array_values(array_filter(array_map('trim', explode(',', (string) $val)))),
            default   => (string) $val,
        };
    }
}
