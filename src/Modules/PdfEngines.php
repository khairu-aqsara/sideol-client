<?php

declare(strict_types=1);

namespace Picsi\Sideolclient\Modules;

use Picsi\Sideolclient\ExecutionTime;
use Picsi\Sideolclient\Index;
use Picsi\Sideolclient\MultipartFormDataModule;
use Picsi\Sideolclient\Stream;
use Psr\Http\Message\RequestInterface;

/**
 * @version 7.0.0
 * @author Khairu Aqsara Sudirman
 * */

class PdfEngines
{
    use MultipartFormDataModule;

    private ?Index $index = null;

    /**
     * Overrides the default index generator for ordering
     * files we want to merge.
     */
    public function index(Index $index): self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Sets the PDF format of the resulting PDF.
     */
    public function pdfFormat(string $format): self
    {
        $this->formValue('pdfFormat', $format);

        return $this;
    }

    /**
     * Merges PDFs into a unique PDF.
     *
     * Note: the merging order is determined by the order of the arguments.
     */
    public function merge(Stream $pdf1, Stream $pdf2, Stream ...$pdfs): RequestInterface
    {
        $index = $this->index ?? new ExecutionTime();

        $this->formFile($index->create() . '_' . $pdf1->getFilename(), $pdf1->getStream());
        $this->formFile($index->create() . '_' . $pdf2->getFilename(), $pdf2->getStream());

        foreach ($pdfs as $pdf) {
            $this->formFile($index->create() . '_' . $pdf->getFilename(), $pdf->getStream());
        }

        $this->endpoint = '/forms/pdfengines/merge';

        return $this->request();
    }

    /**
     * Converts PDF(s) to a specific PDF format.
     * Sideo will return the PDF or a ZIP archive with the PDFs.
     *
     */
    public function convert(string $pdfFormat, Stream $pdf, Stream ...$pdfs): RequestInterface
    {
        $this->formValue('pdfFormat', $pdfFormat);
        $this->formFile($pdf->getFilename(), $pdf->getStream());

        foreach ($pdfs as $pdf) {
            $this->formFile($pdf->getFilename(), $pdf->getStream());
        }

        $this->endpoint = '/forms/pdfengines/convert';

        return $this->request();
    }
}