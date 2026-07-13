<?php

namespace App;

use App\Repositories\AppealRepository;
use App\Repositories\CardRepository;
use App\Repositories\MatchRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\TeamMemberRepository;
use App\Services\AiSummaryGeneratorFactory;
use App\Services\AppealDecisionService;
use App\Services\CardService;
use App\Services\CardStatsService;
use App\Services\MailService;
use App\Services\MatchCompletionService;
use App\Services\NotificationService;
use App\Services\PlayerCardBreakdownService;
use App\Services\PlayerStatsService;
use App\Services\StandingsService;
use App\Support\Database;
use PDO;

/**
 * Wires the plain PHP endpoint scripts (no DI container in this codebase)
 * to fully-configured services, so each entry point isn't re-declaring
 * `new CardRepository(...), new CardStatsService(...), ...` boilerplate.
 */
class ServiceFactory
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        return self::$pdo ??= Database::connection();
    }

    public static function cardService(): CardService
    {
        $pdo = self::pdo();
        return new CardService(
            $pdo,
            new CardRepository($pdo),
            new MatchRepository($pdo),
            new TeamMemberRepository($pdo),
            new CardStatsService(new CardRepository($pdo), new TeamMemberRepository($pdo)),
            AiSummaryGeneratorFactory::make(),
            self::notificationService()
        );
    }

    public static function notificationService(): NotificationService
    {
        $pdo = self::pdo();
        return new NotificationService(
            $pdo,
            new NotificationRepository($pdo),
            new TeamMemberRepository($pdo),
            new MailService()
        );
    }

    public static function cardBreakdownService(): PlayerCardBreakdownService
    {
        return new PlayerCardBreakdownService(new CardRepository(self::pdo()));
    }

    public static function matchCompletionService(): MatchCompletionService
    {
        $pdo = self::pdo();
        return new MatchCompletionService($pdo, new MatchRepository($pdo), self::playerStatsService());
    }

    public static function playerStatsService(): PlayerStatsService
    {
        $pdo = self::pdo();
        return new PlayerStatsService(
            $pdo,
            new MatchRepository($pdo),
            new CardStatsService(new CardRepository($pdo), new TeamMemberRepository($pdo))
        );
    }

    public static function standingsService(): StandingsService
    {
        return new StandingsService(self::pdo());
    }

    public static function cardRepository(): CardRepository
    {
        return new CardRepository(self::pdo());
    }

    public static function matchRepository(): MatchRepository
    {
        return new MatchRepository(self::pdo());
    }

    public static function notificationRepository(): NotificationRepository
    {
        return new NotificationRepository(self::pdo());
    }

    public static function teamMemberRepository(): TeamMemberRepository
    {
        return new TeamMemberRepository(self::pdo());
    }

    public static function appealDecisionService(): AppealDecisionService
    {
        return new AppealDecisionService(
            new AppealRepository(self::pdo()),
            AiSummaryGeneratorFactory::make(),
            self::notificationService()
        );
    }
}
