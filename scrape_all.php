<?php

/**
 * KVED-2010 Full Scraper (All Sections A–U)
 * Reads HTML files from html_source/ directory — NO network requests.
 * Run: php scrape_all.php
 */

set_time_limit(0);
define('HTML_ROOT', __DIR__ . '/html_source');
define('JSON_OUTPUT', __DIR__ . '/kved_all_sections.json');
define('LOG_FILE',   __DIR__ . '/scraper_all.log');

$ALL_SECTIONS = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U'];

// --- Logging ---
function log_msg(string $msg): void {
    $line = '[' . date('H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
}

// --- Resolve path like a browser ---
function resolve_path(string $href, string $currentContext): string {
    if (strpos($href, '/') === 0) {
        return $href;
    }
    $contextParts = explode('/', trim($currentContext, '/'));
    array_pop($contextParts);
    $hrefParts = explode('/', $href);
    foreach ($hrefParts as $part) {
        if ($part === '' || $part === '.') continue;
        if ($part === '..') {
            array_pop($contextParts);
        } else {
            $contextParts[] = $part;
        }
    }
    return '/' . implode('/', $contextParts);
}

// --- Read local file ---
function read_local(string $href, string $currentContext = ''): ?string {
    $finalPath = resolve_path($href, $currentContext);
    $path = rtrim(HTML_ROOT, '/\\') . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $finalPath), DIRECTORY_SEPARATOR);
    log_msg("  [READ] $path");
    if (!file_exists($path)) {
        log_msg("  [MISS] Not found: $path");
        return null;
    }
    $content = file_get_contents($path);
    if ($content === false) return null;
    if (mb_detect_encoding($content, ['UTF-8'], true) === false) {
        $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
    }
    return $content;
}

// --- Parse list rows ---
function parse_list_rows(string $html): array {
    $results = [];
    if (!preg_match_all('/<tr[^>]+class="List_Row"[^>]*>(.*?)<\/tr>/si', $html, $matches)) {
        return $results;
    }
    foreach ($matches[1] as $rowHtml) {
        if (!preg_match('/<a[^>]+href="([^"]+)"[^>]*>\s*([^<]+?)\s*<\/a>/si', $rowHtml, $linkMatch)) continue;
        $href = trim($linkMatch[1]);
        $code = trim($linkMatch[2]);
        if (!preg_match('/<p[^>]*>(.*?)<\/p>/si', $rowHtml, $titleMatch)) continue;
        $title = trim(strip_tags($titleMatch[1]));
        $title = preg_replace('/\s+/', ' ', $title);
        $results[] = ['code' => $code, 'title' => $title, 'href' => $href];
    }
    return $results;
}

// --- Extract section title from section HTML ---
function extract_section_title(string $html): string {
    // Try to find it in a List_Row or heading
    if (preg_match('/<p[^>]+class="[^"]*Na[^"]*"[^>]*>(.*?)<\/p>/si', $html, $m)) {
        return trim(strip_tags($m[1]));
    }
    if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $m)) {
        $t = trim(strip_tags($m[1]));
        // Remove prefix like "КВЕД-2010. Секція А. "
        $t = preg_replace('/^КВЕД[^.]*\.\s*Секція\s+\w+\.\s*/ui', '', $t);
        return $t;
    }
    return '';
}

// --- Extract description ---
function extract_description(string $html): ?string {
    if (preg_match('/<td[^>]+class="[^"]*Info_DR[^"]*"[^>]*>(.*?)<\/td>/si', $html, $m)) {
        return clean_description($m[1]);
    }
    $naPos = strpos($html, 'class="Na"');
    if ($naPos === false) return null;
    $tdStart = strrpos(substr($html, 0, $naPos), '<td');
    if ($tdStart === false) return null;
    $depth = 0; $pos = $tdStart; $len = strlen($html);
    while ($pos < $len) {
        if (substr($html, $pos, 3) === '<td') { $depth++; $pos += 3; }
        elseif (substr($html, $pos, 4) === '</td') {
            $depth--;
            if ($depth === 0) {
                $tdEnd = $pos + 5;
                $inner = preg_replace('/^<td[^>]*>|<\/td>$/si', '', trim(substr($html, $tdStart, $tdEnd - $tdStart)));
                return clean_description($inner);
            }
            $pos += 4;
        } else $pos++;
    }
    return null;
}

function clean_description(string $content): ?string {
    $clean = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $content);
    $clean = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $clean);
    $clean = preg_replace('/<!--.*?-->/s', '', $clean);
    $clean = strip_tags($clean, '<ul><ol><li><a><p><br><b><strong><i><em>');
    $clean = preg_replace_callback('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/si', 'rewrite_link', $clean);
    
    // Auto-link plain text codes like 37.00 or 49.50 that are NOT already in <a>
    $clean = auto_link_kved($clean);
    
    $clean = preg_replace('/[ \t]+/', ' ', $clean);
    $clean = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $clean);
    $clean = trim($clean);
    return $clean ?: null;
}

