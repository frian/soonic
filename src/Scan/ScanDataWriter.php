<?php

namespace App\Scan;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ScanDataWriter
{
    public function writeCsvRow(mixed $file, array $fields): void
    {
        fputcsv($file, $fields, ';', '"', '\\');
    }

    /**
     * @param array<int, string> $slugs
     * @param-out array<int, string> $slugs
     */
    public function slugify(string $string, array &$slugs): string
    {
        $string = mb_strtolower($string);
        $string = preg_replace('/\s+-\s+/', '-', $string) ?? $string;
        $string = preg_replace('/&/', 'and', $string) ?? $string;
        $string = preg_replace('|[\s+\/]|', '-', $string) ?? $string;
        $string = preg_replace('/-+/', '-', $string) ?? $string;

        $slug = $string;
        $slugCount = 1;
        while (in_array($slug, $slugs, true)) {
            $slug = $string.'-'.$slugCount;
            ++$slugCount;
        }
        $slugs[] = $slug;

        return $slug;
    }

    /**
     * @param array<int, string> $durations
     */
    public function getAlbumDuration(array $durations): string
    {
        $secs = 0;
        foreach ($durations as $duration) {
            $durationParts = explode(':', $duration);
            $numDurationParts = count($durationParts);
            if ($numDurationParts === 1) {
                $secs += (int) $durationParts[0];
            } elseif ($numDurationParts === 2) {
                $secs += (int) $durationParts[0] * 60;
                $secs += (int) $durationParts[1];
            } elseif ($numDurationParts === 3) {
                $secs += (int) $durationParts[0] * 3600;
                $secs += (int) $durationParts[1] * 60;
                $secs += (int) $durationParts[2];
            }
        }
        $secs = $secs > 9 ? $secs : 0 .$secs;

        return (string) $secs;
    }

    /**
     * @param array<int, array<string, mixed>> $songs
     *
     * @return array{album_id: int, songs: array<int, array<string, mixed>>}
     */
    public function addAlbumIds(array $songs, int $albumId, mixed $sqlArtistAlbumFile, mixed $sqlSongFile): array
    {
        $albums = [];
        $ids = [];
        foreach ($songs as $song) {
            if (!in_array($song['album'], $albums, true)) {
                $albums[] = $song['album'];
                $ids[$song['album']] = ++$albumId;
            }
        }

        $artistAlbumValues = [];
        foreach ($songs as &$song) {
            $song['album_id'] = $ids[$song['album']];

            $this->writeCsvRow($sqlSongFile, [
                '',
                $song['album_id'],
                $song['artist_id'],
                $song['path'],
                $song['web_path'],
                $song['title'],
                $song['track_number'],
                $song['year'],
                $song['genre'],
                $song['duration'],
            ]);

            $artistAlbumValue = $song['artist_id'].';'.$song['album_id'];
            if (!in_array($artistAlbumValue, $artistAlbumValues, true)) {
                $artistAlbumValues[] = $artistAlbumValue;
            }
        }
        unset($song);

        foreach ($artistAlbumValues as $value) {
            $parts = explode(';', $value);
            $this->writeCsvRow($sqlArtistAlbumFile, [$parts[0], $parts[1]]);
        }

        return ['album_id' => $albumId, 'songs' => $songs];
    }

    /**
     * @param array<int, array<string, mixed>> $songs
     * @param array<int, string>               $albumsSlugs
     */
    public function buildAlbumTags(array $songs, array &$albumsSlugs, mixed $sqlAlbumFile, SymfonyStyle $io, int $verbosity): void
    {
        $albumSingleTags = ['year', 'genre', 'album_path'];
        $albumsTags = [];

        foreach ($songs as $song) {
            if (!array_key_exists('albums', $albumsTags)) {
                $albumsTags['albums'] = [];
            }
            if (!in_array($song['album'], $albumsTags['albums'], true)) {
                $albumsTags['albums'][] = $song['album'];
            }
        }

        foreach ($albumsTags['albums'] as $album) {
            $albumTags = [];
            foreach ($songs as $song) {
                if ($song['album'] === $album) {
                    foreach ($albumSingleTags as $tag) {
                        if (!array_key_exists($song[$tag], $albumTags)) {
                            $albumTags[$tag] = $song[$tag];
                        }
                    }

                    $albumTags['album'] = $album;

                    if (!array_key_exists('artists', $albumTags)) {
                        $albumTags['artists'] = [];
                    }
                    if (!in_array($song['artist'], $albumTags['artists'], true)) {
                        $albumTags['artists'][] = $song['artist'];
                    }

                    if (!array_key_exists('durations', $albumTags)) {
                        $albumTags['durations'] = [];
                    }
                    $albumTags['durations'][] = $song['duration'];
                }
            }
            $albumTags['artist'] = count($albumTags['artists']) > 1 ? 'Various' : $albumTags['artists'][0];

            $this->writeCsvRow($sqlAlbumFile, [
                '',
                $albumTags['album'],
                $this->slugify($albumTags['album'], $albumsSlugs),
                count($albumTags['durations']),
                $this->getAlbumDuration($albumTags['durations']),
                $albumTags['year'],
                $albumTags['genre'],
                $albumTags['album_path'],
                '',
            ]);

            if ($verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $io->text(' added album '.$albumTags['album_path']);
            }
        }
    }
}
