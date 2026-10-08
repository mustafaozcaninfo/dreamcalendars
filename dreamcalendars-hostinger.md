# dreamcalendars.com — Hostinger (Hestia VPS)

## Ortak yapılandırma ve skill

- Tüm SSH, veritabanı, Cloudflare bilgileri ve yerel/sunucu yolları: `.env`
- `.cursor/deploy.local.env`, mevcut scriptler için `.env` dosyasını yükler.
- Skill: `/Users/mustafa/.codex/skills/dreamcalendars-hostinger/SKILL.md`
- GitHub: https://github.com/mustafaozcaninfo/dreamcalendars — `main`, public; giriş `gh` ve OS keychain üzerinden, token `.env` içine yazılmaz.
- Paylaşılabilir ayarlar: `.env.example`; gizli değer içeren eski PHP dosyaları yerelde kalır, `.php.example` sürümleri GitHub'da tutulur.
- Commit/push öncesi: `python3 scripts/dc-git-secret-check.py`; GitHub push, Hostinger deploy yapmaz.
- Eşleştirme: `public_html/<yol>` → `/home/dreamcalendars/web/dreamcalendars.com/public_html/<yol>`
- Özel yapılandırma: `config/<yol>` → `/home/dreamcalendars/web/dreamcalendars.com/config/<yol>`
- 2026-10-08: SSH anahtarıyla root girişi, site dizinleri ve Cloudflare zone/DNS erişimi doğrulandı. Dosya eşitliği ve DB girişi test edilmedi.

## Sunucu

| Alan | Değer |
|------|--------|
| IP | `2.25.147.42` |
| Panel | HestiaCP (hostname: `web.typecalendar.com`) |
| Site kullanıcısı | `dreamcalendars` |
| Web kökü | `/home/dreamcalendars/web/dreamcalendars.com/public_html` |
| Domain | https://www.dreamcalendars.com |
| Diğer domainler (aynı kullanıcı) | `dreamkalender.de`, `itscalendar.com` |

## Yerel proje

| | |
|---|---|
| Repo kökü | `/Users/mustafa/Documents/Workspace/DreamCalendars` |
| PHP kaynak | `public_html/` |
| Deploy config | `.cursor/deploy.local.env` |
| Deploy scriptleri | `scripts/dc-deploy.sh`, `scripts/dc-pull.sh`, `scripts/dc-cf-purge.sh` |

```bash
cd /Users/mustafa/Documents/Workspace/DreamCalendars
scripts/dc-deploy-doctor.sh          # SSH / path kontrolü
scripts/dc-deploy.sh --confirm footer.php   # production deploy
scripts/dc-cf-purge.sh               # Cloudflare cache purge
scripts/dc-pull.sh                   # sunucudan çek
```

## SSH (root — tam sunucu erişimi)

```bash
ssh root@2.25.147.42
```

**Şifre:** `.env` → `SSHPASS`

Anahtar deploy: `.cursor/cloud_deploy_key` (`DEPLOY_SSH_KEY` in `deploy.local.env`)

## SFTP / rsync

`dreamcalendars` kullanıcısı shell kapalı (`nologin`). Transfer **root SSH** veya deploy scriptleri ile:

```bash
# Yerel → sunucu (deploy)
scripts/dc-deploy.sh --confirm index.php footer.php

# Sunucu → yerel (indirme)
scripts/dc-pull.sh

# Manuel rsync
rsync -avz --exclude 'php_errors.log' --exclude '*.log' \
  -e ssh ./public_html/ root@2.25.147.42:/home/dreamcalendars/web/dreamcalendars.com/public_html/
```

## Veritabanı (MySQL)

Ana site (`connection.php`):

| Alan | Değer |
|------|--------|
| Host | `localhost` (sunucuda) / SSH tüneli ile yerelde `127.0.0.1:3307` |
| Veritabanı | `dreamcalendars_dream` |
| Kullanıcı | `dreamcalendars_dream` |
| Şifre | `.env` → `DB_PASSWORD` |

SaveCalendars (`public_html/SaveCalendars/app/db_connection.php`):

| Alan | Değer |
|------|--------|
| Veritabanı | `dreamcalendars_app` |
| Kullanıcı | `dreamcalendars_app` |
| Şifre | `.env` → `DB_APP_PASSWORD` |

Diğer DB'ler (aynı kullanıcı adı = DB adı): `dreamcalendars_web`, `dreamcalendars_default`, `dreamcalendars_de`, `dreamcalendars_hit`, `dreamcalendars_rapid`, `dreamcalendars_x1`, `dreamcalendars_x2`

Yerelden DB erişimi (SSH tüneli):

