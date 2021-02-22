<?php

declare(strict_types = 1);

namespace Tests\Service\Hyphenator;

class HyphenationDataProvider
{
    
    public static function getHyphenationSingleWords(): array
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
}
