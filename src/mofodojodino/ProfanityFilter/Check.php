<?php

namespace mofodojodino\ProfanityFilter;

class Check
{
    /**
     * Regular expression for checking between swear word characters
     */
    const IN_BETWEEN_REGEX = '[\\s|\||!|@|#|\$|%|^|&|\*|\(|\)|\-|+|_|=|\{|\}|\[|\]|:|;|\'|\"|<|>|\?|,|\.|\/|~|`]*';

    /**
     * List of bad words to test against
     *
     * @var array
     */
    protected $badwords = array();

    /**
     * List of potential character substitutions as a regular expression.
     *
     * @var array
     */
    protected $replacements = array(
        '/á/' => '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|æ|Æ|α|Δ|Λ|λ)+{$}',
        '/a/' => '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|æ|Æ|α|Δ|Λ|λ)+{$}', //this will be used later to generate a regex
        '/b/' => '(b|b\.|b\-|8|\|3|ß|Β|β)+{$}',                               // I think that {$} means "nothing"
        '/c/' => '(c|c\.|c\-|Ç|ç|ć|Ć|č|Č|¢|€|<|\(|{|©)+{$}',
        '/d/' => '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)+{$}',
        '/e/' => '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|ë|Ë|ē|Ē|ė|Ė|ę|Ę|∑)+{$}',
        '/è/' => '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|ë|Ë|ē|Ē|ė|Ė|ę|Ę|∑)+{$}',
        '/é/' => '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|ë|Ë|ē|Ē|ė|Ė|ę|Ę|∑)+{$}'
        '/f/' => '(f|f\.|f\-|ƒ)+{$}',
        '/g/' => '(g|g\.|g\-|6|9)+{$}',
        '/h/' => '(h|h\.|h\-|Η)+{$}',
        '/í/' => '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï|ī|Ī|į|Į)+{$}',
        '/i/' => '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï|ī|Ī|į|Į)+{$}',
        '/j/' => '(j|j\.|j\-)+{$}',
        '/k/' => '(k|k\.|k\-|Κ|κ)+{$}',
        '/l/' => '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï|ł|Ł)+{$}',
        '/m/' => '(m|m\.|m\-)+{$}',
        '/n/' => '(n|n\.|n\-|η|Ν|Π|ñ|Ñ|ń|Ń)+{$}',
        '/ñ/' => '(n|n\.|n\-|η|Ν|Π|ñ|Ñ|ń|Ń)+{$}',
        '/ó/' => '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø|ô|Ô|ö|Ö|ò|Ò|ó|Ó|œ|Œ|ø|Ø|ō|Ō|õ|Õ)+{$}',
        '/o/' => '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø|ô|Ô|ö|Ö|ò|Ò|ó|Ó|œ|Œ|ø|Ø|ō|Ō|õ|Õ)+{$}',
        '/p/' => '(p|p\.|p\-|ρ|Ρ|¶|þ)+{$}',
        '/q/' => '(q|q\.|q\-)+{$}',
        '/r/' => '(r|r\.|r\-|®)+{$}',
        '/s/' => '(s|s\.|s\-|5|\$|§|ß|Ś|ś|Š|š)+{$}',
        '/t/' => '(t|t\.|t\-|Τ|τ)+{$}',
        '/u/' => '(u|u\.|u\-|υ|µ|û|ü|ù|ú|ū|Û|Ü|Ù|Ú|Ū)+{$}',
        '/ú/' => '(u|u\.|u\-|υ|µ|û|ü|ù|ú|ū|Û|Ü|Ù|Ú|Ū)+{$}',
        '/v/' => '(v|v\.|v\-|υ|ν)+{$}',
        '/w/' => '(w|w\.|w\-|ω|ψ|Ψ)+{$}',
        '/x/' => '(x|x\.|x\-|Χ|χ)+{$}',
        '/y/' => '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)+{$}',
        '/z/' => '(z|z\.|z\-|Ζ|ž|Ž|ź|Ź|ż|Ż)+{$}',
    );

    /**
     * @param null $config
     */
    public function __construct($config = null)
    {
        if ($config === null) {
            $config = __DIR__ . '/../../../config/badwords.php';
        }

        $this->badwords = $this->loadBadwordsFromFile($config);
    }

    /**
     * Checks string for profanities based on list 'badwords'
     *
     * @param $string
     *
     * @return bool
     */
    public function hasProfanity($string)
    {
        if (empty($string)) {
            return false;
        }

        $badwords = array();
        for ($i = 0; $i < count($this->badwords); $i++) { //badwords is not the same as this->badwords
            $badwords[ $i ] = '/' . preg_replace(
                    array_keys($this->replacements),
                    array_values($this->replacements),
                    $this->badwords[ $i ]                //a single word in the bad word list
                ) . '/i';
            $badwords[ $i ] = str_replace('{$}', self::IN_BETWEEN_REGEX, $badwords[ $i ]);
        }

        foreach ($badwords as $profanity) {
                stringHasProfanity($string, $profanity);
        }

        return $string_explode;
    }

    /**
     * Checks a string against a profanity.
     *
     * @param $string
     * @param $profanity
     *
     * @return bool
     */
    private function stringHasProfanity($string, $profanity)
    {
       preg_replace($profanity, "*****", $string);
    }

    /**
     * Load 'badwords' from config file.
     *
     * @param $config
     *
     * @return array
     */
    private function loadBadwordsFromFile($config)
    {
        return include($config);
    }
}
