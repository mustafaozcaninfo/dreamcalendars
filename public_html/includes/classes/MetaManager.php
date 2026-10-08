<?php

declare(strict_types=1);

require_once __DIR__ . '/../seo_config.php';

/**
 * Centralized meta / OG / Twitter / JSON-LD @graph output for DreamCalendars.
 */
class MetaManager
{
    private static ?self $instance = null;

    private string $title = 'Dream Calendars';
    private string $description = '';
    private string $canonical = '';
    private string $robots = 'follow, index';
    private string $ogType = 'website';
    private string $ogImage = '';
    private string $ogImageAlt = '';
    private string $ogSection = 'Home';
    private string $twitterCard = 'summary_large_image';
    private bool $organizationAdded = false;
    private bool $websiteAdded = false;

    /** @var list<array<string, mixed>> */
    private array $schemaNodes = [];

    /** @var list<array{label: string, url?: string, current?: bool}> */
    private array $uiBreadcrumbs = [];

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setCanonical(string $url): self
    {
        $this->canonical = $url;

        return $this;
    }

    public function setRobots(string $robots): self
    {
        $this->robots = $robots;

        return $this;
    }

    public function setOgType(string $type): self
    {
        $this->ogType = $type;

        return $this;
    }

    public function setOgImage(string $url): self
    {
        $this->ogImage = $url;

        return $this;
    }

    public function setOgImageAlt(string $alt): self
    {
        $this->ogImageAlt = $alt;

        return $this;
    }

    public function setOgSection(string $section): self
    {
        $this->ogSection = $section;

        return $this;
    }

    public function setTwitterCard(string $card): self
    {
        $this->twitterCard = $card;

        return $this;
    }

    /** @param array<string, mixed> $node */
    public function addSchemaNode(array $node): self
    {
        unset($node['@context']);
        $this->schemaNodes[] = $node;

        return $this;
    }

    /** @return list<array{label: string, url?: string, current?: bool}> */
    public function uiBreadcrumbs(): array
    {
        return $this->uiBreadcrumbs;
    }

    /** @param list<array{label: string, url?: string, current?: bool}> $crumbs */
    public function setUiBreadcrumbs(array $crumbs): self
    {
        $this->uiBreadcrumbs = $crumbs;

        return $this;
    }

    private function siteBase(): string
    {
        global $site;

        return rtrim((string) ($site ?? 'https://www.dreamcalendars.com/'), '/');
    }

    private function cdnBase(): string
    {
        global $cdn;

        return (string) ($cdn ?? 'https://cdn.dreamcalendars.com/');
    }

    private function resolveOgImage(string $webPath, string $fallback): string
    {
        $local = dirname(__DIR__, 2) . $webPath;
        if (is_readable($local)) {
            return $this->siteBase() . $webPath;
        }

        return $fallback;
    }

    private function yearlyCalendarOg(int $year): string
    {
        return $this->resolveOgImage(
            '/printable/yearly/' . $year . '/' . $year . '-Calendar.jpg',
            $this->cdnBase() . 'images/main_page.png'
        );
    }

    private function brand(): string
    {
        return 'Dream Calendars';
    }

    /** @param array<string, string> $vars */
    private function applySeoHint(string $routeKey, array $vars = []): void
    {
        $hint = dc_seo_hint($routeKey, array_merge(dc_seo_default_vars(), $vars));
        if (!empty($hint['title'])) {
            $this->title = $hint['title'];
        }
        if (!empty($hint['description'])) {
            $this->description = $hint['description'];
        }
    }

    private function applySeoFallback(string $title, string $description): void
    {
        if ($this->title === '' || $this->title === 'Dream Calendars') {
            $this->title = $title;
        }
        if ($this->description === '') {
            $this->description = $description;
        }
    }

    public function ensureOgImage(): self
    {
        if ($this->ogImage === '') {
            $this->ogImage = $this->cdnBase() . 'images/main_page.png';
        }
        if ($this->ogImageAlt === '') {
            $this->ogImageAlt = $this->title !== '' ? $this->title : $this->brand();
        }

        return $this;
    }

