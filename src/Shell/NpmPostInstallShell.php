<?php

namespace App\Shell;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;

class NpmPostInstallShell extends AppShell
{

    public $vendorDir;
    /**
     * do not call parent::main because db connection might not be available
     * @see AppShell::main()
     */
    public function main()
    {
        $this->vendorDir = WWW_ROOT . 'node_modules';

        $this->fontawesomePath = $this->vendorDir . DS . '@fortawesome' . DS . 'fontawesome-free' . DS;
        $this->jqueryUiPath = $this->vendorDir . DS . 'components-jqueryui' . DS;
        $this->jqueryKnobPath = $this->vendorDir . DS . 'jquery-knob' . DS;
        $this->leafletPath = $this->vendorDir . DS . 'leaflet' . DS;
        $this->featherlightPath = $this->vendorDir . DS . 'featherlight' . DS;

        $this->cleanOverheadFromDependencies();
        $this->copyAdaptedElfinderFiles();
        $this->copyJqueryUiImages();
        $this->copyFontawesomeFonts();
        $this->copyLeafletImages();
    }

    private function cleanOverheadFromDependencies()
    {

        $folder = new Folder();

        $folder->delete($this->fontawesomePath . 'js');

        $file = new File($this->fontawesomePath . 'css' . DS . 'all.min.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'fontawesome.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'fontawesome.min.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'v4-shims.css');
        $file->delete();
        $file = new File($this->fontawesomePath . 'css' . DS . 'v4-shims.min.css');
        $file->delete();

        $activeThemeFolder = 'smoothness';
        $folder = new Folder($this->jqueryUiPath . 'themes' . DS . $activeThemeFolder);
        $folder->copy($this->jqueryUiPath . 'theme-backup');
        $folder->delete($this->jqueryUiPath . 'themes');
        $folder = new Folder($this->jqueryUiPath . 'theme-backup');
        $folder->move($this->jqueryUiPath . 'themes' . DS . $activeThemeFolder);

        // files not compatible with license GPLv.3
        $file = new File($this->jqueryKnobPath . 'excanvas.js');
        $file->delete();
        $file = new File($this->featherlightPath . 'assets' . DS . 'stylesheets' . DS . 'bootstrap.min.css');
        $file->delete();


    }

    private function copyFontawesomeFonts()
    {
        $folder = new Folder($this->fontawesomePath . 'webfonts' . DS);
        $folder->copy(WWW_ROOT . 'webfonts');
        $this->out('Fontawesome fonts copied.');
    }

    /**
     * if asset compress is on (debug=0=)
     * images linked in css files have to be located in WEBROOT/cache
     */
    private function copyJqueryUiImages()
    {
        $folder = new Folder($this->jqueryUiPath . 'themes' . DS . 'smoothness' . DS . 'images' . DS);
        $folder->copy(WWW_ROOT . 'cache' . DS . 'images');
        $this->out('JQueryUI images copied.');
    }

    private function copyLeafletImages()
    {
        $folder = new Folder($this->leafletPath . 'dist' . DS . 'images' . DS);
        $folder->copy(WWW_ROOT . 'cache' . DS . 'images');
        $this->out('Leaflet images copied.');
    }

    private function copyAdaptedElfinderFiles()
    {
        $elfinderConfigDir = ROOT . DS . 'config' . DS . 'elfinder' . DS;

        $adaptedFiles = [
            $elfinderConfigDir . 'elfinder.html',
            $elfinderConfigDir . 'php' . DS . 'connector.minimal.php'
        ];

        foreach ($adaptedFiles as $file) {
            copy($file, preg_replace('/config/', 'webroot' . DS . 'js', $file, 1));
            $this->out('Elfinder config file ' . $file . ' copied successfully.');
        }
    }
}
