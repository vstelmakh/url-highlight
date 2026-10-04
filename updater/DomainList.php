<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class DomainList implements \Countable
{
    public int $version;
    public \DateTimeImmutable $lastUpdated;

    /** @var array<string, Domain> */
    public array $domains;

    /**
     * @param list<Domain> $domains
     */
    public function __construct(int $version, \DateTimeImmutable $lastUpdated, array $domains)
    {
        $this->version = $this->resolveVersion($version);
        $this->lastUpdated = $this->resolveLastUpdated($lastUpdated);
        $this->domains = $this->resolveDomains($domains);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->domains);
    }

    private function resolveVersion(int $version): int
    {
        if ($version <= 0) {
            throw new \DomainException(sprintf('Version %d should be greater than 0.', $version));
        }
        return $version;
    }

    private function resolveLastUpdated(\DateTimeImmutable $lastUpdated): \DateTimeImmutable
    {
        // ARPANET officially changed to the TCP/IP standard on January 1, 1983, hence the birth of the Internet.
        $internetBirthDate = new \DateTimeImmutable('1983-01-01 00:00:00 UTC');

        if ($internetBirthDate > $lastUpdated) {
            throw new \DomainException(sprintf(
                'Last updated date "%s" should be after the internet was born.',
                $lastUpdated->format(\DateTimeInterface::ATOM),
            ));
        }

        return $lastUpdated;
    }

    /**
     * @param list<Domain> $domains
     *
     * @return array<string, Domain>
     */
    private function resolveDomains(array $domains): array
    {
        if ($domains === []) {
            throw new \DomainException('Domains list should not be empty.');
        }

        $result = [];

        foreach ($domains as $domain) {
            $result[$domain->unicode] = $domain;
        }

        ksort($result, SORT_STRING);

        return $result;
    }
}
