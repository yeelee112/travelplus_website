<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use PharData;
use RuntimeException;

/** Reads document content only; never follows links or executes document instructions. */
class TourDocumentImporter
{
    public function fromDocx(string $path): array
    {
        // Phar reads ZIP containers without requiring the optional ext-zip module.
        try {
            $zip = new PharData($path);
            $entry = $zip['word/document.xml'] ?? null;
            if ($entry === null || $entry->getSize() > 8 * 1024 * 1024) {
                throw new RuntimeException('Tài liệu không hợp lệ hoặc nội dung quá lớn.');
            }
            $xml = $entry->getContent();
        } catch (\Throwable $e) {
            throw new RuntimeException('Không đọc được file Word. Hãy chọn file .docx hợp lệ hoặc dán nội dung.', 0, $e);
        }
        return $this->fromXml($xml);
    }

    public function fromXml(string $xml): array
    {
        if (strlen($xml) > 8 * 1024 * 1024 || stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
            throw new RuntimeException('Nội dung XML không được hỗ trợ.');
        }
        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $valid = $doc->loadXML($xml, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (! $valid) {
            throw new RuntimeException('Không đọc được nội dung Word.');
        }
        $xp = new DOMXPath($doc);
        $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xp->registerNamespace('mc', 'http://schemas.openxmlformats.org/markup-compatibility/2006');
        $lines = [];
        // Word stores textboxes twice (Choice/Fallback). Read leaf paragraphs once.
        foreach ($xp->query('//w:body//w:p[not(descendant::w:p) and not(ancestor::mc:Fallback) and not(ancestor::w:del)]') as $paragraph) {
            $line = '';
            foreach ($xp->query('.//w:r[not(ancestor::w:del)]', $paragraph) as $run) {
                $tags = [];
                foreach (['b' => 'strong', 'i' => 'em', 'u' => 'u'] as $property => $tag) {
                    $setting = $xp->query('w:rPr/w:' . $property, $run)->item(0);
                    if ($setting !== null && ! in_array(strtolower($setting->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'val')), ['0', 'false', 'off', 'none'], true)) {
                        $tags[] = $tag;
                    }
                }
                foreach ($xp->query('w:t | w:tab | w:br | w:cr', $run) as $node) {
                    if (in_array($node->localName, ['br', 'cr'], true)) {
                        $line .= "\n";
                        continue;
                    }
                    $fragment = htmlspecialchars($node->localName === 't' ? $node->textContent : ' ', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    foreach (array_reverse($tags) as $tag) {
                        $fragment = '<' . $tag . '>' . $fragment . '</' . $tag . '>';
                    }
                    $line .= $fragment;
                }
            }
            foreach (explode("\n", $line) as $html) {
                $plain = trim(str_replace("\xc2\xa0", ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                if ($plain !== '') {
                    $lines[] = ['text' => $plain, 'html' => '<p>' . $html . '</p>'];
                }
            }
        }
        return $this->parseLines($lines);
    }

    public function fromText(string $text): array
    {
        if (strlen($text) > 500000) {
            throw new RuntimeException('Nội dung tối đa 500.000 byte.');
        }
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', str_replace("\xc2\xa0", ' ', $text))), static fn ($line) => $line !== ''));
        return $this->parseLines(array_map(fn ($line) => ['text' => $line, 'html' => $this->paragraph($line)], $lines));
    }

    /** HTML here is generated from escaped Word runs or escaped plain text only. */
    private function parseLines(array $lines): array
    {
        if ($lines === []) {
            throw new RuntimeException('Tài liệu chưa có nội dung văn bản.');
        }
        $data = ['name_vi' => mb_substr($lines[0]['text'], 0, 250), 'status' => 'draft', 'itinerary_days' => [], 'included_items' => [], 'excluded_items' => [], 'itinerary_import_html' => ''];
        $intro = []; $notes = []; $days = []; $section = 'intro'; $day = null;
        foreach ($lines as $block) {
            $line = $block['text'];
            $compact = preg_replace('/\s+/u', '', mb_strtolower($line));
            if (preg_match('/^(?:ngày|day)\s*(\d{1,3})\s*[:.\-–]?\s*(.*)$/iu', $line, $match)) {
                $day = (int) $match[1];
                $section = 'day';
                $days[$day] ??= ['day_number' => $day, 'title_vi' => $match[2], 'description_vi' => '', 'title_en' => '', 'description_en' => '', 'sort_order' => $day - 1];
                $data['itinerary_import_html'] .= $block['html'];
                continue;
            }
            if (preg_match('/^(?:giátour)?khôngbaogồm[:：]?$/u', $compact)) {
                $section = 'excluded_items';
                continue;
            }
            if (preg_match('/^(?:giátour)?baogồm[:：]?$/u', $compact)) {
                $section = 'included_items';
                continue;
            }
            if (preg_match('/^(giátour[:：]|phụthutrẻem|lưuý[:：]|điềukiện|chínhsách)/u', $compact)) {
                $section = 'notes';
            }
            if ($section === 'day' && $day !== null) {
                $days[$day]['description_vi'] .= $block['html'];
                $data['itinerary_import_html'] .= $block['html'];
            } elseif (in_array($section, ['included_items', 'excluded_items'], true)) {
                $data[$section][] = ['label_vi' => $line, 'label_en' => '', 'sort_order' => count($data[$section])];
            } elseif ($section === 'notes') {
                $notes[] = $line;
            } else {
                $intro[] = $line;
            }
        }
        $data['itinerary_days'] = array_values($days);
        $data['description_vi'] = implode("\n", array_merge(array_values(array_unique($intro)), $notes));
        if (preg_match('/(\d+)\s*(?:N|ngày)\s*(\d+)\s*(?:D|Đ|đêm)/iu', implode(' ', $intro), $duration)) {
            $data['duration_days'] = (int) $duration[1];
            $data['duration_nights'] = (int) $duration[2];
        } elseif ($days !== []) {
            $data['duration_days'] = max(array_keys($days));
            $data['duration_nights'] = max(0, $data['duration_days'] - 1);
        }
        return $data;
    }

    private function paragraph(string $text): string
    {
        return '<p>' . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }
}
