<?php
namespace exussum12\CoverageChecker;

/**
 * Class CodeClimateLoader
 * Used for parsing reports in CodeClimate format
 * @package exussum12\CoverageChecker
 */
class CodeClimateLoader implements FileChecker
{
    /**
     * @var array
     */
    protected $file;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param string $file the path to the codeclimate file
     */
    public function __construct($file)
    {
        $json = $this->convertToJson(file_get_contents($file));
        $this->file = json_decode($json);
    }

    /**
     * {@inheritdoc}
     */
    public function parseLines()
    {
        foreach ($this->file as $line) {
            $this->addError($line);
        }

        return array_keys($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorsOnLine($file, $lineNumber)
    {
        $errors = [];
        if (isset($this->errors[$file][$lineNumber])) {
            $errors = $this->errors[$file][$lineNumber];
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function handleNotFoundFile()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDescription()
    {
        return 'Parse codeclimate output';
    }

    private function addError($line)
    {
        $trim = './';
        $fileName = substr($line->location->path, strlen($trim));
        $start = $line->location->lines->begin;
        $end = $line->location->lines->end;
        $message = $line->description;

        for ($lineNumber = $start; $lineNumber <= $end; $lineNumber++) {
            $this->errors[$fileName][$lineNumber][] = $message;
        }
    }

    private function convertToJson($codeClimateFormat)
    {
        $codeClimateFormat = str_replace("\0", ',', $codeClimateFormat);

        return '[' . $codeClimateFormat . ']';
    }
}
