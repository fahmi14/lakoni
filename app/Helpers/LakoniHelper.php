<?php

namespace App\Helpers;

class LakoniHelper
{
    /**
     * Render string data cerdas: otomatis mendeteksi URL murni, teks dengan URL di dalamnya,
     * atau tag <a> HTML menjadi elemen tombol/link pastel yang sangat kalem, rapi, dan menarik.
     *
     * @param mixed $value
     * @param string|null $columnName
     * @return string (HTML safe)
     */
    public static function formatCellContent($value, ?string $columnName = null): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        if (is_array($value)) {
            return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
        }

        $str = trim((string)$value);
        if ($str === '') {
            return '-';
        }

        // 1. Cek jika data adalah tag HTML <a>
        if (stripos($str, '<a ') !== false && preg_match('/href=[\'"]([^\'"]+)[\'"]/i', $str, $matches)) {
            $url = $matches[1];
            preg_match('/>([^<]+)<\/a>/i', $str, $labelMatches);
            $labelText = !empty($labelMatches[1]) ? trim($labelMatches[1]) : 'Buka Tautan';
            return self::renderPastelLinkButton($url, $labelText, $columnName);
        }

        // 2. Cek jika data adalah URL murni (http:// atau https:// atau www.)
        if (preg_match('/^(https?:\/\/[^\s]+)$/i', $str, $matches)) {
            $url = $matches[1];
            $labelText = self::determineLinkLabel($url, $columnName);
            return self::renderPastelLinkButton($url, $labelText, $columnName);
        }

        // 3. Cek jika data diawali www.
        if (preg_match('/^(www\.[^\s]+)$/i', $str, $matches)) {
            $url = 'https://' . $matches[1];
            $labelText = self::determineLinkLabel($url, $columnName);
            return self::renderPastelLinkButton($url, $labelText, $columnName);
        }

        // 4. Cek jika data berisi teks biasa namun memiliki URL di dalamnya (inline URL auto-linking)
        if (preg_match('/(https?:\/\/[^\s<"\'\)]+)/i', $str)) {
            // Jika ada URL di dalam kalimat, ubah URL tersebut menjadi tombol/link pastel inline
            $replaced = preg_replace_callback('/(https?:\/\/[^\s<"\'\)]+)/i', function ($m) use ($columnName) {
                $targetUrl = $m[1];
                $label = self::determineLinkLabel($targetUrl, $columnName, true);
                return self::renderPastelInlineLink($targetUrl, $label);
            }, htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));

            return $replaced;
        }

        // 5. Cek jika ada elemen HTML umum yang valid (span, button, badge)
        if (stripos($str, '<') !== false && stripos($str, '>') !== false) {
            return $str;
        }

        // Default teks biasa
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Tentukan label tombol link berdasarkan konteks URL atau nama kolom
     */
    private static function determineLinkLabel(string $url, ?string $columnName = null, bool $isInline = false): string
    {
        if (!empty($columnName) && stripos($columnName, 'link') === false) {
            $cleanedCol = ucwords(str_replace(['_', '-'], ' ', $columnName));
            return 'Buka ' . $cleanedCol;
        }

        // Deteksi domain khusus (misal FASIH BPS, Google Form, Drive, dll)
        if (stripos($url, 'fasih') !== false) {
            return 'Assignment FASIH';
        }
        if (stripos($url, 'drive.google.com') !== false) {
            return 'Google Drive';
        }
        if (stripos($url, 'docs.google.com/spreadsheets') !== false) {
            return 'Google Sheets';
        }
        if (stripos($url, 'forms.gle') !== false || stripos($url, 'docs.google.com/forms') !== false) {
            return 'Google Form';
        }

        if ($isInline) {
            $host = parse_url($url, PHP_URL_HOST);
            return $host ? $host : 'Buka Tautan';
        }

        return 'Buka Tautan';
    }

    /**
     * Render tombol pastel yang elegan, kalem, clear, dan keren
     */
    public static function renderPastelLinkButton(string $url, string $label, ?string $columnName = null): string
    {
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        // Ikon spesifik
        $icon = 'fa-solid fa-arrow-up-right-from-square';
        if (stripos($url, 'fasih') !== false) {
            $icon = 'fa-solid fa-clipboard-check';
        } elseif (stripos($url, 'drive.google.com') !== false || stripos($url, 'docs.google.com') !== false) {
            $icon = 'fa-solid fa-file-lines';
        }

        return <<<HTML
<a href="{$escapedUrl}" target="_blank" rel="noopener noreferrer" 
   class="btn-pastel-link-modern" 
   title="Buka tautan: {$escapedUrl}">
    <span class="btn-pastel-link-icon"><i class="{$icon}"></i></span>
    <span class="btn-pastel-link-text">{$escapedLabel}</span>
</a>
HTML;
    }

    /**
     * Render tautan inline berdesain pastel kalem untuk URL di dalam teks
     */
    public static function renderPastelInlineLink(string $url, string $label): string
    {
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<a href="{$escapedUrl}" target="_blank" rel="noopener noreferrer" 
   class="badge-pastel-link-inline" 
   title="Buka tautan: {$escapedUrl}">
    <i class="fa-solid fa-link small"></i>
    <span>{$escapedLabel}</span>
</a>
HTML;
    }
}

