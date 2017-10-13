<?php
namespace Dukhanin\Panel\Traits;

trait HasAssets
{
    protected $assets = [
        'scripts' => [],
        'styles' => [],
        'others' => [],
    ];

    public function addAsset(string $url, $section = null)
    {
        if (is_null($section)) {
            $section = $this->suggestAssetSection($url);
        }

        if (! isset($this->assets[$section])) {
            $this->assets[$section] = [];
        }

        $this->assets[$section][] = $url;
    }

    public function assets($section = null)
    {
        if (is_null($section)) {
            return array_flatten($this->assets);
        }

        return array_get($this->assets, $section, []);
    }

    public function pushScripts()
    {
        view()->startPush('scripts');

        foreach ($this->assets('scripts') as $url) {
            echo html_tag('script', [
                'tag-plural' => true,
                'src' => $url,
                'type' => 'text/javascript',
            ]);
        }

        view()->stopPush();
    }

    public function pushStyles()
    {
        view()->startPush('styles');

        foreach ($this->assets('styles') as $url) {
            echo html_tag('link', [
                'tag-singular' => true,
                'rel' => 'stylesheet',
                'href' => $url,
            ]);
        }

        view()->stopPush();
    }

    public function pushAssets()
    {
        $this->pushScripts();

        $this->pushStyles();
    }

    protected function suggestAssetSection(string $url)
    {
        if (ends_with($url = explode('?', $url)[0], '.js')) {
            return 'scripts';
        }

        if (ends_with($url, ['.css', '.scss'])) {
            return 'styles';
        }

        return 'others';
    }
}