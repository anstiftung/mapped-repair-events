<?php
declare(strict_types=1);

namespace App\Services\PdfWriter;

use Cake\Utility\Inflector;
use Cake\View\ViewBuilder;
use TCPDF;

abstract class PdfWriterService
{

    protected mixed $pdfLibrary;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];
    protected ?string $plugin = null;
    protected string $filename = '';
    public ?string $templateFile = null;

    public function setPdfLibrary(TCPDF $pdfLibrary): static
    {
        $this->pdfLibrary = $pdfLibrary;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
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

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    private function getContent(): void
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

    public function writeInline(): string
    {
        $this->getContent();
        return $this->pdfLibrary->Output($this->getFilename(), 'I');
    }

    public function writeAttachment(): string
    {
        $this->getContent();
        return $this->pdfLibrary->Output('', 'S');
    }

    public function writeFile(): string
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

    public function writeHtml(): string
    {
        $this->getContent();
        return $this->pdfLibrary->getHtml();
    }

}