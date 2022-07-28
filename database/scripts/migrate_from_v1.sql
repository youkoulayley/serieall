/*
 * DUMP DATABASE WITH THIS COMMAND
 * mysqldump c1serieall2 -p --skip-set-charset --default-character-set=latin1 > dump_db_v2.sql
 */

/* IMPORT SLOGANS */
INSERT INTO serieall.slogans(id, message, source, url, created_at, updated_at)
  SELECT id, name, source, url, NOW(), NOW() FROM serieall_old.slogans;

/* IMPORT USERS
 * Rename old username
 */
INSERT INTO serieall.users (id, username, user_url, email, password, role, suspended, activated, edito, website, twitter, facebook, created_at, updated_at)
  SELECT
    id,
    CASE WHEN id = 760
    THEN 'chloe1'
    WHEN id = 8577
    THEN 'celia1'
    WHEN id = 1902
    THEN 'lo1'
    ELSE
      login
    END,
     CASE WHEN id = 760
    THEN LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM('chloe1'), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-'))
    WHEN id = 760
    THEN LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM('celia1'), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-'))
    WHEN id = 1902
    THEN LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM('lo1'), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-'))
    ELSE
       LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(login), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-'))
    END,
    email,
    password,
    role,
    suspended,
    1,
    edito,
    website,
    twitter,
    facebook,
    created,
    created
  FROM serieall_old.users;

/* IMPORT GENRES */
INSERT INTO serieall.genres (id, name, genre_url, created_at, updated_at)
    SELECT id, name, LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(name), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-')), NOW(), NOW() FROM serieall_old.genres;

/* IMPORT NATIONALITIES */
INSERT INTO serieall.nationalities(name, nationality_url, created_at, updated_at)
SELECT DISTINCT(nationalite), LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(nationalite), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-')), NOW(), NOW() FROM serieall_old.shows;

/* IMPORT CHANNELS */
INSERT INTO serieall.channels (name, channel_url, created_at, updated_at)
SELECT DISTINCT(name_fr), CASE WHEN name_fr = 'Netflix FR' THEN 'netflix_fr' WHEN name_fr = 'Bravo' THEN 'bravo2' WHEN name_fr = 'Radio-Canada' THEN 'radio-canada2' WHEN name_fr = 'Starz' THEN 'starz2' ELSE LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(name_fr), ':', ''), ')', ''), '(', ''), ',', ''), '\\', ''), '\/', ''), '\"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-')) END , NOW(), NOW()
FROM (SELECT DISTINCT(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(chainefr, ',', numbers.n), ',', -1))) AS name_fr
FROM
  (select 1 n
   union all
   select 2
   union all select 3
   union all
   select 4
   union all select 5) numbers INNER JOIN serieall_old.shows
    on CHAR_LENGTH(serieall_old.shows.chainefr)
       - CHAR_LENGTH(REPLACE(serieall_old.shows.chainefr, ',', '')) >= numbers.n - 1
WHERE TRIM(chainefr) IS NOT NULL AND LENGTH(TRIM(chainefr)) > 0
  AND TRIM(chainefr) NOT LIKE '%/%'
UNION
SELECT DISTINCT(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(chaineus, ',', numbers.n), ',', -1))) AS name_us
FROM
  (select 1 n
   union all
   select 2
   union all select 3
   union all
   select 4
   union all select 5) numbers INNER JOIN serieall_old.shows
    on CHAR_LENGTH(serieall_old.shows.chaineus)
       - CHAR_LENGTH(REPLACE(serieall_old.shows.chaineus, ',', '')) >= numbers.n - 1
WHERE TRIM(chaineus) IS NOT NULL AND LENGTH(TRIM(chaineus)) > 0
  AND TRIM(chaineus) NOT LIKE '%/%') AS us_channel;

/* IMPORT SHOWS */
INSERT INTO serieall.shows (id, thetvdb_id, show_url, name, name_fr, synopsis, format, annee, encours, diffusion_us, diffusion_fr, particularite, moyenne, moyenne_redac, nbnotes, taux_erectile, avis_rentree, created_at, updated_at)
  SELECT
    id,
    CASE WHEN tvdb_id = 0
      THEN NULL
    WHEN id = 339
      THEN 353968
    WHEN id = 396
      THEN NULL
    WHEN id = 538
      THEN 82438
    WHEN id = 239
      THEN 142861
    WHEN id = 659
      THEN NULL
    ELSE tvdb_id END,
    CASE WHEN id = 1923
      THEN 'star-trek-discovery2'
    ELSE
      menu
    END,
    name,
    titrefr,
    synopsis,
    format,
    annee,
    encours,
    diffusionus,
    diffusionfr,
    particularite,
    moyenne,
    moyenne_redac,
    nbnotes,
    te_rentree,
    avis_rentree,
    NOW(),
    NOW()
  FROM serieall_old.shows;

/* IMPORT SHOW_USER */
INSERT INTO serieall.show_user (show_id, user_id, state, message)
  SELECT show_id, user_id, etat, text FROM serieall_old.followedshows;

/* IMPORT GENRE_SHOW */
INSERT INTO serieall.genre_show (genre_id, show_id)
  SELECT genre_id, show_id FROM serieall_old.genres_shows;

/* IMPORT NATIONALITY_SHOW */
INSERT INTO serieall.nationality_show (nationality_id, show_id)
  SELECT nationalities.id, shows.id FROM serieall_old.shows, serieall.nationalities
  WHERE serieall.nationalities.name = serieall_old.shows.nationalite;

