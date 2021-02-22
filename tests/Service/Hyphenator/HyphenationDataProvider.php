<?php

declare(strict_types = 1);

namespace Tests\Service\Hyphenator;

use App\Entity\HyphenationPattern;

class HyphenationDataProvider
{
    /**
     * Hyphenation patterns for words 'mistranslate' and 'vigorous', in the form
     * of dictionary [patternString => HyphenationPattern obj]
     * @return HyphenationPattern[]
     */
    public function getFewPatternsDictionary(): array
    {
        return [
            // 'mistranslate' patterns
            new HyphenationPattern('.mis1'),
            new HyphenationPattern('a2n'),
            new HyphenationPattern('m2is'),
            new HyphenationPattern('2n1s2'),
            new HyphenationPattern('n2sl'),
            new HyphenationPattern('s1l2'),
            new HyphenationPattern('s3lat'),
            new HyphenationPattern('st4r'),
            new HyphenationPattern('4te.'),
            new HyphenationPattern('1tra'),
            // 'vigorous' patterns
            new HyphenationPattern('1go'),
            new HyphenationPattern('gor5ou'),
            new HyphenationPattern('2ig'),
            new HyphenationPattern('i2go'),
            new HyphenationPattern('ig3or'),
            new HyphenationPattern('ou2'),
            new HyphenationPattern('2us'),
            // random patterns
            new HyphenationPattern('1va'),
            new HyphenationPattern('2a2r'),
            new HyphenationPattern('2ite'),
            new HyphenationPattern('1pos'),
        ];
    }
    
    // Data provider methods
    
    public function getFewSingleWordsData(): array
    {
        return [
            'mistranslate' => ['mistranslate', 'mis-trans-late'],
            'vigorous' => ['vigorous', 'vig-or-ous'],
        ];
    }
    
    public function getManySingleWordsData(): array
    {
        return [
            ['mistranslate', 'mis-trans-late'],
            ['vigorous', 'vig-or-ous'],
            ['changed', 'changed'],
            ['pitch', 'pitch'],
            ['uncopyrightable', 'un-copy-rightable'],
            ['system', 'sys-tem'],
            ['disastrous', 'dis-as-trous'],
            ['frightening', 'fright-en-ing'],
            ['encouraging', 'en-cour-ag-ing'],
        ];
    }
    
    public function getShortTextsData(): array
    {
        return [
            ['mistranslate vigorous', 'mis-trans-late vig-or-ous']
        ];
    }
    
    public function getLongTextsData(): array
    {
        return [
            [
                'If once you start down the dark path, forever will it dominate your destiny, consume you '
                .'it will, as it did Obi-Wan’s apprentice.',
                'if once y-ou s-tart down the dark- path, for-ev-er will it dom-i-nate y-our des-tiny, con-sume y-ou '
                .'it will, as it did o-bi-wan’s ap-pren-tice.'
            ],
            [
                'Most wels catfish are mainly about 1.3–1.6 m (4 ft 3 in–5 ft 3 in) long; fish longer than 2 m '
                .'(6 ft 7 in) are normally a rarity.',
                'most wel-s cat-fish are main-ly about 1.3–1.6 m (4 ft 3 in-–5 ft 3 in-) long; fish longer than 2 m '
                .'(6 ft 7 in-) are nor-mal-ly a rar-i-ty.'
            ]
        ];
    }
}
