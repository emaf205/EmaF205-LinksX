<?php
/*
EMAF205 Links
Version: 1.0.0
Created by EmaF205 — Milan
Contact: emagumroad@gmail.com
https://linktr.ee/emaf205

Minimal link page powered by three files:
- index.php
- page.txt
- style.txt

MIT License.
Keep this header when redistributing the source code.
*/

error_reporting(0);
ini_set('display_errors', '0');

function emaf_default_page() {
    return array(
        'SITE_TITLE' => 'Links',
        'SITE_DESCRIPTION' => 'A minimal page for selected links.',
        'FOOTER_TEXT' => 'Made with EMAF205 Links'
    );
}

function emaf_default_style() {
    return array(
        'BACKGROUND_COLOR' => '',
        'TEXT_COLOR' => '',
        'FONT' => '',
        'BUTTON_BACKGROUND' => '',
        'BUTTON_TEXT_COLOR' => '',
        'BUTTON_BORDER_COLOR' => ''
    );
}

function emaf_page_keys() {
    return array(
        'SITE_TITLE'=>1,
        'SITE_DESCRIPTION'=>1,
        'FOOTER_TEXT'=>1
    );
}

function emaf_style_keys() {
    return array(
        'BACKGROUND_COLOR'=>1,
        'TEXT_COLOR'=>1,
        'FONT'=>1,
        'BUTTON_BACKGROUND'=>1,
        'BUTTON_TEXT_COLOR'=>1,
        'BUTTON_BORDER_COLOR'=>1
    );
}

function emaf_item_keys() {
    return array(
        'TITLE'=>1,
        'URL'=>1,
        'TARGET'=>1
    );
}

function emaf_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function emaf_safe_url($url, $fallback) {
    $url = trim((string)$url);
    if ($url === '') return $fallback;

    $low = strtolower($url);

    if (strpos($url, "\n") !== false || strpos($url, "\r") !== false) return $fallback;
    if (strpos($low, 'javascript:') === 0) return $fallback;
    if (strpos($low, 'data:') === 0) return $fallback;
    if (strpos($low, 'vbscript:') === 0) return $fallback;

    return $url;
}

function emaf_color($value, $fallback) {
    $value = trim((string)$value);
    if ($value === '') return $fallback;
    if (preg_match('/^#[0-9a-fA-F]{3}$/', $value)) return $value;
    if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) return $value;
    return $fallback;
}

function emaf_target_attr($target) {
    $target = trim((string)$target);
    if (!in_array($target, array('_blank','_self','_parent','_top'), true)) {
        $target = '_blank';
    }

    $rel = $target === '_blank' ? ' rel="noopener"' : '';
    return ' target="'.emaf_e($target).'"'.$rel;
}

function emaf_font_stack($font) {
    $font = strtolower(trim((string)$font));
    if ($font === 'sans' || $font === 'sans-serif') {
        return "Arial, Helvetica, sans-serif";
    }

    return "Georgia, 'Times New Roman', serif";
}

function emaf_parse_page($path) {
    $out = array(
        'config' => array(),
        'items' => array()
    );

    if (!is_file($path) || !is_readable($path)) {
        return $out;
    }

    $raw = file_get_contents($path, false, null, 0, 1048576);
    if ($raw === false) {
        return $out;
    }

    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $global = emaf_page_keys();
    $item_keys = emaf_item_keys();
    $current = -1;

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') continue;
        if (substr($line, 0, 1) === '#') continue;
        if (strpos($line, ':') === false) continue;

        list($key, $value) = explode(':', $line, 2);
        $key = strtoupper(trim($key));
        $value = trim($value);

        if ($key === 'ITEM') {
            $out['items'][] = array('SLUG' => $value);
            $current = count($out['items']) - 1;
            continue;
        }

        if (isset($global[$key])) {
            $out['config'][$key] = $value;
            continue;
        }

        if ($current >= 0 && isset($item_keys[$key])) {
            $out['items'][$current][$key] = $value;
            continue;
        }
    }

    return $out;
}

function emaf_parse_style($path) {
    $out = array();

    if (!is_file($path) || !is_readable($path)) {
        return $out;
    }

    $raw = file_get_contents($path, false, null, 0, 262144);
    if ($raw === false) {
        return $out;
    }

    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $keys = emaf_style_keys();

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') continue;
        if (substr($line, 0, 1) === '#') continue;
        if (strpos($line, ':') === false) continue;

        list($key, $value) = explode(':', $line, 2);
        $key = strtoupper(trim($key));
        $value = trim($value);

        if (isset($keys[$key])) {
            $out[$key] = $value;
        }
    }

    return $out;
}

