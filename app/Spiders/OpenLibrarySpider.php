<?php

namespace App\Spiders;

use Exception;
use Generator;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Illuminate\Support\Facades\App;
use RoachPHP\Extensions\LoggerExtension;
use Symfony\Component\DomCrawler\Crawler;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;

class OpenLibrarySpider extends BasicSpider
{
    /**
     * @var string[]
     */
    public array $startUrls = [
        'https://openlibrary.org/trending/forever'
    ];

    /**
     * The downloader middleware that should be used for runs of this spider.
     */
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        [UserAgentMiddleware::class, ['userAgent' => 'Mozilla/5.0 (compatible; RoachPHP/0.1.0)']],
    ];

    public array $itemProcessors = [
        //
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public int $concurrency = 2;

    public int $requestDelay = 1;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        // Get all the items on the page.
        $items = $response
            ->filter('ul.list-books > li')
            ->each(fn (Crawler $node) => [
                'title' => $node->filter('.resultTitle a')->text(),
                'url' => $node->filter('.resultTitle a')->link()->getUri(),
                'author' => $node->filter('.bookauthor a')->text(),
                'cover' => $node->filter('.bookcover img')->attr('src'),
            ]);

        foreach ($items as $item) {
            yield $this->request('GET', $item['url'], 'parseBookPage', ['item' => $item]);
        }

        // Try to get the next page url and yield a request for it if it exists.
        try {
            $nextPageUrl = $response->filter('div.pager div.pagination > :last-child')->link()->getUri();
            yield $this->request('GET', $nextPageUrl);
        } catch (Exception) {
        }
    }

    /**
     * Parses the book page and returns a generator of items.
     */
    public function parseBookPage(Response $response): Generator
    {
        $item = $response->getRequest()->getOptions()['item'];

        $descriptionArray = $response
            ->filter('div.book-description-content p')
            ->each(fn (Crawler $node) => $node->text());

        $item['description'] = implode("\n", $descriptionArray);
        $item['pages'] = $response->filter('span[itemprop="numberOfPages"]')->innerText();
        $item['publishDate'] = $response->filter('span[itemprop="datePublished"]')->innerText();

        yield $this->item($item);
    }
}
