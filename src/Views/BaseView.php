<?php

namespace Oriole\Views;

use Oriole\Oriole;
use RuntimeException;

class BaseView
{
    protected array $appConfig;
    
    /**
     * Data that is made available to the Views.
     *
     * @var array
     */
    protected array $data = [];
    
    /**
     * The name of the layout being used, if any.
     * Set by the `extend` method used within views.
     *
     * @var string|null
     */
    protected string|null $layout = null;
    
    /**
     * Holds the sections and their data.
     *
     * @var array
     */
    protected array $sections = [];
    
    /**
     * The name of the current section being rendered,
     * if any.
     *
     * @var array<string>
     */
    protected array $sectionStack = [];
    
    public function __construct()
    {
        $this->appConfig = (new Oriole)->getConfig('app');
    }
    
    /**
     * Builds the output based upon a file name and any
     * data that has already been set.
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render(string $view, array $data = []) : string
    {
        $oldData = $this->data;
        $this->data = array_merge($oldData, $data);
        
        $output = (function ($view) : string {
            extract($this->data);
            ob_start();
            include $this->appConfig['viewsPath'] . $view;
            
            return ob_get_clean() ? : '';
        })($view);
        
        $this->data = $oldData;
    
        // When using layouts, the data has already been stored
        // in $this->sections, and no other valid output
        // is allowed in $output, so we'll overwrite it.
        if ($this->layout !== null && $this->sectionStack === []) {
            $layoutView = $this->layout;
            $this->layout = null;
            $output = $this->render($layoutView, $data);
        }
    
        $this->data = [];
        
        return $output;
    }
    
    /**
     * Specifies that the current view should extend an existing layout.
     */
    public function extend(string $layout) : void
    {
        $this->layout = $layout;
    }
    
    /**
     * Starts holds content for a section within the layout.
     *
     * @param string $name Section name
     */
    public function section(string $name) : void
    {
        $this->sectionStack[] = $name;
        
        ob_start();
    }
    
    /**
     * Captures the last section
     *
     * @throws RuntimeException
     */
    public function endSection() : void
    {
        $contents = ob_get_clean();
        
        if ($this->sectionStack === []) {
            throw new RuntimeException('View themes, no current section.');
        }
        
        $section = array_pop($this->sectionStack);
        
        // Ensure an array exists, so we can store multiple entries for this.
        if (! array_key_exists($section, $this->sections)) {
            $this->sections[$section] = [];
        }
        
        $this->sections[$section][] = $contents;
    }
    
    /**
     * Renders a section's contents.
     */
    public function renderSection(string $sectionName) : void
    {
        if (! isset($this->sections[$sectionName])) {
            echo '';
            return;
        }
        
        foreach ($this->sections[$sectionName] as $key => $contents) {
            echo $contents;
            unset($this->sections[$sectionName][$key]);
        }
    }
}