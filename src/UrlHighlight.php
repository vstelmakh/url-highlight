<?php

namespace VStelmakh\UrlHighlight;

class UrlHighlight
{
    /**
     * @param string $string
     * @return bool
     */
    public function isUrl(string $string): bool
    {
        $urlRegex = $this->getUrlRegex(true);
        return (bool)preg_match($urlRegex, $string);
    }

    /**
     * @param bool $strict
     * @return string
     */
    private function getUrlRegex(bool $strict): string
    {
        $prefix = '';
        $suffix = '';

        if ($strict) {
            $prefix = '^';
            $suffix = '$';
        }

        return '/' . $prefix . '
            \b
            (                                         # Capture URL
                (?:
                    [a-z][\w-]+:                          # url protocol and colon
                    (?:
                        \/{2}                                 # 2 slashes
                        |                                     # or
                        [\w\d]                                # single letter or digit
                    )
                    |                                     # or
                    www\d*\.                              # www., www1., www2., ...
                    |                                     # or
                    \w+\.\w{2,}\/                         # domain name followed by a slash
                )
                (?:                                       # one or more:
                    [^\s()<>]+                                # run of non-space, non-()<>
                    |                                         # or
                    \(([^\s()<>]+|(\([^\s()<>]+\)))*\)        # balanced brackets (up to 2 levels)
                )+
                (?:                                       # end with:
                    \(([^\s()<>]+|(\([^\s()<>]+\)))*\)        # balanced brackets (up to 2 levels)
                    |                                         # or
                    [^\s`!()\[\]{};:\'".,<>?«»“”‘’]           # not a space or punctuation chars
                )
            )
        ' . $suffix . '/ixu';
    }
}
