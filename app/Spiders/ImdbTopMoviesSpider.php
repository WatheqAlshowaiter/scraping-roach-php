<?php

namespace App\Spiders;

use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use App\Processors\CleanMovieTitle;
use Symfony\Component\DomCrawler\Crawler;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;


class ImdbTopMoviesSpider extends BasicSpider
{
    /**
     * @var string[]
     */
    public array $startUrls = [
        'https://www.imdb.com/chart/top/'
    ];

    /**
     * The downloader middleware that should be used for runs of this spider.
     */
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        [UserAgentMiddleware::class, ['userAgent' => 'Mozilla/5.0 (compatible; RoachPHP/0.1.0)']],
    ];

    /**
     * The item processors that emitted items will be sent through.
     */
    public array $itemProcessors = [
        CleanMovieTitle::class,
    ];

    /**
     * Parses the response and returns a generator of items.
     */
    public function parse(Response $response): Generator
    {
        $items = $response
            ->filter('ul.ipc-metadata-list div.ipc-title > a')
            ->each(fn (Crawler $node) => [
                'url' => $node->link()->getUri(),
                'title' => $node->children('h3')->text(),
            ]);

        foreach ($items as $item) {
            yield $this->item($item);
        }
    }
}
