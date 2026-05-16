<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(string $path): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA journal_mode=WAL');

        self::migrate($db);

        self::$instance = $db;
        return $db;
    }

    private static function migrate(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS games (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                title       TEXT    NOT NULL,
                genre       TEXT    NOT NULL,
                developer   TEXT    NOT NULL,
                year        INTEGER NOT NULL,
                price       INTEGER NOT NULL DEFAULT 0,
                old_price   INTEGER,
                rating      REAL    NOT NULL DEFAULT 0,
                emoji       TEXT,
                logo        TEXT,
                discount    INTEGER,
                platform    TEXT,
                players     TEXT,
                description TEXT,
                tags        TEXT,
                created_at  TEXT    DEFAULT (datetime('now')),
                updated_at  TEXT    DEFAULT (datetime('now'))
            );

            CREATE INDEX IF NOT EXISTS idx_games_genre  ON games(genre);
            CREATE INDEX IF NOT EXISTS idx_games_rating ON games(rating DESC);
            CREATE INDEX IF NOT EXISTS idx_games_price  ON games(price);
        SQL);

        $count = (int) $db->query('SELECT COUNT(*) FROM games')->fetchColumn();
        if ($count === 0) {
            self::seed($db);
        }
    }

    private static function seed(PDO $db): void
    {
        $games = [
            ['Elden Ring',            'rpg',       'FromSoftware',        2022, 1899, 2499, 4.9, '⚔️',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/capsule_616x353.jpg', -24,  'PC / PS5 / Xbox Series',    '1 гравець + кооп',    'Масштабна RPG від FromSoftware зі сценарієм Джорджа Р. Р. Мартіна.', ['Відкритий світ','Складно','Лор','Соульслайк']],
            ['Cyberpunk 2077',        'rpg',       'CD Projekt RED',      2020, 1299, null, 4.7, '🤖',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/capsule_616x353.jpg', null, 'PC / PS5 / Xbox Series',    '1 гравець',           'Night City — найнебезпечніше місто майбутнього.', ['Кіберпанк','Відкритий світ','Вибори','Immersive Sim']],
            ['God of War',            'action',    'Santa Monica Studio', 2018, 999,  1499, 4.9, '🪓',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1593500/capsule_616x353.jpg', -33,  'PC / PS4 / PS5',            '1 гравець',           'Кратос та його син Атрей мандрують суворими скандинавськими землями.', ['Сюжет','Скандинавська','Кінематограф','Атмосфера']],
            ['The Witcher 3',         'rpg',       'CD Projekt RED',      2015, 599,  1199, 4.9, '🐺',  'https://cdn.cloudflare.steamstatic.com/steam/apps/292030/capsule_616x353.jpg',  -50,  'PC / PS4 / Xbox / Switch',  '1 гравець',           'Геральт з Рівії шукає прийомну доньку Цирі серед воєн та чудовиськ.', ['Класика','Відьмак','Великі DLC','Відкритий світ']],
            ['Red Dead Redemption 2', 'adventure', 'Rockstar Games',      2018, 1499, null, 4.8, '🤠',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1174180/capsule_616x353.jpg', null, 'PC / PS4 / Xbox One',       '1 гравець + онлайн', 'Артур Морган та банда Ван дер Лінде виживають на заході Дикого Заходу.', ['Вестерн','Наратив','Деталізація','Онлайн']],
            ['EA Sports FC 25',       'sports',    'EA Sports',           2024, 2199, 2499, 4.2, '⚽',  'https://cdn.cloudflare.steamstatic.com/steam/apps/2669320/capsule_616x353.jpg', -12,  'PC / PS5 / Xbox / Switch',  '1–4 гравці + онлайн','Новий сезон найпопулярнішого футбольного симулятора.', ['Футбол','Ultimate Team','Онлайн','Щорічний']],
            ['Hades II',              'action',    'Supergiant Games',    2024, 899,  null, 4.8, '🔱',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1145350/capsule_616x353.jpg', null, 'PC (Early Access)',          '1 гравець',           'Богиня Мелінея пробивається з підземного світу, щоб зупинити Хроноса.', ['Roguelike','Early Access','Грецька','Реіграбельність']],
            ['StarCraft II',          'strategy',  'Blizzard',            2010, 0,    999,  4.7, '🚀',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1466860/capsule_616x353.jpg', -100, 'PC / Mac',                  '1 гравець + онлайн', 'Легенда жанру RTS тепер безкоштовна.', ['Безкоштовно','Кіберспорт','RTS','Класика']],
            ["Baldur's Gate 3",       'rpg',       'Larian Studios',      2023, 1799, null, 5.0, '🎲',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1086940/capsule_616x353.jpg', null, 'PC / PS5 / Xbox',           '1–4 гравці',          'Революція жанру покрокових RPG. Переможець GOTY 2023.', ['D&D','Кооп','GOTY 2023','Вибір']],
            ['Half-Life: Alyx',       'action',    'Valve',               2020, 1299, 1599, 4.8, '🔬',  'https://cdn.cloudflare.steamstatic.com/steam/apps/546560/capsule_616x353.jpg',  -19,  "PC VR (обов'язково)",       '1 гравець',           'Найкраща VR-гра у світі від Valve.', ['VR','Half-Life','Атмосфера','Valve']],
            ['Age of Empires IV',     'strategy',  'Relic Entertainment', 2021, 1099, null, 4.5, '🏰',  'https://cdn.cloudflare.steamstatic.com/steam/apps/1466860/capsule_616x353.jpg', null, 'PC / Xbox',                 '1 гравець + онлайн', 'Четверте пришестя легендарної серії стратегій.', ['Середньовіччя','RTS','Цивілізації','Game Pass']],
            ['Spider-Man 2',          'action',    'Insomniac Games',     2023, 2499, null, 4.8, '🕷️', 'https://cdn.cloudflare.steamstatic.com/steam/apps/2119490/capsule_616x353.jpg', null, 'PC / PS5',                   '1 гравець',           'Пітер Паркер та Майлз Моралес разом захищають Нью-Йорк.', ['Marvel','Паркур','Відкритий світ','PS5']],
        ];

        $stmt = $db->prepare(<<<SQL
            INSERT INTO games (title,genre,developer,year,price,old_price,rating,emoji,logo,discount,platform,players,description,tags)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        SQL);

        foreach ($games as $g) {
            $stmt->execute([
                $g[0], $g[1], $g[2], $g[3], $g[4], $g[5], $g[6],
                $g[7], $g[8], $g[9], $g[10], $g[11], $g[12],
                json_encode($g[13], JSON_UNESCAPED_UNICODE),
            ]);
        }
    }
}
