Wkhtmltopdf
===========

This is a PHP wrapper class for the [wkhtmltopdf](http://code.google.com/p/wkhtmltopdf/) static binary.

Features
--------

Wkhtmltopdf uses a Webkit rendering engine to create a PDF from a HTML input file. This PHP wrapper class facilitates generating PDF files from a HTML string.

Currently a subset of features of the wkhtmltopdf command-line binary is supported through the PHP class' API, but you can pass any additional supported option through the `Wkhtmltopdf::setOption` method.

To get a complete overview of the command-line options execute (use the binary for your platform):

    ./bin/wkhtmltopdf-i386 -H

Installation
------------

Make sure you added https://packages.king-foo.net to your Composer repositories, then add the package to your project's `composer.json`:

    "require": {
        "kingfoo/wkhtmltopdf": "*@dev"
    }

Requirements
------------

A working binary for the wkhtmltopdf tool. Binaries for Linux (i386 and amd64) are included in the `bin` directory.

Example Usage
-------------

    use KingFoo\Pdf\Wkhtmltopdf;

    $wkhtmltopdf = new Wkhtmltopdf($pathToBinary);

    $headerHtml = file_get_contents($htmlHeaderFile);
    $footerHtml = file_get_contents($htmlHeaderFile);

    $html = file_get_contents($someHtmlFile);

    $wkhtmltopdf
        ->setPageSize(Wkhtmltopdf::PAGE_SIZE_LETTER)
        ->setOrientation(Wkhtmltopdf::ORIENTATION_LANDSCAPE)
        ->setHeader($headerHtml)
        ->setFooter($footerHtml)
        ->setOption('dpi', 300)
        ->setOption('no-pdf-compression');

    $pdf = $wkhtmltopdf->convert($html);
