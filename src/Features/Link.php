<?php

namespace Dukhanin\Panel\Features;

trait Link
{
    public function initFeatureLink()
    {
        $this->modelActions()->put('link', function ($panel, $model) {
            if (empty($url = $this->getModelLink($model))) {
                return false;
            }

            return [
                'url' => $url,
            ];
        });
    }

    public function getModelLink($model)
    {
        //
    }
}