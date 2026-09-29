<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Parser
{
    public const string IANA_TLD_LIST_URL = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt';

    public function __construct(
        private LineReader $lineReader,
    ) {}

    public function parse(string $url = self::IANA_TLD_LIST_URL): DomainList
    {
        $lines = $this->lineReader->readLines($url);
        $header = array_shift($lines);

        if ($header === null) {
            throw new \RuntimeException(sprintf('No header line found in "%s".', $url));
        }

        $version = $this->parseVersion($header);
        $lastUpdated = $this->parseLastUpdated($header);
        $domains = array_map($this->parseDomain(...), $lines);

        return new DomainList($version, $lastUpdated, $domains);
    }

    private function parseVersion(string $line): int
    {
        $pattern = '/Version (?<version>\d+),/i';

        if (preg_match($pattern, $line, $matches) !== 1) {
            throw new \RuntimeException(sprintf('Could not parse version from header line "%s".', $line));
        }

        return (int) $matches['version'];
    }

    private function parseLastUpdated(string $line): \DateTimeImmutable
    {
        $pattern = '/Last Updated (?<date>.+)$/i';

        if (preg_match($pattern, $line, $matches) !== 1) {
            throw new \RuntimeException(sprintf('Could not parse date from header line "%s".', $line));
        }

        $lastUpdated = \DateTimeImmutable::createFromFormat('D M d H:i:s Y T', $matches['date']);
        if ($lastUpdated === false) {
            throw new \RuntimeException(sprintf(
                'Could not parse date "%s". Unexpected format.',
                $matches['date'],
            ));
        }

        return $lastUpdated;
    }

    private function parseDomain(string $line): Domain
    {
        $normalized = mb_strtolower(trim($line));
        $isPunycode = str_starts_with($normalized, 'xn--');
        $value = $isPunycode ? idn_to_utf8($line, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) : $normalized;

        if ($value === false) {
            throw new \RuntimeException(sprintf(
                'Error "%s" on decoding punycode domain "%s".',
                error_get_last()['message'] ?? '-',
                $normalized,
            ));
        }

        return new Domain($value);
    }
}