    /** @param list<array{name: string, item: string}> $crumbs */
    private function addBreadcrumbList(array $crumbs): void
    {
        $elements = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $this->siteBase() . '/'],
        ];
        $position = 2;
        foreach ($crumbs as $crumb) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
                'item' => $crumb['item'],
            ];
        }
        $this->addSchemaNode([
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ]);
    }

    /** @param list<array{label: string, url?: string, current?: bool}> $uiCrumbs */
  private function syncBreadcrumbs(array $uiCrumbs, array $schemaCrumbs): void
    {
        $this->uiBreadcrumbs = $uiCrumbs;
        $this->addBreadcrumbList($schemaCrumbs);
    }

    private function addWebPage(?string $name = null): void
    {
        $node = [
            '@type' => 'WebPage',
            'name' => $name ?? $this->title,
            'url' => $this->canonical,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => $this->siteBase() . '/#website',
            ],
        ];
        if ($this->description !== '') {
            $node['description'] = $this->description;
        }
        if ($this->ogImage !== '') {
            $node['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $this->ogImage,
            ];
        }
        $this->addSchemaNode($node);
    }

    private function addOrganization(): void
    {
        if ($this->organizationAdded) {
            return;
        }
        $this->organizationAdded = true;
        $this->addSchemaNode([
            '@type' => 'Organization',
            '@id' => $this->siteBase() . '/#organization',
            'name' => $this->brand(),
            'url' => $this->siteBase() . '/',
            'sameAs' => ['https://twitter.com/dreamcalendars'],
        ]);
    }

    private function addWebSite(): void
    {
        if ($this->websiteAdded) {
            return;
        }
        $this->websiteAdded = true;
        $this->addSchemaNode([
            '@type' => 'WebSite',
            '@id' => $this->siteBase() . '/#website',
            'url' => $this->siteBase() . '/',
            'name' => $this->brand(),
            'publisher' => ['@id' => $this->siteBase() . '/#organization'],
        ]);
    }

    /** @param list<array{name: string, url: string}> $items */
    public function addItemList(string $name, string $url, array $items): self
    {
        if ($items === []) {
            return $this;
        }
        $elements = [];
        $position = 1;
        foreach ($items as $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'url' => $item['url'],
            ];
        }
        $this->addSchemaNode([
            '@type' => 'ItemList',
            'name' => $name,
            'url' => $url,
            'numberOfItems' => count($elements),
            'itemListElement' => $elements,
        ]);

        return $this;
    }

    /** @param list<array{question: string, answer: string}> $questions */
    public function addFaqPage(array $questions): self
    {
        if ($questions === []) {
            return $this;
        }
        $entities = [];
        foreach ($questions as $qa) {
            $entities[] = [
                '@type' => 'Question',
                'name' => $qa['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $qa['answer'],
                ],
            ];
        }
        $this->addSchemaNode([
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ]);

        return $this;
    }

    private function addEvent(string $name, string $startDate, string $url): void
    {
        $this->addSchemaNode([
            '@type' => 'Event',
            'name' => $name,
            'startDate' => $startDate,
            'url' => $url,
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => [
                '@type' => 'Country',
                'name' => 'United States',
            ],
        ]);
    }

    private function addArticle(string $headline, string $url, ?string $datePublished = null, ?string $dateModified = null): void
    {
        $node = [
            '@type' => 'Article',
            'headline' => $headline,
            'url' => $url,
            'isPartOf' => ['@id' => $this->siteBase() . '/#website'],
            'author' => [
                '@type' => 'Organization',
                'name' => $this->brand(),
                'url' => $this->siteBase() . '/',
            ],
            'publisher' => ['@id' => $this->siteBase() . '/#organization'],
        ];
        if ($this->description !== '') {
            $node['description'] = $this->description;
        }
        if ($this->ogImage !== '') {
            $node['image'] = $this->ogImage;
        }
        if ($datePublished !== null && $datePublished !== '') {
            $node['datePublished'] = $datePublished;
        }
        if ($dateModified !== null && $dateModified !== '') {
            $node['dateModified'] = $dateModified;
        }
        $this->addSchemaNode($node);
    }

    private function finishPage(?string $webPageName = null): void
    {
        $this->addOrganization();
        $this->addWebSite();
        $this->addWebPage($webPageName);
        $this->ensureOgImage();
        $this->ogImageAlt = $this->title;
    }

    public function configureHome(): self
    {
        global $nowyear;
        $year = (int) ($nowyear ?? date('Y'));
        $this->applySeoHint('/', ['year' => (string) $year]);
        $this->canonical = $this->siteBase() . '/';
        $this->ogType = 'website';
        $this->ogSection = 'Home';
        $this->ogImage = $this->cdnBase() . 'images/' . $year . '-calendars.jpg';
        if (!@getimagesize($this->ogImage)) {
            $this->ogImage = $this->cdnBase() . 'images/main_page.png';
        }
        $this->finishPage();

        return $this;
    }

    public function configureMonth(string $monthSlug, int $year, string $monthTitle): self
    {
        $url = $this->siteBase() . '/calendar/' . $monthSlug . '-' . $year;
        $this->applySeoHint('/calendar/{month}-{year}', [
            'month' => $monthSlug,
            'monthTitle' => $monthTitle,
            'year' => (string) $year,
        ]);
        $this->canonical = $url;
        $this->robots = 'follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large';
        $this->ogType = 'article';
        $this->ogSection = 'Calendars';
        $this->ogImage = $this->siteBase() . '/printable/' . $year . '/' . $monthSlug . '/' . $monthTitle . '-' . $year . '-Calendar.jpg';
        $label = $monthTitle . ' ' . $year;
        $this->syncBreadcrumbs(
            [
                ['label' => 'Calendars', 'url' => '/'],
                ['label' => $label, 'current' => true],
            ],
            [
                ['name' => 'Calendars', 'item' => $this->siteBase() . '/'],
                ['name' => $label, 'item' => $url],
            ]
        );
        $this->finishPage();

        return $this;
    }

    public function configureYear(int $year): self
    {
        $url = $this->siteBase() . '/calendar/' . $year;
        $this->applySeoHint('/calendar/{year}', ['year' => (string) $year]);
        $this->canonical = $url;
        $this->robots = 'follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large';
        $this->ogType = 'article';
        $this->ogSection = 'Calendars';
        $this->ogImage = $this->siteBase() . '/printable/yearly/' . $year . '/' . $year . '-Calendar.jpg';
        $label = (string) $year;
        $this->syncBreadcrumbs(
            [
                ['label' => 'Calendars', 'url' => '/'],
                ['label' => $label . ' Calendar', 'current' => true],
            ],
            [
                ['name' => 'Calendars', 'item' => $this->siteBase() . '/'],
                ['name' => $label, 'item' => $url],
            ]
        );
        $this->finishPage();

        return $this;
    }

    public function configureBlankCalendar(): self
    {
        global $nowyear;
        $year = (string) ($nowyear ?? date('Y'));
        $url = $this->siteBase() . '/calendar/blank';
        $this->applySeoHint('/calendar/blank', ['year' => $year]);
        $this->applySeoFallback(
            'Blank Calendar - Printable Blank Calendar ' . $year,
            'Free Blank Calendar ' . $year . '. You can create your activities, work plans and school class schedules very easily and quickly using our free printable blank calendar templates.'
        );
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Calendars';
        $this->ogImage = $this->siteBase() . '/printable/blank/blank-calendar-template-1.jpg';
        $this->addFaqPage([
            [
                'question' => 'What is a blank calendar?',
                'answer' => 'A blank calendar is a dateless printable template with empty day boxes so you can write your own dates, plans, and schedules.',
            ],
            [
                'question' => 'Can I print a blank calendar for free?',
                'answer' => 'Yes. Dream Calendars offers free blank calendar templates you can download and print for personal use.',
            ],
            [
                'question' => 'What can I use a blank calendar for?',
                'answer' => 'Blank calendars are useful for meal planning, habit tracking, birthday countdowns, study schedules, and custom project timelines.',
            ],
        ]);
        $this->syncBreadcrumbs(
            [
                ['label' => 'Calendars', 'url' => '/'],
                ['label' => 'Blank Calendar', 'current' => true],
            ],
            [
                ['name' => 'Calendars', 'item' => $this->siteBase() . '/'],
                ['name' => 'Blank Calendar', 'item' => $url],
            ]
        );
        $this->finishPage();

        return $this;
    }

    /** @param list<array{name: string, url: string}> $listItems */
    public function configureHolidaysYear(int $year, array $listItems = []): self
    {
        $url = $this->siteBase() . '/holidays/' . $year;
        $this->applySeoHint('/holidays/{year}', ['year' => (string) $year]);
        $this->applySeoFallback(
            $year . ' US Holidays - Federal & Observance Dates',
            'Complete list of ' . $year . ' US federal holidays and observances with dates, weekdays, and printable calendars.'
        );
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Holidays';
        $this->ogImage = $this->yearlyCalendarOg($year);
        $label = $year . ' Holidays';
        if ($listItems !== []) {
            $this->addItemList($year . ' US Holidays', $url, $listItems);
        }
        $this->syncBreadcrumbs(
            [['label' => $label, 'current' => true]],
            [['name' => $label, 'item' => $url]]
        );
        $this->finishPage();

        return $this;
    }

    public function configureWhenIs(string $slug, string $holidayName, int $year, ?string $eventDateIso = null): self
    {
        $url = $this->siteBase() . '/when-is/' . $slug;
        $title = $holidayName . ' ' . $year . ': When is ' . $holidayName . ' ' . $year . ' & ' . ($year + 1) . '?';
        $this->applySeoHint('/when-is/{slug}', [
            'holiday' => $holidayName,
            'year' => (string) $year,
            'slug' => $slug,
        ]);
        if ($this->title === 'When Is {holiday}? {year} Date & Calendar' || str_contains($this->title, '{')) {
            $this->title = $title;
        }
        if ($this->description === '' || str_contains($this->description, '{')) {
            $this->description = $holidayName . ' ' . $year . ', ' . $holidayName . ' ' . ($year + 1) . ' and further. Find exact dates, weekdays, and printable calendars.';
        }
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Holidays';
        if ($eventDateIso !== null && $eventDateIso !== '') {
            $ts = strtotime($eventDateIso);
            if ($ts !== false) {
                $monthTitle = date('F', $ts);
                $monthSlug = strtolower($monthTitle);
                $eventYear = (int) date('Y', $ts);
                $this->ogImage = $this->resolveOgImage(
                    '/printable/' . $eventYear . '/' . $monthSlug . '/' . $monthTitle . '-' . $eventYear . '-Calendar.jpg',
                    $this->yearlyCalendarOg($year)
                );
            } else {
                $this->ogImage = $this->yearlyCalendarOg($year);
            }
        } else {
            $this->ogImage = $this->yearlyCalendarOg($year);
        }
        $this->syncBreadcrumbs(
            [
                ['label' => 'Holidays', 'url' => '/holidays/' . $year],
                ['label' => 'When Is ' . $holidayName, 'current' => true],
            ],
            [
                ['name' => 'Holidays', 'item' => $this->siteBase() . '/holidays/' . $year],
                ['name' => 'When Is ' . $holidayName, 'item' => $url],
            ]
        );
        if ($eventDateIso !== null && $eventDateIso !== '') {
            $this->addEvent($holidayName, $eventDateIso, $url);
        }
        $this->finishPage();

        return $this;
    }

    public function configureWeekNumbers(int $year): self
    {
        $url = $this->siteBase() . '/week-numbers/' . $year;
        $this->applySeoHint('/week-numbers/{year}', ['year' => (string) $year]);
        $this->applySeoFallback(
            $year . ' Week Numbers - US & ISO 8601 Calendar',
            'Week numbers for ' . $year . ' in US and ISO 8601 format. Printable week number calendar.'
        );
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Tools';
        $this->ogImage = $this->yearlyCalendarOg($year);
        $this->syncBreadcrumbs(
            [['label' => 'Week Numbers ' . $year, 'current' => true]],
            [['name' => 'Week Numbers ' . $year, 'item' => $url]]
        );
        $this->finishPage();

        return $this;
    }

    public function configureDayNumbers(int $year): self
    {
        $url = $this->siteBase() . '/day-numbers/' . $year;
        $this->applySeoHint('/day-numbers/{year}', ['year' => (string) $year]);
        $this->applySeoFallback(
            $year . ' Day Numbers - Day of Year Calendar',
            'Day of year numbers for ' . $year . '. Printable day-number calendar and reference table.'
        );
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Tools';
        $this->ogImage = $this->yearlyCalendarOg($year);
        $this->syncBreadcrumbs(
            [['label' => 'Day Numbers ' . $year, 'current' => true]],
            [['name' => 'Day Numbers ' . $year, 'item' => $url]]
        );
        $this->finishPage();

        return $this;
    }

    public function configureLeapYears(): self
    {
        $url = $this->siteBase() . '/leap-years';
        $this->applySeoHint('/leap-years', []);
        $this->applySeoFallback(
            'Leap Years - List, Rules & Printable Calendars',
            'Leap year list, rules, and printable calendars. Find which years are leap years and why.'
        );
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Tools';
        $this->ogImage = $this->cdnBase() . 'images/leap-years.png';
        $this->syncBreadcrumbs(
            [['label' => 'Leap Years', 'current' => true]],
            [['name' => 'Leap Years', 'item' => $url]]
        );
        $this->finishPage();

        return $this;
    }

    /** @param list<array{question: string, answer: string}> $faq */
    public function configureArticle(string $slug, string $title, string $description, array $faq = []): self
    {
        $url = $this->siteBase() . '/article/' . $slug;
        $this->title = $title;
        $this->description = $description;
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Articles';
        $templatePath = dirname(__DIR__, 2) . '/templates/articles/' . $slug . '.php';
        $mtime = is_readable($templatePath) ? filemtime($templatePath) : time();
        $dateIso = date('c', $mtime);
        if ($faq !== []) {
            $this->addFaqPage($faq);
        }
        $this->syncBreadcrumbs(
            [
                ['label' => 'Articles', 'url' => '/'],
                ['label' => $title, 'current' => true],
            ],
            [
                ['name' => 'Articles', 'item' => $this->siteBase() . '/'],
                ['name' => $title, 'item' => $url],
            ]
        );
        $this->addArticle($title, $url, $dateIso, $dateIso);
        $this->finishPage();

        return $this;
    }

    public function configureStaticPage(string $slug, string $title, string $description): self
    {
        $url = $this->siteBase() . '/pages/' . $slug;
        $this->applySeoHint('/pages/{slug}', [
            'title' => $title,
            'description' => $description,
            'slug' => $slug,
        ]);
        if ($this->title === '{title} | Dream Calendars' || str_contains($this->title, '{title}')) {
            $this->title = $title . ' | Dream Calendars';
        }
        if ($this->description === '' || $this->description === '{description}') {
            $this->description = $description;
        }
        $this->canonical = $url;
        $this->ogType = 'website';
        $this->ogSection = 'Pages';
        $this->syncBreadcrumbs(
            [['label' => $title, 'current' => true]],
            [['name' => $title, 'item' => $url]]
        );
        $this->finishPage($title);

        return $this;
    }

    public function configureNoindex(string $reason = ''): self
    {
        $this->robots = 'noindex, follow';
        $this->ogType = 'website';
        if ($reason !== '') {
            $this->title = $reason;
        }
        $this->finishPage();

        return $this;
    }

    public function configureDaylightSaving(int $year): self
    {
        $url = $this->siteBase() . '/daylight-saving-time';
        $this->applySeoHint('/daylight-saving-time', ['year' => (string) $year]);
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Tools';
        $this->syncBreadcrumbs(
            [['label' => $year . ' Daylight Saving Time', 'current' => true]],
            [['name' => $year . ' Daylight Saving Time', 'item' => $url]]
        );
        $this->finishPage($year . ' Daylight Saving Time');

        return $this;
    }

    public function configureCurrentMoon(): self
    {
        $url = $this->siteBase() . '/todays-moon-phase';
        $this->applySeoHint('/current-moon', []);
        $this->title = "Current (Today's) Moon Phase - Dream Calendars";
        $this->description = "Today's moon phase, illumination percentage, and lunar calendar. Current moon phase for sky watchers.";
        $this->canonical = $url;
        $this->ogType = 'article';
        $this->ogSection = 'Tools';
        $this->ogImage = $this->cdnBase() . 'images/leap-years.png';
        $this->syncBreadcrumbs(
            [['label' => "Today's Moon Phase", 'current' => true]],
            [['name' => "Today's Moon Phase", 'item' => $url]]
        );
        $this->finishPage("Today's Moon Phase");

        return $this;
    }

    public function render(): string
    {
        $this->ensureOgImage();
        $esc = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $out = [];
        $out[] = '<title>' . $esc($this->title) . '</title>';
        if ($this->description !== '') {
            $out[] = '<meta name="description" content="' . $esc($this->description) . '" />';
        }
        $out[] = '<meta name="robots" content="' . $esc($this->robots) . '" />';
        if ($this->canonical !== '') {
            $out[] = '<link rel="canonical" href="' . $esc($this->canonical) . '" />';
        }

        $out[] = '<meta property="og:locale" content="en_US" />';
        $out[] = '<meta property="og:type" content="' . $esc($this->ogType) . '" />';
        $out[] = '<meta property="og:site_name" content="' . $esc($this->brand()) . '" />';
        $out[] = '<meta property="article:section" content="' . $esc($this->ogSection) . '" />';
        if ($this->canonical !== '') {
            $out[] = '<meta property="og:url" content="' . $esc($this->canonical) . '" />';
        }
        $out[] = '<meta property="og:title" content="' . $esc($this->title) . '" />';
        if ($this->description !== '') {
            $out[] = '<meta property="og:description" content="' . $esc($this->description) . '" />';
        }
        if ($this->ogImage !== '') {
            $imageAlt = $this->ogImageAlt !== '' ? $this->ogImageAlt : $this->title;
            $out[] = '<meta property="og:image" content="' . $esc($this->ogImage) . '" />';
            $out[] = '<meta property="og:image:secure_url" content="' . $esc($this->ogImage) . '" />';
            $out[] = '<meta property="og:image:alt" content="' . $esc($imageAlt) . '" />';
        }

        $out[] = '<meta name="twitter:card" content="' . $esc($this->twitterCard) . '" />';
        $out[] = '<meta name="twitter:site" content="@dreamcalendars" />';
        $out[] = '<meta name="twitter:creator" content="@dreamcalendars" />';
        if ($this->canonical !== '') {
            $out[] = '<meta name="twitter:url" content="' . $esc($this->canonical) . '" />';
        }
        $out[] = '<meta name="twitter:title" content="' . $esc($this->title) . '" />';
        if ($this->description !== '') {
            $out[] = '<meta name="twitter:description" content="' . $esc($this->description) . '" />';
        }
        if ($this->ogImage !== '') {
            $out[] = '<meta name="twitter:image" content="' . $esc($this->ogImage) . '" />';
        }

        if ($this->schemaNodes !== []) {
            $graph = ['@context' => 'https://schema.org', '@graph' => $this->schemaNodes];
            $json = json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json !== false) {
                $out[] = '<script type="application/ld+json">' . $json . '</script>';
            }
        }

        return implode("\n    ", $out);
    }
}

function dc_meta(): MetaManager
{
    return MetaManager::getInstance();
}
