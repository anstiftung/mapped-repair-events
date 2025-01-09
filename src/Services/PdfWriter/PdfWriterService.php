<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use Cake\Utility\Inflector;
use Cake\View\ViewBuilder;

abstract class PdfWriterService
{

    protected $pdfLibrary;
    protected $data = [];
    protected $plugin = null;
    protected $filename = '';
    public $templateFile = null;

    public function setPdfLibrary($pdfLibrary): PdfWriterService
    {
        $this->pdfLibrary = $pdfLibrary;
        return $this;
    }

    public function setData($data): PdfWriterService
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFilenameWithoutPath(): string
    {
        return basename($this->filename);
    }

    public function setFilename($filename): PdfWriterService
    {
        $this->filename = $filename;
        return $this;
    }

    public function getData() {
        return $this->data;
    }

    private function getContent()
    {
        $this->data['pdf'] = $this->pdfLibrary;
        $viewBuilder = new ViewBuilder();
        if ($this->plugin) {
            $viewBuilder->setPlugin($this->plugin);
        }
        if (is_null($this->templateFile)) {
            $reflect = new \ReflectionClass($this);
            $this->templateFile = Inflector::underscore(str_replace('PdfWriter', '', $reflect->getShortName()));
            $this->templateFile = DS . 'pdf' . DS . $this->templateFile;
        }
        $viewBuilder->setLayout('ajax')->setVars($this->getData())->setTemplate($this->templateFile)->build()->render();
    }

    public function writeInline()
    {
        $this->getContent();
        return $this->pdfLibrary->Output($this->getFilename(), 'I');
    }

    public function writeAttachment()
    {
        $this->getContent();
        return $this->pdfLibrary->Output('', 'S');
    }

    public function writeFile()
    {
        $this->getContent();

        // pdf saved on server
        if (file_exists($this->getFilename())) {
            unlink($this->getFilename());
        }

        $path = dirname($this->getFilename());
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $this->pdfLibrary->Output($this->getFilename(), 'F');
    }

    public function writeHtml()
    {
        $this->getContent();
        return $this->pdfLibrary->getHtml();
    }

}