function rewrite_link(array $m): string {
    $href = $m[1]; $text = $m[2];
    if (preg_match('/KVED10_([A-Z0-9_]+)\.html/i', $href, $k)) {
        $parts = explode('_', $k[1]);
        if (count($parts) === 1 && ctype_alpha($parts[0])) $url = '/catalog/section-' . strtoupper($parts[0]);
        elseif (count($parts) === 1)  $url = '/catalog/' . $parts[0];
        elseif (count($parts) === 2 && strlen($parts[1]) === 1) $url = "/catalog/{$parts[0]}/{$parts[0]}-{$parts[1]}";
        elseif (count($parts) === 2 && strlen($parts[1]) >= 2) {
            $g = substr($parts[1], 0, 1);
            $url = "/catalog/{$parts[0]}/{$parts[0]}-{$g}/{$parts[0]}-{$parts[1]}";
        } else return $m[0];
        return '<a href="' . $url . '">' . $text . '</a>';
    }
    return $m[0];
}

function auto_link_kved(string $text): string {
    // This regex avoids matching codes inside <a> tags by using a negative lookbehind/lookahead strategy if possible,
    // but in PHP it's easier to split by <a> tags and only process outside.
    $parts = preg_split('/(<a[^>]*>.*?<\/a>)/si', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as &$part) {
        if (strpos($part, '<a') === 0) continue;
        
        // Match Class (XX.XX)
        $part = preg_replace_callback('/\b(\d{2})\.(\d{2})\b/', function($m) {
            $g = substr($m[2], 0, 1);
            return '<a href="/catalog/' . $m[1] . '/' . $m[1] . '-' . $g . '/' . $m[1] . '-' . $m[2] . '">' . $m[0] . '</a>';
        }, $part);
        
        // Match Group (XX.X)
        $part = preg_replace_callback('/\b(\d{2})\.(\d)\b/', function($m) {
            return '<a href="/catalog/' . $m[1] . '/' . $m[1] . '-' . $m[2] . '">' . $m[0] . '</a>';
        }, $part);

        // Match Division (XX) - ONLY if preceded by "див." or "вид." to avoid matching every 2-digit number
        $part = preg_replace_callback('/(?<=див\.|вид\.|розділу)\s+\b(\d{2})\b/u', function($m) {
             return ' <a href="/catalog/' . $m[1] . '">' . $m[1] . '</a>';
        }, $part);
    }
    return implode('', $parts);
}

// --- Process one section ---
function process_section(string $letter): array {
    $sectionHref = "/KVED2010/SECT/KVED10_{$letter}.html";
    log_msg(">>> SECTION $letter: $sectionHref");
    $sectionHtml = read_local($sectionHref);
    if (!$sectionHtml) {
        log_msg("  [SKIP] Cannot read section $letter");
        return [];
    }

    $title = extract_section_title($sectionHtml);
    $section = [
        'code'        => $letter,
        'title'       => $title,
        'href'        => $sectionHref,
        'description' => extract_description($sectionHtml),
        'divisions'   => [],
    ];

    $divisions = parse_list_rows($sectionHtml);
    log_msg("  Found " . count($divisions) . " divisions.");

    foreach ($divisions as $div) {
        $divResolvedPath = resolve_path($div['href'], $sectionHref);
        log_msg("  > Division {$div['code']}: {$div['title']}");
        $divHtml = read_local($divResolvedPath);

        $divData = [
            'code'        => $div['code'],
            'title'       => $div['title'],
            'href'        => $div['href'],
            'description' => $divHtml ? extract_description($divHtml) : null,
            'groups'      => [],
        ];

        if ($divHtml) {
            $groups = parse_list_rows($divHtml);
            foreach ($groups as $grp) {
                $grpResolvedPath = resolve_path($grp['href'], $divResolvedPath);
                log_msg("    >> Group {$grp['code']}: {$grp['title']}");
                $grpHtml = read_local($grpResolvedPath);

                $grpData = [
                    'code'        => $grp['code'],
                    'title'       => $grp['title'],
                    'href'        => $grp['href'],
                    'description' => $grpHtml ? extract_description($grpHtml) : null,
                    'classes'     => [],
                ];

                if ($grpHtml) {
                    $classes = parse_list_rows($grpHtml);
                    foreach ($classes as $cls) {
                        $clsResolvedPath = resolve_path($cls['href'], $grpResolvedPath);
                        log_msg("      > Class {$cls['code']}: {$cls['title']}");
                        $clsHtml = read_local($clsResolvedPath);
                        $grpData['classes'][] = [
                            'code'        => $cls['code'],
                            'title'       => $cls['title'],
                            'href'        => $cls['href'],
                            'description' => $clsHtml ? extract_description($clsHtml) : null,
                        ];
                    }
                }
                $divData['groups'][] = $grpData;
            }
        }
        $section['divisions'][] = $divData;
    }

    return $section;
}

// --- Main ---
@unlink(LOG_FILE);
log_msg('=== KVED Full Scraper Started ===');
log_msg('HTML root: ' . HTML_ROOT);
log_msg('Sections: ' . implode(', ', $GLOBALS['ALL_SECTIONS']));

$allSections = [];

foreach ($ALL_SECTIONS as $letter) {
    $sectionData = process_section($letter);
    if ($sectionData) {
        $allSections[] = $sectionData;
        // Incremental save after each section
        file_put_contents(JSON_OUTPUT, json_encode($allSections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        log_msg("  [SAVED] Section $letter saved to JSON. Total sections: " . count($allSections));
    }
}

log_msg('=== DONE. Output: ' . JSON_OUTPUT . ' ===');
log_msg('Total sections saved: ' . count($allSections));
