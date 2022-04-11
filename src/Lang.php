<?php

namespace MarkSitko\DeepTranslatable;

class Lang
{
    const DE = 'de'; // German
    const EN = 'en'; // English
    const EN_GB = 'en-GB'; // English-British
    const EN_US = 'en-US'; // English-American
    const ES = 'es'; // Spanish
    const FR = 'fr'; // French
    const IT = 'it'; // Italien
    const JP = 'jp'; // Japanese
    const NL = 'nl'; // Dutch
    const PL = 'pl'; // Polish
    const PT = 'pt'; // Portuguese
    const PT_BR = 'pt-BR'; // Portuguese-Brazil
    const PT_PT = 'pt-PT'; // Portuguese-Portugal
    const RU = 'ru'; // Russian
    const ZH = 'zh'; // Chinese
    const AUTO = null; // Auto

    public static function exists(string $code): bool
    {
        return in_array($code, self::all());
    }

    public static function all(): array
    {
        return [
            self::DE,
            self::EN,
            self::EN_GB,
            self::EN_US,
            self::ES,
            self::FR,
            self::IT,
            self::JP,
            self::NL,
            self::PL,
            self::PT,
            self::PT_BR,
            self::PT_PT,
            self::RU,
            self::ZH,
            self::AUTO,
        ];
    }

    public static function langToLanguage(string $lang): string
    {
        return match (strtolower($lang)) {
            self::DE => 'Deutsch',
            self::EN => 'Englisch',
            self::ES => 'Spanisch',
            self::FR => 'Französich',
            self::IT => 'Italienisch',
            self::JP => 'Japanisch',
            self::NL => 'Niederländisch',
            self::PL => 'Polnisch',
            self::PT => 'Portugisich',
            self::RU => 'Russisch',
            self::ZH => 'Chinesisch',
            default => 'Deutsch',
        };
    }
}