function emaf_items($parsed) {
    $items = array();

    foreach ($parsed['items'] as $item) {
        $title = isset($item['TITLE']) && trim($item['TITLE']) !== ''
            ? trim($item['TITLE'])
            : (isset($item['SLUG']) ? trim($item['SLUG']) : 'Link');

        $url = isset($item['URL']) ? trim($item['URL']) : '#';
        $target = isset($item['TARGET']) ? trim($item['TARGET']) : '_blank';

        $items[] = array(
            'title' => $title,
            'url' => emaf_safe_url($url, '#'),
            'target' => $target
        );
    }

    if (count($items) === 0) {
        $items[] = array('title'=>'Website', 'url'=>'https://emaf205.com', 'target'=>'_blank');
        $items[] = array('title'=>'Contact', 'url'=>'mailto:emagumroad@gmail.com', 'target'=>'_self');
    }

    return $items;
}

function emaf_css($style) {
    $bg = emaf_color($style['BACKGROUND_COLOR'], '#ffffff');
    $fg = emaf_color($style['TEXT_COLOR'], '#111111');
    $button_bg = emaf_color($style['BUTTON_BACKGROUND'], 'transparent');
    $button_text = emaf_color($style['BUTTON_TEXT_COLOR'], $fg);
    $button_border = emaf_color($style['BUTTON_BORDER_COLOR'], $fg);
    $font = emaf_font_stack($style['FONT']);

    return "
:root{
  --bg:$bg;
  --fg:$fg;
  --muted:color-mix(in srgb, var(--fg) 58%, transparent);
  --button-bg:$button_bg;
  --button-text:$button_text;
  --button-border:$button_border;
}

*{
  box-sizing:border-box;
}

html,
body{
  min-height:100%;
}

body{
  margin:0;
  background:var(--bg);
  color:var(--fg);
  font-family:$font;
  font-weight:400;
  display:flex;
  justify-content:center;
  padding:26px 16px;
}

.page{
  width:min(100%,390px);
  margin:0 auto;
  text-align:center;
}

h1{
  margin:0;
  color:var(--fg);
  font-size:clamp(28px,8vw,34px);
  line-height:1.08;
  letter-spacing:-0.02em;
  font-weight:400;
}

.description{
  margin:10px auto 0;
  max-width:340px;
  color:var(--muted);
  font-size:15px;
  line-height:1.42;
  font-weight:400;
}

.links{
  display:grid;
  gap:10px;
  margin-top:28px;
}

.button{
  width:100%;
  min-height:54px;
  padding:13px 18px;
  border:1px solid var(--button-border);
  border-radius:0;
  background:var(--button-bg);
  color:var(--button-text);
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  text-decoration:none;
  text-transform:uppercase;
  letter-spacing:.08em;
  font-size:13px;
  line-height:1.15;
  font-weight:400;
}

.button:hover{
  background:var(--fg);
  color:var(--bg);
}

footer{
  margin-top:26px;
  color:var(--muted);
  font-size:13px;
  line-height:1.4;
  font-weight:400;
}

@media(min-height:760px){
  body{
    padding-top:46px;
  }
}

@media(max-width:360px){
  body{
    padding:22px 12px;
  }

  .button{
    min-height:52px;
    font-size:12px;
  }
}
";
}

$page_parsed = emaf_parse_page(__DIR__ . '/page.txt');
$page = array_merge(emaf_default_page(), $page_parsed['config']);

$style_parsed = emaf_parse_style(__DIR__ . '/style.txt');
$style = array_merge(emaf_default_style(), $style_parsed);

$items = emaf_items($page_parsed);

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo emaf_e($page['SITE_TITLE']); ?></title>
<meta name="description" content="<?php echo emaf_e($page['SITE_DESCRIPTION']); ?>">
<style><?php echo emaf_css($style); ?></style>
</head>
<body>
<main class="page">
  <h1><?php echo emaf_e($page['SITE_TITLE']); ?></h1>

  <?php if (trim($page['SITE_DESCRIPTION']) !== ''): ?>
  <p class="description"><?php echo emaf_e($page['SITE_DESCRIPTION']); ?></p>
  <?php endif; ?>

  <nav class="links" aria-label="Links">
    <?php foreach ($items as $item): ?>
    <a class="button" href="<?php echo emaf_e($item['url']); ?>"<?php echo emaf_target_attr($item['target']); ?>>
      <?php echo emaf_e($item['title']); ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <?php if (trim($page['FOOTER_TEXT']) !== ''): ?>
  <footer><?php echo emaf_e($page['FOOTER_TEXT']); ?></footer>
  <?php endif; ?>
</main>
</body>
</html>