/* IMPORT CHANNEL_SHOW */
INSERT INTO serieall.channel_show (channel_id, show_id)
  SELECT channels.id, shows.id FROM serieall_old.shows, serieall.channels WHERE shows.chaineus = channels.name;

INSERT INTO serieall.channel_show (channel_id, show_id)
  SELECT channels.id, shows.id FROM serieall_old.shows, serieall.channels WHERE shows.chainefr = channels.name;

/* IMPORT SEASONS */
INSERT INTO serieall.seasons (id, thetvdb_id, name, ba, moyenne, nbnotes, show_id, created_at, updated_at)
  SELECT
    id,
    tvdb_id,
    name,
    ba,
    moyenne,
    nbnotes,
    show_id,
    NOW(),
    NOW()
  FROM serieall_old.seasons;

/* IMPORT EPISODES
 * ignore incorect 0000-00-00 date
 */
INSERT IGNORE INTO serieall.episodes (id, thetvdb_id, numero, name, name_fr, resume, diffusion_us, diffusion_fr, ba, moyenne, nbnotes, season_id, created_at, updated_at)
  SELECT
    id,
    tvdb_id,
    numero,
    name,
    titrefr,
    resume,
    diffusionus,
    diffusionfr,
    ba,
    moyenne,
    nbnotes,
    season_id,
    NOW(),
    NOW()
  FROM serieall_old.episodes;

/* IMPORT EPISODE_USER
 * Ignore if key constrain does not exists
*/
INSERT IGNORE INTO serieall.episode_user (episode_id, user_id, rate, created_at, updated_at)
    SELECT episode_id, user_id, name, created, created FROM serieall_old.rates;

/* IMPORT COMMENTS */
INSERT IGNORE INTO serieall.comments (id, message, thumb, spoiler, user_id, commentable_id, commentable_type, created_at, updated_at)
  SELECT
    id,
    text,
    CASE WHEN thumb = 'up'
      THEN 1
    WHEN thumb = 'down'
      THEN 3
    WHEN thumb = 'neutral'
      THEN 2
    END,
    spoiler,
    user_id,
    CASE WHEN episode_id = 0
      THEN
        CASE WHEN season_id = 0
          THEN
            CASE WHEN show_id = 0
              THEN article_id
            ELSE
              show_id
            END
        ELSE
          season_id
        END
      ELSE
        episode_id
    END,
    CASE WHEN episode_id = 0
      THEN
        CASE WHEN season_id = 0
          THEN
            CASE WHEN show_id = 0
              THEN 'App\\Models\\Article'
            ELSE
                'App\\Models\\Show'
            END
          ELSE
            'App\\Models\\Season'
        END
      ELSE
        'App\\Models\\Episode'
    END,
    created,
    created
  FROM serieall_old.comments
  WHERE thumb IS NOT NULL;

/* IMPORT REACTIONS */
INSERT INTO serieall.comments (message, user_id, parent_id, commentable_id, commentable_type, created_at, updated_at)
    SELECT text, user_id, comment_id, 0, "", created, created FROM serieall_old.reactions;

/* IMPORT ARTICLES */
INSERT INTO serieall.articles (created_at, updated_at, name, article_url, intro, content, image, source, category_id, published_at, podcast, state, id)
    SELECT created, modified, articles.name, CONCAT(url, '.html'), chapo, text, CONCAT('/images/articles/old/', photo), source, categories.id, modified, CASE WHEN podcast IS NOT NULL THEN 1 ELSE 0 END, etat, articles.id FROM serieall_old.articles, serieall.categories WHERE categories.name = articles.category;

/* IMPORT ARTICLE_USER */
INSERT IGNORE INTO serieall.article_user (article_id, user_id)
    SELECT id, user_id FROM serieall_old.articles;

/* IMPORT ARTICLABLES */
INSERT IGNORE INTO serieall.articlables (article_id, articlable_id, articlable_type)
  SELECT
    id,
    CASE WHEN episode_id = 0
      THEN
        CASE WHEN season_id = 0
          THEN
            CASE WHEN show_id = 0
              THEN 0
            ELSE
              show_id
            END
        ELSE
          season_id
        END
    ELSE
      episode_id
    END,
    CASE WHEN episode_id = 0
      THEN
        CASE WHEN season_id = 0
          THEN
            CASE WHEN show_id = 0
              THEN ''
            ELSE
              'App\\Models\\Show'
            END
        ELSE
          'App\\Models\\Season'
        END
    ELSE
      'App\\Models\\Episode'
    END
  FROM serieall_old.articles;

/* IMPORT ARTICLABLES FOR SEASON OF EPISODES */
INSERT IGNORE INTO serieall.articlables (article_id, articlable_id, articlable_type)
  SELECT
    id,
    CASE WHEN episode_id != 0
      THEN
        season_id
    END
    ,
    CASE WHEN episode_id != 0
      THEN
        'App\\Models\\Season'
    END
  FROM serieall_old.articles;

/* IMPORT ARTICLABLES FOR SHOW OF SEASONS */
INSERT IGNORE INTO serieall.articlables (article_id, articlable_id, articlable_type)
  SELECT
    id,
    CASE WHEN season_id != 0
      THEN
        show_id
    END
    ,
    CASE WHEN season_id != 0
      THEN
        'App\\Models\\Show'
    END
  FROM serieall_old.articles;
