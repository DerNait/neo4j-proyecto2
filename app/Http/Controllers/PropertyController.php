<?php

namespace App\Http\Controllers;

use App\Services\Neo4jClient;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(private Neo4jClient $neo4j) {}

    private function validateKeys(array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', (string) $key)) {
                return "Clave de propiedad inválida: {$key}";
            }
        }
        return null;
    }

    // ── Individual ─────────────────────────────────────────────────────────────

    public function updateNode(Request $request)
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
                'MATCH (n) WHERE elementId(n) = $id SET n += $props',
                ['id' => $id, 'props' => $props]
            );
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeFromNode(Request $request)
    {
        $id   = $request->input('id');
        $keys = $request->input('keys', []);

        if (!$id || empty($keys)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }
        if ($err = $this->validateKeys($keys)) {
            return response()->json(['error' => $err], 422);
        }

        // Keys are whitelist-validated above — safe to embed in Cypher string
        $removes = implode(', ', array_map(fn($k) => "n.{$k}", $keys));

        try {
            $this->neo4j->run(
                "MATCH (n) WHERE elementId(n) = \$id REMOVE {$removes}",
                ['id' => $id]
            );
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ── Bulk ───────────────────────────────────────────────────────────────────

    public function bulkUpdateNodes(Request $request)
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
                'UNWIND $ids AS id MATCH (n) WHERE elementId(n) = id SET n += $props',
                ['ids' => array_values($ids), 'props' => $props]
            );
            return response()->json(['success' => true, 'count' => count($ids)]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkRemoveFromNodes(Request $request)
    {
        $ids  = $request->input('ids', []);
        $keys = $request->input('keys', []);

        if (empty($ids) || empty($keys)) {
            return response()->json(['error' => 'Parámetros inválidos'], 422);
        }
        if ($err = $this->validateKeys($keys)) {
            return response()->json(['error' => $err], 422);
        }

        $removes = implode(', ', array_map(fn($k) => "n.{$k}", $keys));

        try {
            $this->neo4j->run(
                "UNWIND \$ids AS id MATCH (n) WHERE elementId(n) = id REMOVE {$removes}",
                ['ids' => array_values($ids)]
            );
            return response()->json(['success' => true, 'count' => count($ids)]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
