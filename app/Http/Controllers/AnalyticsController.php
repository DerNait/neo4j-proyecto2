<?php

namespace App\Http\Controllers;

use App\Services\Neo4jClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Date;
use Laudis\Neo4j\Types\DateTime as Neo4jDateTime;
use Laudis\Neo4j\Types\LocalDateTime;

class AnalyticsController extends Controller
{
    public function __construct(private Neo4jClient $neo4j) {}

    public function index()
    {
        return Inertia::render('Analytics/AnalyticsIndex');
    }

    // Filtrado Colaborativo
    public function recommend(Request $request)
    {
        $username = trim($request->input('username', ''));
        if ($username === '') {
            return response()->json(['error' => 'Username requerido'], 422);
        }

        $safe = addslashes(preg_replace('/[^\p{L}\p{N}_\-\.]/u', '', $username));

        $cypher = "
            MATCH (target:User)
            WHERE toLower(toString(target.username)) CONTAINS toLower('{$safe}')
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
            RETURN g.title           AS title,
                   g.developer       AS developer,
                   g.metacriticScore AS metacriticScore,
                   userOverlap,
                   postCount,
                   round(userOverlap * 3.0 + postCount * 1.0 + metacritic * 0.1, 2) AS score
            ORDER BY score DESC
            LIMIT 10
        ";

        try {
            $result = $this->neo4j->run($cypher, []);
            if ($result->count() === 0) {
                return response()->json(['error' => "Usuario '{$username}' no encontrado o sin recomendaciones"], 404);
            }
            $rows = [];
            foreach ($result as $row) {
                $rows[] = [
                    'title'         => $row->get('title'),
                    'developer'     => $row->get('developer'),
                    'metacriticScore' => $row->get('metacriticScore'),
                    'userOverlap'   => $row->get('userOverlap'),
                    'postCount'     => $row->get('postCount'),
                    'score'         => $row->get('score'),
                ];
            }
            return response()->json($rows);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Score de Influencia
    // Rankea usuarios por: seguidores × 2 + posts × 1.5 + karma × 0.01
    public function influencers()
    {
        $cypher = "
            MATCH (u:User)
            OPTIONAL MATCH (u)<-[:FOLLOWS]-(follower:User)
            OPTIONAL MATCH (u)-[:WROTE]->(p:Post)
            WITH u,
                 count(DISTINCT follower) AS followers,
                 count(DISTINCT p)        AS posts,
                 toInteger(coalesce(u.karmaPoints, 0)) AS karma
            RETURN u.username AS username,
                   followers,
                   posts,
                   karma,
                   round(toFloat(followers) * 2.0 + toFloat(posts) * 1.5 + toFloat(karma) * 0.01, 2) AS influenceScore
            ORDER BY influenceScore DESC
            LIMIT 15
        ";

        try {
            $result = $this->neo4j->run($cypher, []);
            $rows = [];
            foreach ($result as $row) {
                $rows[] = [
                    'username'       => $row->get('username'),
                    'followers'      => $row->get('followers'),
                    'posts'          => $row->get('posts'),
                    'karma'          => $row->get('karma'),
                    'influenceScore' => $row->get('influenceScore'),
                ];
            }
            return response()->json($rows);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Juegos Trending
    // Score = postCount × 5 + totalUpvotes × 0.05 + totalVoters × 1
    public function trending()
    {
        $cypher = "
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
            RETURN g.title           AS title,
                   g.developer       AS developer,
                   g.metacriticScore AS metacriticScore,
                   postCount,
                   totalUpvotes,
                   totalCommenters,
                   round(toFloat(postCount) * 5.0 + toFloat(totalUpvotes) * 0.05 + toFloat(totalVoters) * 1.0, 2) AS trendScore
            ORDER BY trendScore DESC
            LIMIT 10
        ";

        try {
            $result = $this->neo4j->run($cypher, []);
            $rows = [];
            foreach ($result as $row) {
                $rows[] = [
                    'title'           => $row->get('title'),
                    'developer'       => $row->get('developer'),
                    'metacriticScore' => $row->get('metacriticScore'),
                    'postCount'       => $row->get('postCount'),
                    'totalUpvotes'    => $row->get('totalUpvotes'),
                    'totalCommenters' => $row->get('totalCommenters'),
                    'trendScore'      => $row->get('trendScore'),
                ];
            }
            return response()->json($rows);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
