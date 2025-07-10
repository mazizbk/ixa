<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Util;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ExcelExporter
{

    const FORMAT_CSV = 'csv';
    const FORMAT_XLSX = 'xlsx';
    const FORMAT_HTML = 'html';

    public function exportArray(array $array, $filename, $filenameFallback, $format = self::FORMAT_XLSX)
    {
        $spreadsheet = $this->arrayToSpreadsheet($array);

        return $this->spreadsheetToResponse($spreadsheet, $filename, $filenameFallback, $format);
    }

    public function arrayToSpreadsheet(array $array)
    {
        $spreadsheet = new Spreadsheet();
        /** @noinspection PhpUnhandledExceptionInspection */
        $sheet = $spreadsheet->getActiveSheet();
        $this->arrayToSheet($array, $sheet);

        return $spreadsheet;
    }

    public function arrayToSheet(array $array, Worksheet $sheet): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */

        $sheet->fromArray($array);
        for ($i=0; $i<count($array[0])+1; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }
    }

    public function spreadsheetToResponse(Spreadsheet $spreadsheet, string $filename, ?string $filenameFallback = null, string $format = self::FORMAT_XLSX): StreamedResponse
    {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        switch ($format) {
            case self::FORMAT_XLSX:
                $writer = new Xlsx($spreadsheet);
                $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case self::FORMAT_CSV:
                $writer = new Csv($spreadsheet);
                $mimeType = 'text/csv';
                break;
            case self::FORMAT_HTML:
                $writer = new Html($spreadsheet);
                $mimeType = 'text/html';
                $disposition = ResponseHeaderBag::DISPOSITION_INLINE;
                break;
            default:
                throw new \InvalidArgumentException;
        }

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, Response::HTTP_OK);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition',
            $response->headers->makeDisposition(
                $disposition,
                $filename,
                $filenameFallback ?? self::safeFilename($filename)
            )
        );

        return $response;
    }

    public static function safeFilename(string $filename): string
    {
        return preg_replace('/[^\x20-\x24\x26-\x2E\x30-\x5B\x5D-\x7E]/','', $filename);
    }
}
