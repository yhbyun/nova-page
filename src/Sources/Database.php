<?php

namespace Whitecube\NovaPage\Sources;

use Whitecube\NovaPage\Pages\Template;
use App\Models\StaticPage;

class Database implements SourceInterface
{
    /**
     * The table used to store static pages content
     *
     * @var string
     */
    protected $tableName;

    /**
     * Retrieve the source's name
     *
     * @return string
     */
    public function getName()
    {
        return 'database';
    }

    /**
     * Set the source's configuration parameters
     *
     * @return string
     */
    public function setConfig(array $config)
    {
        $this->tableName = $config['table_name'];
    }

    /**
     * Retrieve data from the filesystem
     *
     * @param \Whitecube\NovaPage\Pages\Template $template
     * @return object
     */
    public function fetch(Template $template)
    {
        $staticPage = StaticPage::where('name', $template->getName())->first();
        if ($staticPage) {
            return [
                'title' => $staticPage->title,
                'created_at' => $staticPage->created_at,
                'updated_at' => $staticPage->updated_at,
                'attributes' => $this->getParsedAttributes($template, json_decode($staticPage->attributes, true)) ?? []
            ];
        }
        return;
    }

    /**
     * Save template in the filesystem
     *
     * @param \Whitecube\NovaPage\Pages\Template $template
     * @return bool
     */
    public function store(Template $template)
    {
        $staticPage = StaticPage::firstOrNew(['name' => $template->getName()]);
        $staticPage->name = $template->getName();
        $staticPage->title = $template->getTitle();
        $staticPage->type = $template->getType();
        $staticPage->attributes = json_encode($template->getAttributes(), JSON_UNESCAPED_UNICODE);
        $staticPage->created_at = $template->getDate('created_at');
        $staticPage->save();
    }

    /**
     * Retrieve and parse attributes array
     *
     * @param \Whitecube\NovaPage\Pages\Template $template
     * @param array $attributes
     * @return array
     */
    protected function getParsedAttributes(Template $template, $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (!is_array($value) && !is_object($value)) {
                continue;
            }
            if ($template->isJsonAttribute($key)) {
                continue;
            }
            $attributes[$key] = json_encode($value);
        }

        return $attributes;
    }
}
