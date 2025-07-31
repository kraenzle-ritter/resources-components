<?php

namespace KraenzleRitter\ResourcesComponents\Tests\Unit;

use KraenzleRitter\ResourcesComponents\Helpers\TextHelper;
use PHPUnit\Framework\TestCase;

class TextHelperTest extends TestCase
{
    public function test_extract_first_sentence_with_french_wikipedia_reference()
    {
        $text = "nécessaire] L'un des cousins d'Ernst Cassirer, l'éditeur Bruno Cassirer, publiera une partie des ouvrages du philosophe.";

        $result = TextHelper::extractFirstSentence($text);

        $this->assertEquals("L'un des cousins d'Ernst Cassirer, l'éditeur Bruno Cassirer, publiera une partie des ouvrages du philosophe.", $result);
    }

    public function test_extract_first_sentence_with_citation_needed()
    {
        $text = "[citation nécessaire] Ernst Cassirer était un philosophe allemand. Il a écrit de nombreux ouvrages.";

        $result = TextHelper::extractFirstSentence($text);

        $this->assertEquals("Ernst Cassirer était un philosophe allemand.", $result);
    }

    public function test_extract_first_sentence_with_ref_necessary()
    {
        $text = "[réf. nécessaire] Ceci est un test. Voici une autre phrase.";

        $result = TextHelper::extractFirstSentence($text);

        $this->assertEquals("Ceci est un test.", $result);
    }

    public function test_extract_first_sentence_with_square_brackets()
    {
        $text = "[quelque chose] Ceci est un test de nettoyage. Voici une autre phrase.";

        $result = TextHelper::extractFirstSentence($text);

        $this->assertEquals("Ceci est un test de nettoyage.", $result);
    }

    public function test_extract_first_sentence_german()
    {
        $text = "Ernst Cassirer (* 28. Juli 1874 in Breslau; † 13. April 1945 in New York) war ein deutscher Philosoph.";

        $result = TextHelper::extractFirstSentence($text);

        $this->assertEquals("Ernst Cassirer (* 28. Juli 1874 in Breslau; † 13. April 1945 in New York) war ein deutscher Philosoph.", $result);
    }

    public function test_extract_first_sentence_with_french_accents()
    {
        $text = "Émile Zola était un écrivain français. Il a vécu au XIXe siècle.";

        $result = TextHelper::extractFirstSentence($text);

        $this->assertEquals("Émile Zola était un écrivain français.", $result);
    }

    public function test_extract_first_sentence_with_max_length()
    {
        $text = "Ceci est une phrase très longue qui devrait être tronquée car elle dépasse la limite de caractères.";

        $result = TextHelper::extractFirstSentence($text, 30);

        $this->assertEquals("Ceci est une phrase très lo...", $result);
    }

    public function test_extract_first_sentence_empty_text()
    {
        $result = TextHelper::extractFirstSentence('');

        $this->assertEquals('', $result);
    }

    public function test_extract_first_sentence_long_text_without_period()
    {
        $longText = str_repeat("Lorem ipsum dolor sit amet consectetur adipiscing elit ", 10);

        $result = TextHelper::extractFirstSentence($longText);

        // Should add a period at the end
        $this->assertStringEndsWith('.', $result);
    }
}
