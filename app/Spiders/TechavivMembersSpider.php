<?php

namespace App\Spiders;

use Exception;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Downloader\Middleware\UserAgentMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\MaxRequestExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;

class TechavivMembersSpider extends BasicSpider
{
    public array $startUrls = [
        'https://www.techaviv.com/members',
    ];

    public string $peopleLink = 'https://www.techaviv.com';

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
        [UserAgentMiddleware::class, ['userAgent' => 'Mozilla/5.0 (compatible; RoachPHP/0.1.0)']],
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        //
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
        // MaxRequestExtension::class
    ];

    public int $concurrency = 2;

    public int $requestDelay = 2;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        $items = $response
            ->filter('div#clubList > .person-item')
            ->each(fn (Crawler $node) => [
                'name' => $node->filter('.person-name')->text(),
                'url' => $node->filter('.person-top a.person-img-link')->link()->getUri(),
                'avatar' => $node->filter('img.person-img')->attr('src'),
                'title' => $node->filter('.person-title')->text(),
                'location' => $node->filter('[fs-cmsfilter-field=location]')->text(),
                'company' => [
                    'name' => $node->filter('[fs-cmsfilter-field=company]')->text(),
                    'link' => $node->filter('a.person-company-link')->attr('href'),
                    'logo' => $node->filter('.person-logo.img-bw')->attr('src'),
                ],
            ]);

        // todo just for debugging. remove it later
        // count 60 *16  + 45
        // $items = array_slice($items, 0, 2);
        // dd($items);

        foreach ($items as $item) {
            // yield $this->item($item);
            // yield $this->request('GET', $item->getUri(), 'parseBlogPage');
            yield $this->request('GET', $item['url'], 'parseMemberPage', ['item' => $item]);
        }

        // here is the pagination
        // ?8b7cffee_page=1-17

        try {
            $nextPageUrl = $response->filter('.w-pagination-next.cta')->link()->getUri();
            yield $this->request('GET', $nextPageUrl);
        } catch (Exception $e) {
            logger('error while scrapping: '.$e->getMessage());
        }
    }

    public function parseMemberPage(Response $response): Generator
    {
        $item = $response->getRequest()->getOptions()['item'];

        $item['description'] = $response->filter('.large-p.w-richtext')->text();
        $item['socials'] = $response
            ->filter('a.member-social-link:not(.w-condition-invisible)')
            ->each(fn (Crawler $node) => $node->attr('href'));

        yield $this->item($item);
    }
}
