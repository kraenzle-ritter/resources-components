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
        $text = static::stripWikiAndMarkdown($text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\[[^\]]*\]/', '', $text); // Remove [references], [citation needed], etc.
        $text = preg_replace('/\(.*?\)/', '', $text);    // Optional: remove parentheses
        $text = trim($text);

        $abbreviations = [
            // German
            'z\. ?B\.',
            'u\. ?a\.',
            'd\. ?h\.',
            'etc\.',
            'usw\.',
            'bspw\.',
            'sog\.',
            'bzw\.',
            'Dr\.',
            'Prof\.',
            'Dipl\.',
            'Nr\.',
            'Hr\.',
            'Fr\.',
            // English
            'Mr\.',
            'Mrs\.',
            'Ms\.',
            'Dr\.',
            'Prof\.',
            'Inc\.',
            'Ltd\.',
            'St\.',
            'e\.g\.',
            'i\.e\.',
            // French
            'M\.',
            'Mme\.',
            'Dr\.',
            'p\. ex\.',
            'etc\.',
            'n°\.',
            'env\.',
            'cf\.',
            'v\.',
            'St\.',
            // Italian
            'Sig\.',
            'Sig\.ra',
            'Dott\.',
            'Dr\.',
            'ecc\.',
            'p\.es\.',
            'es\.',
            'S\.r\.l\.',
            'n\.',

            // Years or date suffixes
            '\d{1,2}\.\s?(Jan|Feb|Mär|Apr|Mai|Jun|Jul|Aug|Sep|Okt|Nov|Dez|Januar|Februar|März|April|Juni|Juli|August|September|Oktober|November|Dezember)\.?',
            '\d{1,2}\s?(janv|févr|mars|avr|mai|juin|juil|août|sept|oct|nov|déc|janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\.?',
            '\d{1,2}\s?(gen|feb|mar|apr|mag|giu|lug|ago|set|ott|nov|dic|gennaio|febbraio|marzo|aprile|maggio|giugno|luglio|agosto|settembre|ottobre|novembre|dicembre)\.?'
        ];

        // Combine into one lookbehind-safe group
        $exclusionPattern = implode('|', $abbreviations);

        // Regex: Find first sentence-ending punctuation not preceded by an abbreviation or date
        $pattern = '/^.*?(?<!' . $exclusionPattern . ')[.!?](?=\s|$)/iu';

        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[0]);
        }

        // Fallback: return whole text if no sentence-ending punctuation found
        return trim($text);
    }

    public static function stripWikiAndMarkdown(string $input): string
    {
        // Remove Wikipedia-style links: [[Link]] or [[Link|Text]]
        $input = preg_replace('/\[\[(?:[^|\]]*\|)?([^\]]+)\]\]/', '$1', $input);

        // Remove Markdown-style links: [Text](URL)
        $input = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1', $input);

        // Remove bold and italic formatting (**bold**, *italic*, __bold__, _italic_)
        $input = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $input); // bold
        $input = preg_replace('/(\*|_)(.*?)\1/', '$2', $input);    // italic

        // Remove inline code: `code`
        $input = preg_replace('/`([^`]+)`/', '$1', $input);

        // Remove code blocks: ```...``` or indented blocks
        $input = preg_replace('/```.*?```/s', '', $input);
        $input = preg_replace('/^\s{4}.*$/m', '', $input);

        // Remove HTML tags
        $input = strip_tags($input);

        // Remove Markdown headers: # Header
        $input = preg_replace('/^#{1,6}\s*/m', '', $input);

        // Remove list markers: -, *, +, 1., etc.
        $input = preg_replace('/^\s*([-*+]|\d+\.)\s+/m', '', $input);

        // Remove Markdown table rows and separators
        $input = preg_replace('/^\s*\|.*\|\s*$/m', '', $input);

        // Decode HTML entities
        $input = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace (multiple spaces, newlines)
        $input = preg_replace('/\s+/', ' ', $input);
        $input = trim($input);

        return $input;
    }
}
