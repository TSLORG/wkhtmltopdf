<?php

namespace KingFoo\Pdf;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Wkhtmltopdf
{
    const PAGE_SIZE_A0        = 'A0';
    const PAGE_SIZE_A1        = 'A1';
    const PAGE_SIZE_A2        = 'A2';
    const PAGE_SIZE_A3        = 'A3';
    const PAGE_SIZE_A4        = 'A4';
    const PAGE_SIZE_A5        = 'A5';
    const PAGE_SIZE_A6        = 'A6';
    const PAGE_SIZE_A7        = 'A7';
    const PAGE_SIZE_A8        = 'A8';
    const PAGE_SIZE_A9        = 'A9';
    const PAGE_SIZE_B0        = 'B0';
    const PAGE_SIZE_B1        = 'B1';
    const PAGE_SIZE_B2        = 'B2';
    const PAGE_SIZE_B3        = 'B3';
    const PAGE_SIZE_B4        = 'B4';
    const PAGE_SIZE_B5        = 'B5';
    const PAGE_SIZE_B6        = 'B6';
    const PAGE_SIZE_B7        = 'B7';
    const PAGE_SIZE_B8        = 'B8';
    const PAGE_SIZE_B9        = 'B9';
    const PAGE_SIZE_B10       = 'B10';
    const PAGE_SIZE_C5E       = 'C5E';
    const PAGE_SIZE_COMM10E   = 'Comm10E';
    const PAGE_SIZE_DLE       = 'DLE';
    const PAGE_SIZE_EXECUTIVE = 'Executive';
    const PAGE_SIZE_FOLIO     = 'Folio';
    const PAGE_SIZE_LEDGER    = 'Ledger';
    const PAGE_SIZE_LEGAL     = 'Legal';
    const PAGE_SIZE_LETTER    = 'Letter';
    const PAGE_SIZE_TABLOID   = 'Tabloid';
    const PAGE_SIZE_CUSTOM    = 'Custom';

    const ORIENTATION_LANDSCAPE = 'Landscape';
    const ORIENTATION_PORTRAIT  = 'Portrait';

    protected $binary = null;
    protected $debug = false;
    protected $logger = null;

    protected $pageSize   = self::PAGE_SIZE_A4;
    protected $pageWidth  = '210mm';
    protected $pageHeight = '297mm';

    protected $orientation = self::ORIENTATION_PORTRAIT;

    protected $marginTop    = '10mm';
    protected $marginRight  = '10mm';
    protected $marginBottom = '10mm';
    protected $marginLeft   = '10mm';

    protected $lowQuality = false;
    protected $grayscale  = false;
    protected $title      = null;

    protected $header = null;
    protected $footer = null;

    protected $zoom = 1;

    protected $options = array();

    public function __construct($binary, $debug = false)
    {
        $this->binary = $binary;
        $this->debug = $debug;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function debug($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * @param int $zoom
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
    }

    /**
     * @return int
     */
    public function getZoom()
    {
        return $this->zoom;
    }



    public function setPageSize($value)
    {
        $this->pageSize = $value;
        return $this;
    }

    protected function getRealSize($value)
    {
        if (is_numeric($value)) {
            $value = sprintf("%umm", $value);
        }
        return $value;
    }

    public function setPageWidth($value)
    {
        $this->pageSize = self::PAGE_SIZE_CUSTOM;
        $this->pageWidth = $this->getRealSize($value);
        return $this;
    }

    public function setPageHeight($value)
    {
        $this->pageSize = self::PAGE_SIZE_CUSTOM;
        $this->pageHeight = $this->getRealSize($value);
        return $this;
    }

    public function setOrientation($value)
    {
        $this->orientation = $value;
        return $this;
    }

    public function setMarginTop($value)
    {
        $this->marginTop = $this->getRealSize($value);
        return $this;
    }

    public function setMarginRight($value)
    {
        $this->marginRight = $this->getRealSize($value);
        return $this;
    }

    public function setMarginBottom($value)
    {
        $this->marginBottom = $this->getRealSize($value);
        return $this;
    }

    public function setMarginLeft($value)
    {
        $this->marginLeft = $this->getRealSize($value);
        return $this;
    }

    public function setLowQuality($value)
    {
        $this->lowQuality = $value;
        return $this;
    }

    public function setGrayscale($value)
    {
        $this->grayscale = $value;
        return $this;
    }

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }

