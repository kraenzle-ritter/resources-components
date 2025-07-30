<?php

namespace KraenzleRitter\ResourcesComponents\Helpers;

class TextHelper
{
    /**
     * Extracts the first sentence from a text, considering common exceptions like date formats.
     *
     * @param string $text The text to process
     * @param int $maxLength Maximum length of the returned sentence (0 = unlimited)
     * @return string The first sentence
     */
    public static function extractFirstSentence($text, $maxLength = 0)
    {
        if (empty($text)) {
            return '';
        }

        // Normalize text: remove multiple spaces and strip HTML tags
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // Known abbreviations and patterns that end with a period but don't mark the end of a sentence
        $exceptions = [
            // Date formats with day
            '/\b\d{1,2}\.\s*\d{1,2}\.\s*\d{2,4}\b/',  // 28.04.1945
            '/\b\d{1,2}\.\s+(Jan|Feb|Mär|Apr|Mai|Jun|Jul|Aug|Sep|Okt|Nov|Dez)\.?\s+\d{2,4}\b/i', // 28. Apr. 1945
            '/\b\d{1,2}\.\s+(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\.?\s+\d{2,4}\b/i', // 28. April 1945
            '/\b\d{1,2}\.\s+(Jan|Feb|Mär|Apr|Mai|Jun|Jul|Aug|Sep|Okt|Nov|Dez|Januar|Februar|März|April|Juni|Juli|August|September|Oktober|November|Dezember)\b/i', // 28. April (without year)
            // Birth and death dates with asterisk or cross (complete dates)
            '/\(\*\s*\d{1,2}\.\s*\d{1,2}\.\s*\d{2,4}\b/',  // (* 28.04.1945
            '/\(\*\s*\d{1,2}\.\s+(Jan|Feb|Mär|Apr|Mai|Jun|Jul|Aug|Sep|Okt|Nov|Dez)\.?\s+\d{2,4}\b/i', // (* 28. Apr. 1945
            '/\(\*\s*\d{1,2}\.\s+(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\.?\s+\d{2,4}\b/i', // (* 28. April 1945
            '/\(†\s*\d{1,2}\.\s*\d{1,2}\.\s*\d{2,4}\b/',  // († 28.04.1945
            '/\(†\s*\d{1,2}\.\s+(Jan|Feb|Mär|Apr|Mai|Jun|Jul|Aug|Sep|Okt|Nov|Dez)\.?\s+\d{2,4}\b/i', // († 28. Apr. 1945
            '/\(†\s*\d{1,2}\.\s+(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\.?\s+\d{2,4}\b/i', // († 28. April 1945
            // Birth and death dates with asterisk or cross (incomplete dates)
            '/\(\*\s*\d{1,2}\.\b/',  // (* 28.
            '/\(†\s*\d{1,2}\.\b/',   // († 28.
            // General abbreviations
            '/\b[A-Za-z]\.\s+[A-Za-z]\.\s+[A-Za-z]\./',  // F. K. L.
            '/\b[A-Za-z]\.\s+[A-Za-z]\./',             // F. K.
            '/\b(ca|bzw|ggf|usw|etc|inkl|exkl|z\.B|d\.h|u\.a|o\.ä|m\.E)\.\s+/i', // Common German abbreviations
            '/\b(Dr|Prof|Hr|Fr|St|Bd|Nr)\.\s+/i',      // Titles and other common abbreviations
        ];

        // Replace all exceptions temporarily with placeholders
        $placeholders = [];
        foreach ($exceptions as $index => $pattern) {
            $placeholder = "##PLACEHOLDER{$index}##";
            $matches = [];
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $placeholders[$placeholder . count($placeholders)] = $match;
                    $text = preg_replace('/' . preg_quote($match, '/') . '/', $placeholder . (count($placeholders) - 1), $text, 1);
                }
            }
        }

        // Special handling for birth date information with (* 28. July ...)
        // If the text starts with a name and contains a birth date, we extract the complete biographical sentence
        if (preg_match('/^([A-Z][a-zA-Zäöüß\s-]+)\s+\(\*\s+\d{1,2}\.\s+[A-Za-zäöüß]+\s+\d{4}/', $text)) {
            // In this case, we assume we have a biographical entry
            // and extract everything up to the first actual sentence end
            $sentences = preg_split('/\.(?=\s+[A-Z])/', $text, 2);
            $firstSentence = $sentences[0];
        } else {
            // Normal sentence extraction for other cases
            $sentences = preg_split('/[.!?](?=\s+[A-Z])/', $text, 2);
            $firstSentence = $sentences[0];
        }

        // Now replace the placeholders with their original texts
        foreach ($placeholders as $placeholder => $original) {
            $firstSentence = str_replace($placeholder, $original, $firstSentence);
        }

        // Remove remaining HTML artifacts and unwanted patterns
        $cleanupPatterns = [
            '/\} \]\)/' => '', // Removes patterns like "} ])"
            '/\[\[.*?\]\]/' => '', // Removes Wiki syntax [[...]]
            '/<.*?>/' => '', // Removes remaining HTML tags
            '/\{\{.*?\}\}/' => '', // Removes template syntax {{...}}
            '/&lt;.*?&gt;/' => '', // Removes encoded HTML tags
            '/\{\|.*?\|\}/' => '', // Removes table markup
        ];

        foreach ($cleanupPatterns as $pattern => $replacement) {
            $firstSentence = preg_replace($pattern, $replacement, $firstSentence);
        }

        // Add a period at the end if none exists
        if (!preg_match('/[.!?]$/', $firstSentence)) {
            $firstSentence .= '.';
        }

        // Trim to max length if needed
        if ($maxLength > 0 && mb_strlen($firstSentence) > $maxLength) {
            $firstSentence = mb_substr($firstSentence, 0, $maxLength - 3) . '...';
        }

        return trim($firstSentence);
    }
}
