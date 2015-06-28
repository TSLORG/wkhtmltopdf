<?php

namespace KingFoo\Pdf;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Wkhtmltoimage
{
    protected $binary = null;
    protected $debug = false;
    protected $logger = null;

    protected $lowQuality = false;
    protected $grayscale  = false;
    protected $title      = null;

    protected $header = null;
    protected $footer = null;

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

    protected function getRealSize($value)
    {
        if (is_numeric($value)) {
            $value = sprintf("%umm", $value);
        }
        return $value;
    }

    public function setWidth($value)
    {
        $this->setOption('width', $value);
        return $this;
    }

    public function setHeight($value)
    {
        $this->setOption('height', $value);
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
        $filename = sys_get_temp_dir() . '/wkhtmltoimage-' . uniqid() . '.' . $extension;
        if (null === $data) {
            return $filename;
        }
        file_put_contents($filename, $data);
        return $filename;
    }

    protected function buildOptions()
    {
        $options = array();
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
        $output = $this->createTempFile('', 'png');
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
