<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Presenters;

use Timegridio\Concierge\Models\Business;

class BusinessPresenter extends Presenter
{
    public function __construct(Business $resource)
    {
        parent::__construct($resource);
    }

    protected function business(): Business
    {
        /** @var Business $resource */
        $resource = $this->resource;

        return $resource;
    }

    public function facebookImg(string $type = 'square'): string
    {
        $url = parse_url((string) $this->business()->social_facebook);
        $name = e($this->business()->name);

        if (! $this->business()->social_facebook || ! array_key_exists('path', $url)) {
            return "<img class=\"img-thumbnail\" src=\"//placehold.it/100x100\" height=\"100\" width=\"100\" alt=\"{$name}\"/>";
        }

        $userId = trim($url['path'], '/');

        if ($url['path'] === '/profile.php' && isset($url['query'])) {
            parse_str($url['query'], $parts);
            $userId = $parts['id'] ?? $userId;
        }

        $pictureUrl = e("http://graph.facebook.com/{$userId}/picture?type={$type}");

        return "<img class=\"img-thumbnail media-object\" src=\"{$pictureUrl}\" height=\"100\" width=\"100\" alt=\"{$name}\"/>";
    }

    public function staticMap(int $zoom = 15): string
    {
        $data = [
            'center' => $this->business()->postal_address,
            'zoom' => $zoom,
            'scale' => '2',
            'size' => '180x100',
            'maptype' => 'roadmap',
            'format' => 'gif',
            'visual_refresh' => 'true',
        ];

        $src = e('http://maps.googleapis.com/maps/api/staticmap?'.http_build_query($data, '', '&amp;'));

        return "<img class=\"img-responsive img-thumbnail center-block\" width=\"180\" height=\"100\" src=\"{$src}\"/>";
    }

    public function industryIcon(): string
    {
        $coverPhoto = $this->business()->pref('cover_photo_url');
        $slug = e($this->business()->category?->slug ?? 'default');
        $src = $coverPhoto ?: (function_exists('asset')
            ? asset('/img/industries/'.$slug.'.png')
            : '/img/industries/'.$slug.'.png');

        return '<img class="img-responsive center-block" src="'.e((string) $src).'"/>';
    }
}
