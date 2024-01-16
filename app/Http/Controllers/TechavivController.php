<?php

namespace App\Http\Controllers;

use App\Spiders\TechavivMembersSpider;
use RoachPHP\Roach;

class TechavivController extends Controller
{
    public function members()
    {
        $members = Roach::collectSpider(TechavivMembersSpider::class);

        $members = array_map(fn ($item) => $item->all(), $members);

        storage_path(file_put_contents('members.json', json_encode($members, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));

        // todo after get all text data, then get all image data
    }
}
