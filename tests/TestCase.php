<?php

namespace Tests;

use App\Support\Database;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case wired to fa_db_test (see tests/bootstrap.php). Each test
 * inserts its own isolated fixtures and tracks them for teardown, so tests
 * never depend on - or corrupt - the shared seed data in fa_db_test.
 */
abstract class TestCase extends BaseTestCase
{
    protected PDO $pdo;

    /** @var array<string, array<int, int>> table => list of primary key values to delete in tearDown */
    private array $inserted = [];

    protected function setUp(): void
    {
        $this->pdo = Database::connection();
    }

    protected function tearDown(): void
    {
        // Delete children before parents to respect FK-like relationships.
        $order = ['notifications', 'match_status_log', 'appeal_cases', 'ai_discipline_cases', 'cards', 'match_day_reports', 'weekly_fixtures', 'match', 'team_members', 'team'];
        foreach ($order as $table) {
            foreach ($this->inserted[$table] ?? [] as $id) {
                $column = $this->primaryKeyFor($table);
                $this->pdo->prepare("DELETE FROM `$table` WHERE `$column` = ?")->execute([$id]);
            }
        }
    }

    private function primaryKeyFor(string $table): string
    {
        return match ($table) {
            'team' => 'team_id',
            'team_members' => 'member_id',
            'match' => 'id',
            'weekly_fixtures' => 'fixture_id',
            'cards' => 'card_id',
            'ai_discipline_cases' => 'case_id',
            'match_status_log' => 'id',
            'notifications' => 'id',
            'match_day_reports' => 'report_id',
            'appeal_cases' => 'appeal_id',
            default => 'id',
        };
    }

    protected function track(string $table, int $id): int
    {
        $this->inserted[$table][] = $id;
        return $id;
    }

    protected function createTeam(string $name): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO team (name, logon, stadium, username, password, email) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $unique = uniqid('team_', true);
        $stmt->execute([$name, '', 'Test Stadium', $unique, $unique, 'team-' . $unique . '@example.test']);
        return $this->track('team', (int) $this->pdo->lastInsertId());
    }

    protected function createPlayer(int $teamId, string $fname = 'Test', string $lname = 'Player', array $overrides = []): int
    {
        $data = array_merge([
            'fname' => $fname,
            'lname' => $lname,
            'number' => 10,
            'role_in_team' => 'player',
            'position' => 'Forward',
            'team' => (string) $teamId,
            'post' => null,
            'email' => null,
            'is_captain' => 0,
        ], $overrides);

        $stmt = $this->pdo->prepare(
            'INSERT INTO team_members (fname, lname, number, role_in_team, position, team, post, email, is_captain, yellow, double_yellow, red)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0)'
        );
        $stmt->execute([
            $data['fname'], $data['lname'], $data['number'], $data['role_in_team'],
            $data['position'], $data['team'], $data['post'], $data['email'], $data['is_captain'],
        ]);
        return $this->track('team_members', (int) $this->pdo->lastInsertId());
    }

    protected function createMatch(int $team1Id, int $team2Id, string $status = 'live', array $overrides = []): int
    {
        $data = array_merge([
            'week' => 1,
            'stadium' => 'Test Stadium',
            'match_date' => date('Y-m-d'),
            'match_time' => '15:00:00',
            'season' => '2026-test',
            'team1_goal' => null,
            'team2_goal' => null,
        ], $overrides);

        $stmt = $this->pdo->prepare(
            'INSERT INTO `match` (team1_id, team2_id, week, stadium, match_date, match_time, season, status, team1_goal, team2_goal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $team1Id, $team2Id, $data['week'], $data['stadium'], $data['match_date'],
            $data['match_time'], $data['season'], $status, $data['team1_goal'], $data['team2_goal'],
        ]);
        return $this->track('match', (int) $this->pdo->lastInsertId());
    }

    protected function assignReferee(int $matchId, int $refereeId, int $officialId = 0): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO weekly_fixtures (match_id, referee, assistant1, assistant2, official, access_code)
             VALUES (?, ?, 0, 0, ?, ?)'
        );
        $stmt->execute([$matchId, $refereeId, $officialId ?: $refereeId, (string) rand(100000, 999999)]);
        return $this->track('weekly_fixtures', (int) $this->pdo->lastInsertId());
    }

    protected function trackCard(int $cardId): void
    {
        $this->track('cards', $cardId);
    }
}