```bash
ssh -L 3307:localhost:3306 root@2.25.147.42 -N
# mysql -h 127.0.0.1 -P 3307 -u dreamcalendars_dream -p dreamcalendars_dream
```

Yerel önizleme:

```bash
cd public_html && php -S localhost:8080   # tünel açıkken connection.php localhost kullanır
```

## Cloudflare (cache purge)

| Alan | Değer |
|------|--------|
| E-posta (hesap) | `info@dreamcalendars.com` |
| **Global API Key** | `.env` → `CF_API_KEY` |
| Zone ID (`dreamcalendars.com`) | `cc28022618d7bc662beb91e81add7994` |

> **Global API Key** — Bearer/API Token değil. `scripts/dc-cf-purge.sh` `X-Auth-Email` + `X-Auth-Key` kullanır.  
> Ortak kaynak: `.env` → `CF_EMAIL`, `CF_API_KEY`, `CF_ZONE_ID`

```bash
scripts/dc-cf-purge.sh              # tüm zone purge
scripts/dc-cf-purge.sh --urls / /css/
```

## PHP

| | |
|--|--|
| Sunucu | PHP 8.3-fpm — deploy sonrası otomatik reload |
| Yerel (Mac) | Homebrew PHP — `php -l` deploy öncesi |

## Hızlı komutlar

```bash
# Sunucuda site logları
ssh root@2.25.147.42 "tail -50 /home/dreamcalendars/web/dreamcalendars.com/public_html/php_errors.log"

# Nginx/Apache vhost
ssh root@2.25.147.42 "ls /home/dreamcalendars/conf/web/dreamcalendars.com/"

# Sunucuda hızlı tar (yedek / indirme)
ssh root@2.25.147.42 'tar -czf /tmp/dc-public_html.tar.gz -C /home/dreamcalendars/web/dreamcalendars.com --exclude=php_errors.log public_html'
scp root@2.25.147.42:/tmp/dc-public_html.tar.gz ./
```

## Search engine indexing (IndexNow · Bing · Yandex)

| Engine | Method | Status |
|--------|--------|--------|
| **IndexNow** | `api.indexnow.org` + `bing.com/indexnow` + `yandex.com/indexnow` | ✅ key deployed |
| **Yahoo** | Via IndexNow/Bing network (no separate API) | ✅ |
| **Bing Webmaster** | `SubmitFeed` sitemap API | ✅ key configured |
| **Yandex Webmaster** | Sitemap + recrawl API (~145/gün) | ✅ verified |

**IndexNow key:** `0375ae8c5d4f42e8be96869ee48a53a2`  
**Key file:** https://www.dreamcalendars.com/0375ae8c5d4f42e8be96869ee48a53a2.txt

**Yandex doğrulama:** https://www.dreamcalendars.com/yandex_502d7680dfa18aa6.html — Yandex Webmaster'da "Verify" tıkla.

```bash
scripts/dc-index-setup.sh     # deploy + migrate + first run
scripts/dc-index-run.sh       # manual cron via SSH
```

**Yandex OAuth (recrawl API için — opsiyonel):**  
Yandex Webmaster → site ekle → doğrulama tamamlandıktan sonra [OAuth uygulaması](https://oauth.yandex.com/) oluştur → token al → `config/indexing.local.php` içine `yandex_oauth_token`, `yandex_user_id`, `yandex_host_id` ekle. OAuth olmadan da `yandex.com/indexnow` endpoint'i çalışıyor.

Server crontab (dreamcalendars user or root):

```bash
0 */2 * * * php /home/dreamcalendars/web/dreamcalendars.com/public_html/cron/index-ping.php >> /home/dreamcalendars/web/dreamcalendars.com/public_html/logs/index-ping.log 2>&1
```

Optional keys in `config/indexing.local.php` on server (`/home/dreamcalendars/web/dreamcalendars.com/config/`):

```php
return [
    'bing_api_key' => '...',  // Bing Webmaster Tools → Settings → API Access
    'yandex_enabled' => true,
    'yandex_oauth_token' => '...',
    'yandex_user_id' => '...',
    'yandex_host_id' => '...',
];
```

After publish hook (PHP):

```php
require_once __DIR__ . '/includes/indexing/runner.php';
dc_index_notify_urls(['https://www.dreamcalendars.com/article/my-slug']);
```


- **Staging yok** — tek hedef production; her deploy `--confirm` gerektirir
- `COPYFILE_DISABLE=1` (macOS `._` dosyaları yüklenmez)
- PHP değişikliğinde `php -l` + `php8.3-fpm` reload
- HTML/CSS değişikliğinde `scripts/dc-cf-purge.sh`
