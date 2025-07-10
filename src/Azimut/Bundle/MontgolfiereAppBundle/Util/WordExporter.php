<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Util;

use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class WordExporter
{
    public static function createDocument(): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->getCompatibility()->setOoxmlVersion(16);
        //Titles template configuration
        $phpWord->addTitleStyle(0, [
            'size' => 22,
            'color' => '000000',
            'font' => 'Cambria',
        ], [
            'alignment' => Jc::START,
            'spaceAfter' => Converter::pointToTwip(8),
        ]);
        $phpWord->addTitleStyle(1, [
            'name' => 'Cambria',
            'size' => 18,
            'color' => '000000',
        ], [
            'borderBottomColor' => '000000',
            'borderBottomSize' => Converter::pointToTwip(.5),
            'spaceAfter' => Converter::pointToTwip(8),
        ]);
        $phpWord->addTitleStyle(2, [
            'size' => 16,
            'color' => '000000',
            'underline' => Font::UNDERLINE_SINGLE,
        ], [
            'spaceAfter' => Converter::pointToTwip(8),
        ]);
        $phpWord->addTitleStyle(3, [
            'size' => 12,
            'color' => '243F60',
        ], [
            'spaceAfter' => Converter::pointToTwip(8),
        ]);
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultParagraphStyle([
            'spacing' => Converter::pointToTwip(1.08),
            'spaceAfter' => Converter::pointToTwip(8),
        ]);

        $phpWord->getSettings()->setThemeFontLang(new Language(Language::FR_FR));

        return $phpWord;
    }

    public static function convertTextToWord(string $text): string
    {
        return str_replace(
            ['&', '<', '>', "\n"],
            ['&amp;', '&lt;', '&gt;', '<w:br/>'],
            $text
        );
    }

    public static function makeResponse(PhpWord $phpWord, string $fileName): StreamedResponse
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $response = new StreamedResponse(function () use ($objWriter) {
            $objWriter->save('php://output');
        }, Response::HTTP_OK);

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName,
                preg_replace('/[^\x20-\x7e]/', '', $fileName)
            )
        );

        return $response;
    }
}