    public function setHeader($value)
    {
        $this->header = $value;
        return $this;
    }

    public function setFooter($value)
    {
        $this->footer = $value;
        return $this;
    }

    protected function createTempFile($data = null, $extension='html')
    {
        $filename = sys_get_temp_dir() . '/wkhtmltopdf-' . uniqid() . '.' . $extension;
        if (null === $data) {
            return $filename;
        }
        file_put_contents($filename, $data);
        return $filename;
    }

    protected function buildOptions()
    {
        $options = array();
        $options['page-size'] = $this->pageSize;
        if (self::PAGE_SIZE_CUSTOM === $this->pageSize) {
            $options['page-width'] = $this->pageWidth;
            $options['page-height'] = $this->pageHeight;
        }
        $options['orientation'] = $this->orientation;
        $options['margin-top'] = $this->marginTop;
        $options['margin-right'] = $this->marginRight;
        $options['margin-bottom'] = $this->marginBottom;
        $options['margin-left'] = $this->marginLeft;
        $options['zoom'] = $this->zoom;

        if ($this->lowQuality) {
            $options['lowquality'] = null;
        }
        if ($this->grayscale) {
            $options['grayscale'] = null;
        }
        if ($this->title) {
            $options['title'] = $this->title;
        }
        if ($this->header) {
            $options['header-html'] = $this->createTempFile($this->header);
            $options['header-spacing'] = 10; // fixes overlapping (http://code.google.com/p/wkhtmltopdf/issues/detail?id=182)
        }
        if ($this->footer) {
            $options['footer-html'] = $this->createTempFile($this->footer);
            $options['footer-spacing'] = 10; // fixes overlapping (http://code.google.com/p/wkhtmltopdf/issues/detail?id=182)
        }
        return array_merge($options, $this->options);
    }

    public function setOption($name, $value = null)
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function convert($html, $extension = 'html')
    {
        $input = $this->createTempFile($html, $extension);
        $output = $this->createTempFile();
        $options = $this->buildOptions();
        $cmd = $this->binary;
        foreach ($options as $option => $argument) {
            if (null === $argument) {
                $cmd .= sprintf(' --%s', $option);
            } else {
                $cmd .= sprintf(' --%s %s', $option, escapeshellarg($argument));
            }
        }
        $cmd .= sprintf(' %s %s', escapeshellarg($input), escapeshellarg($output));
        if ($this->debug) {
            $this->debug('Executing command.', array('command' => $cmd));
        }
        $proc = proc_open($cmd, array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w')), $pipes);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        //error_log($stdout, 3, '/tmp/wkhtmltopdf-output.log');
        //echo '<pre>' . $stdout . '</pre>';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        //error_log($stderr, 3, '/tmp/wkhtmltopdf-error.log');
        //echo '<pre>' . $stderr . '</pre>';
        fclose($pipes[2]);
        $return = proc_close($proc);
        //var_dump($return);
        //die();
        if (is_readable($output)) {
            $pdf = file_get_contents($output);
        } else {
            throw new \Exception(sprintf('The binary reported an error: executing "%s" with error message "%s"', $cmd, $stderr));
        }

        unlink($input);
        unlink($output);
        if (isset($options['header-html'])) {
            unlink($options['header-html']);
        }
        if (isset($options['footer-html'])) {
            unlink($options['footer-html']);
        }
        if (0 === $return) {
            return $pdf;
        } else {
            throw new \Exception(sprintf('The binary reported an error: executing "%s" with error message "%s"', $cmd, $stderr));
        }
    }

    public static function getJavaScriptHelper()
    {
        return <<<EOT
<script>
(function() {
    var vars = {}, p = window.location.search.substring(1).split("&"), i, t;
    for (i in p) {
        t = p[i].split("=");
        vars[t[0]] = decodeURIComponent(t[1]);
    }
    window.wkhtmltopdf = vars;
})();
</script>
EOT;
    }
}
