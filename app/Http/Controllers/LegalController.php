<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class LegalController extends Controller
{
    public function terms()
    {
        return $this->page('Terms of Service', 'terms');
    }

    public function privacy()
    {
        return $this->page('Privacy Policy', 'privacy');
    }

    private function page(string $title, string $document)
    {
        $markdown = file_get_contents(resource_path("markdown/{$document}.md"));

        return view('legal.show', [
            'title' => $title,
            'html' => Str::markdown($markdown, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ]);
    }
}
