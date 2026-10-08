-- DreamCalendars index queue (dreamcalendars_dream)
-- Run: scripts/dc-mysql.sh --remote --file scripts/sql/001_index_queue.sql

CREATE TABLE IF NOT EXISTS index_queue (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  url VARCHAR(512) NOT NULL,
  url_hash CHAR(40) NOT NULL,
  type VARCHAR(32) NOT NULL DEFAULT 'page',
  priority TINYINT UNSIGNED NOT NULL DEFAULT 5,
  indexnow_status ENUM('pending','submitted','error','skipped') NOT NULL DEFAULT 'pending',
  yandex_status ENUM('pending','submitted','error','skipped','disabled') NOT NULL DEFAULT 'pending',
  indexnow_at DATETIME NULL,
  yandex_at DATETIME NULL,
  last_error VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_url_hash (url_hash),
  KEY idx_in_pending (indexnow_status, priority, id),
  KEY idx_ya_pending (yandex_status, priority, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS index_submissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  engine VARCHAR(32) NOT NULL,
  scope VARCHAR(32) NOT NULL DEFAULT 'cron',
  url_count INT UNSIGNED NOT NULL DEFAULT 0,
  ok_count INT UNSIGNED NOT NULL DEFAULT 0,
  http_status INT NULL,
  detail VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_engine_day (engine, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS index_meta (
  meta_key VARCHAR(64) NOT NULL PRIMARY KEY,
  meta_value VARCHAR(255) NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
